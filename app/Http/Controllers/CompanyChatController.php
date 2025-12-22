<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Company;
use App\Models\Attachment;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Events\MessageEdited;
use App\Events\MessageDeleted;
use App\Events\NewMessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ConversationParticipant;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;

class CompanyChatController extends Controller
{


    // ✅ TAMBAH INI
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(string $companyId)
    {
        $userId = Auth::id();
        $company = Company::findOrFail($companyId);

        $isCompanyMember = $company->users()->where('users.id', $userId)->exists();

        if (!$isCompanyMember) {
            abort(403, 'Akses ditolak');
        }

        return view('company-chat', compact('company'));
    }

    /**
     * 🔥 FIXED: Get chat data untuk company
     */
    public function getChatData(string $companyId)
    {
        $userId = Auth::id();
        $company = Company::findOrFail($companyId);

        // Validasi akses company
        $isCompanyMember = $company->users()->where('users.id', $userId)->exists();
        if (!$isCompanyMember) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        // Main company group
        $mainCompanyGroup = Conversation::where('company_id', $companyId)
            ->where('scope', 'company')
            ->where('type', 'group')
            ->first();

        if (!$mainCompanyGroup) {
            $mainCompanyGroup = Conversation::create([
                'company_id' => $companyId,
                'scope' => 'company',
                'type' => 'group',
                'name' => $company->name,
                'created_by' => $userId
            ]);

            Log::info("Created new main company conversation for company {$companyId} with name '{$company->name}'");
        } else {
            // Sync nama conversation dengan nama company
            if ($mainCompanyGroup->name !== $company->name) {
                $mainCompanyGroup->update(['name' => $company->name]);
                Log::info("Synced company conversation name from '{$mainCompanyGroup->name}' to '{$company->name}' for company {$companyId}");
            }
        }

        // Pastikan user adalah participant
        ConversationParticipant::firstOrCreate(
            ['conversation_id' => $mainCompanyGroup->id, 'user_id' => $userId],
            ['last_read_at' => now()]
        );

        // Other conversations (private chats)
        $otherConversations = Conversation::where('company_id', $companyId)
            ->where('scope', 'company')
            ->where('type', 'private')
            ->where('id', '!=', $mainCompanyGroup->id)
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->with(['participants.user'])
            ->get()
            ->map(function ($conversation) use ($userId) {
                // Calculate unread count
                $participantData = $conversation->participants->where('user_id', $userId)->first();
                $lastReadAt = $participantData ? $participantData->last_read_at : null;

                if ($lastReadAt) {
                    $conversation->unread_count = Message::where('conversation_id', $conversation->id)
                        ->where('sender_id', '!=', $userId)
                        ->where('created_at', '>', $lastReadAt)
                        ->count();
                } else {
                    $conversation->unread_count = $conversation->messages()
                        ->where('sender_id', '!=', $userId)
                        ->count();
                }

                // Load last message
                $conversation->last_message = $conversation->messages()
                    ->with(['sender', 'attachments'])
                    ->orderBy('created_at', 'DESC')
                    ->first();

                return $conversation;
            });

        // Calculate unread untuk main group
        $participantData = $mainCompanyGroup->participants->where('user_id', $userId)->first();
        $lastReadAt = $participantData ? $participantData->last_read_at : null;

        if ($lastReadAt) {
            $mainCompanyGroup->unread_count = Message::where('conversation_id', $mainCompanyGroup->id)
                ->where('sender_id', '!=', $userId)
                ->where('created_at', '>', $lastReadAt)
                ->count();
        } else {
            $mainCompanyGroup->unread_count = $mainCompanyGroup->messages()
                ->where('sender_id', '!=', $userId)
                ->count();
        }

        $mainCompanyGroup->last_message = $mainCompanyGroup->messages()
            ->with('sender', 'attachments')
            ->orderBy('created_at', 'DESC')
            ->first();

        // Get semua member company
        $members = $company->users()
            ->where('users.id', '!=', $userId)
            ->get();

        return response()->json([
            'main_group' => $mainCompanyGroup,
            'conversations' => $otherConversations,
            'members' => $members,
        ]);
    }

    /**
     * 🔥 FIXED: Show messages untuk company chat
     */
    public function showMessages(string $conversationId)
    {
        $userId = Auth::id();

        // Validasi participant dan scope company
        $isParticipant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->whereHas('conversation', function ($q) {
                $q->where('scope', 'company');
            })
            ->exists();

        if (!$isParticipant) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        $messages = Message::where('conversation_id', $conversationId)
            ->with([
                'sender:id,full_name,avatar',
                'attachments',
                'reply_to' => function ($query) {
                    $query->select('id', 'sender_id', 'content', 'message_type', 'deleted_at', 'created_at')
                        ->with([
                            'sender:id,full_name,avatar',
                            'attachments'
                        ]);
                }
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        try {
            // Update last read
            ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $userId)
                ->update(['last_read_at' => now()]);

            // Mark messages as read
            Message::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);
        } catch (\Exception $e) {
            Log::error('Gagal update last_read_at di company chat: ' . $e->getMessage());
        }

        return response()->json($messages->toArray());
    }

    /**
     * 🔥 FIXED: Store message untuk company chat
     */
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|uuid|exists:conversations,id',
            'content' => 'nullable|string',
            'files.*' => 'nullable|file|max:10240',
            'reply_to_message_id' => 'nullable|uuid|exists:messages,id',
        ]);

        $userId = Auth::id();

        // Validasi participant dan scope company
        $isParticipant = ConversationParticipant::where('conversation_id', $request->conversation_id)
            ->where('user_id', $userId)
            ->whereHas('conversation', function ($q) {
                $q->where('scope', 'company');
            })
            ->exists();

        if (!$isParticipant) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        DB::beginTransaction();

        try {
            $messageType = 'text';
            if ($request->hasFile('files')) {
                $messageType = 'file';
            }

            $message = Message::create([
                'conversation_id' => $request->conversation_id,
                'sender_id'       => $userId,
                'content'         => $request->input('content') ?? '',
                'message_type'    => $messageType,
                'reply_to_message_id' => $request->input('reply_to_message_id'),
                'deleted_at'      => null,
            ]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('chat_files', $filename, 'public');
                    $fileUrl = url(Storage::url('chat_files/' . $filename));

                    // Pastikan file_size tidak null
                    $fileSize = $file->getSize();
                    if (!$fileSize || $fileSize === 0) {
                        $fullPath = storage_path('app/public/chat_files/' . $filename);
                        if (file_exists($fullPath)) {
                            $fileSize = filesize($fullPath);
                        }
                    }

                    Attachment::create([
                        'attachable_type' => Message::class,
                        'attachable_id'   => $message->id,
                        'file_url'        => $fileUrl,
                        'file_name'       => $file->getClientOriginalName(),
                        'file_size'       => $fileSize ?: 0,
                        'file_type'       => $file->getMimeType() ?: 'application/octet-stream',
                        'uploaded_by'     => $userId,
                    ]);
                }
            }

            $message->load([
                'sender:id,full_name,avatar',
                'attachments',
                'reply_to' => function ($query) {
                    $query->select('id', 'sender_id', 'content', 'message_type', 'deleted_at', 'created_at')
                        ->with([
                            'sender:id,full_name,avatar',
                            'attachments'
                        ]);
                }
            ]);

            DB::commit();

$this->notificationService->notifyNewMessage($message);

            // Broadcast event
            event(new NewMessageSent($message));

            return response()->json([
                'success' => true,
                'data' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal kirim pesan company chat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengirim pesan.',
            ], 500);
        }
    }

    /**
     * 🔥 FIXED: Edit message untuk company chat
     */
    public function editMessage(Request $request, Message $message)
    {
        $userId = Auth::id();

        if ($message->sender_id !== $userId) {
            return response()->json(['error' => 'Anda tidak berhak mengedit pesan ini'], 403);
        }

        if (!$message->canBeEdited()) {
            return response()->json(['error' => 'Pesan tidak dapat diedit'], 400);
        }

        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        try {
            $message->update([
                'content' => $request->input('content'),
                'is_edited' => true,
                'edited_at' => now()
            ]);

            $message->load([
                'sender',
                'attachments',
                'reply_to' => function ($query) {
                    $query->with('sender', 'attachments');
                }
            ]);

            // Broadcast event
            event(new MessageEdited($message));

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Error editing message company chat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengedit pesan',
            ], 500);
        }
    }

    /**
     * 🔥 FIXED: Delete message untuk company chat
     */
    public function deleteMessage(Message $message)
    {
        $userId = Auth::id();

        if ($message->sender_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            $conversationId = $message->conversation_id;
            $messageId = $message->id;

            // Hapus file attachments
            $attachments = Attachment::where('attachable_type', Message::class)
                ->where('attachable_id', $messageId)
                ->get();

            foreach ($attachments as $attachment) {
                if ($attachment->file_url) {
                    $urlParts = parse_url($attachment->file_url);
                    $path = isset($urlParts['path']) ? ltrim($urlParts['path'], '/') : '';
                    $path = str_replace('storage/', '', $path);

                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                        Log::info("Deleted file company chat: {$path}");
                    }
                }
            }

            Attachment::where('attachable_type', Message::class)
                ->where('attachable_id', $messageId)
                ->delete();

            // Soft delete message
            DB::table('messages')
                ->where('id', $messageId)
                ->update([
                    'deleted_at' => now(),
                    'content' => null,
                    'message_type' => 'deleted',
                    'updated_at' => now()
                ]);

            // Update last message di conversation
            $this->updateConversationLastMessage($conversationId);

            DB::commit();

            $message->refresh();
            $message->load(['sender']);

            // Broadcast event
            event(new MessageDeleted($message));

            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting message company chat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus pesan',
            ], 500);
        }
    }

    /**
     * 🔥 FIXED: Helper untuk update last message
     */
    private function updateConversationLastMessage($conversationId)
    {
        $lastMessage = Message::where('conversation_id', $conversationId)
            ->whereNull('deleted_at')
            ->with(['sender', 'attachments'])
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$lastMessage) {
            Conversation::where('id', $conversationId)
                ->update(['last_message_id' => null]);
            return;
        }

        Conversation::where('id', $conversationId)
            ->update(['last_message_id' => $lastMessage->id]);
    }

    /**
     * 🔥 FIXED: Create conversation untuk company chat
     */
    public function createConversation(Request $request)
    {
        $request->validate([
            'company_id' => 'required|uuid|exists:companies,id',
            'type' => 'required|in:group,private',
            'name' => 'nullable|string|max:100',
            'participants' => 'required|array|min:1',
            'participants.*' => 'required|uuid|exists:users,id',
        ]);

        $authId = Auth::id();

        // Validasi untuk private chat
        if ($request->type == 'private' && count($request->participants) == 1) {
            $otherUserId = $request->participants[0];

            if ($otherUserId == $authId) {
                return response()->json(['error' => 'Tidak dapat membuat percakapan dengan diri sendiri.'], 422);
            }

            // Cek existing private chat
            $existing = Conversation::where('type', 'private')
                ->where('scope', 'company')
                ->where('company_id', $request->company_id)
                ->whereHas('participants', fn($q) => $q->where('user_id', $authId))
                ->whereHas('participants', fn($q) => $q->where('user_id', $otherUserId))
                ->has('participants', '=', 2)
                ->first();

            if ($existing) {
                return response()->json([
                    'success'      => true,
                    'conversation' => $existing->load('participants.user'),
                    'existed'      => true
                ]);
            }
        }

        DB::beginTransaction();

        try {
            $conversation = Conversation::create([
                'company_id' => $request->company_id,
                'scope'      => 'company',
                'type'       => $request->type,
                'name'       => $request->type == 'group' ? $request->name : null,
                'created_by' => $authId,
            ]);

            $participantIds = $request->participants;
            foreach ($participantIds as $participantId) {
                if ($participantId == $authId) continue;

                ConversationParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id'         => $participantId,
                    'is_admin'        => false,
                    'last_read_at'    => null,
                ]);
            }

            // Tambahkan creator sebagai participant
            ConversationParticipant::firstOrCreate([
                'conversation_id' => $conversation->id,
                'user_id'         => $authId,
            ], [
                'is_admin' => true,
                'last_read_at' => now()
            ]);

            DB::commit();

            $conversation->load('participants.user');

            return response()->json([
                'success' => true,
                'conversation' => $conversation,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal buat percakapan company chat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal membuat percakapan.',
            ], 500);
        }
    }

    /**
     * 🔥 FIXED: Mark as read untuk company chat
     */
    public function markAsRead(string $conversationId)
    {
        $userId = Auth::id();

        try {
            ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $userId)
                ->update(['last_read_at' => now()]);

            Message::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Gagal markAsRead company chat: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}
