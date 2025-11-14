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

class CompanyChatController extends Controller
{
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

    public function getChatData(string $companyId)
    {
        $userId = Auth::id();
        $company = Company::findOrFail($companyId);

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
        } else {
            if ($mainCompanyGroup->name !== $company->name) {
                $mainCompanyGroup->update(['name' => $company->name]);
            }
        }

        ConversationParticipant::firstOrCreate(
            ['conversation_id' => $mainCompanyGroup->id, 'user_id' => $userId],
            ['last_read_at' => now()]
        );

        // 🔥 LOAD private conversations (untuk store di JS, tapi tidak ditampilkan)
        $otherConversations = Conversation::where('company_id', $companyId)
            ->where('scope', 'company')
            ->where('type', 'private') // 🔥 Hanya private
            ->where('id', '!=', $mainCompanyGroup->id)
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->with(['participants.user'])
            ->get();

        // Calculate unread for main group
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

        // Get members
        $members = $company->users()
            ->where('users.id', '!=', $userId)
            ->get();

        return response()->json([
            'main_group' => $mainCompanyGroup,
            'conversations' => $otherConversations, // 🔥 Tetap kirim (untuk store di JS)
            'members' => $members,
        ]);
    }

    // 🆕 TAMBAHKAN: Show Messages
    public function showMessages(string $conversationId)
    {
        $userId = Auth::id();

        $isParticipant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
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
        } catch (\Exception $e) {
            Log::error('Gagal update last_read_at: ' . $e->getMessage());
        }

        return response()->json($messages->toArray());
    }

    // 🆕 TAMBAHKAN: Send Message
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|uuid|exists:conversations,id',
            'content' => 'nullable|string',
            'files.*' => 'nullable|file|max:10240',
            'reply_to_message_id' => 'nullable|uuid|exists:messages,id',
        ]);

        $userId = Auth::id();

        $isParticipant = ConversationParticipant::where('conversation_id', $request->conversation_id)
            ->where('user_id', $userId)
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

                    Attachment::create([
                        'attachable_type' => Message::class,
                        'attachable_id'   => $message->id,
                        'file_url'        => $fileUrl,
                        'file_name'       => $file->getClientOriginalName(),
                        'file_size'       => $file->getSize(),
                        'file_type'       => $file->getMimeType(),
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

            event(new NewMessageSent($message));

            return response()->json([
                'success' => true,
                'data' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal kirim pesan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengirim pesan.',
            ], 500);
        }
    }

    // 🆕 TAMBAHKAN: Edit Message
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

            event(new MessageEdited($message));

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Error editing message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengedit pesan',
            ], 500);
        }
    }

    // 🆕 TAMBAHKAN: Delete Message
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
                    }
                }
            }

            Attachment::where('attachable_type', Message::class)
                ->where('attachable_id', $messageId)
                ->delete();

            DB::table('messages')
                ->where('id', $messageId)
                ->update([
                    'deleted_at' => now(),
                    'content' => null,
                    'message_type' => 'deleted',
                    'updated_at' => now()
                ]);

            DB::commit();

            $message->refresh();
            $message->load(['sender']);

            event(new MessageDeleted($message));

            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting message: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus pesan',
            ], 500);
        }
    }

    // 🆕 TAMBAHKAN: Create Private Conversation
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

        if ($request->type == 'private' && count($request->participants) == 1) {
            $otherUserId = $request->participants[0];

            if ($otherUserId == $authId) {
                return response()->json(['error' => 'Tidak dapat membuat percakapan dengan diri sendiri.'], 422);
            }

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
            Log::error('Gagal buat percakapan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal membuat percakapan.',
            ], 500);
        }
    }

    // 🆕 TAMBAHKAN: Mark as Read
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
            Log::error('Gagal markAsRead: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}
