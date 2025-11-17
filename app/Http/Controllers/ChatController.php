<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Workspace;
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

class ChatController extends Controller
{
    /**
     * 🔥 NEW: Endpoint khusus untuk load chat data (separasi dari index)
     */
    public function getChatData(string $workspaceId)
    {
        $userId = Auth::id();
        $workspace = Workspace::findOrFail($workspaceId);

        // Main group conversation
        $mainGroup = Conversation::where('workspace_id', $workspaceId)
            ->where('type', 'group')
            ->first();

        if (!$mainGroup) {
            $mainGroup = Conversation::create([
                'workspace_id' => $workspaceId,
                'type' => 'group',
                'name' => $workspace->name,
                'created_by' => $workspace->created_by
            ]);

            Log::info("Created new main conversation for workspace {$workspaceId} with name '{$workspace->name}'");
        } else if ($mainGroup->name !== $workspace->name) {
            $mainGroup->update(['name' => $workspace->name]);
            Log::info("Synced conversation name from '{$mainGroup->name}' to '{$workspace->name}' for workspace {$workspaceId}");
        }

        ConversationParticipant::firstOrCreate(
            ['conversation_id' => $mainGroup->id, 'user_id' => $userId],
            ['last_read_at' => now()]
        );

        // Other conversations
        $otherConversations = Conversation::where('workspace_id', $workspaceId)
            ->where('id', '!=', $mainGroup->id)
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

        // Calculate unread for main group
        $participantData = $mainGroup->participants->where('user_id', $userId)->first();
        $lastReadAt = $participantData ? $participantData->last_read_at : null;

        if ($lastReadAt) {
            $mainGroup->unread_count = Message::where('conversation_id', $mainGroup->id)
                ->where('sender_id', '!=', $userId)
                ->where('created_at', '>', $lastReadAt)
                ->count();
        } else {
            $mainGroup->unread_count = $mainGroup->messages()
                ->where('sender_id', '!=', $userId)
                ->count();
        }

        $mainGroup->last_message = $mainGroup->messages()
            ->with('sender', 'attachments')
            ->orderBy('created_at', 'DESC')
            ->first();

        // Get members
        $members = $workspace->users()
            ->where('users.id', '!=', $userId)
            ->get();

        return response()->json([
            'main_group' => $mainGroup,
            'conversations' => $otherConversations,
            'members' => $members,
        ]);
    }

    public function index(string $workspaceId)
    {
        $workspace = Workspace::findOrFail($workspaceId);
        return view('chat', compact('workspace'));
    }

    /**
     * Load messages dengan relasi replyTo
     */
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

        $messagesWithReply = $messages->filter(fn($msg) => !is_null($msg->reply_to_message_id));

        Log::info('📤 Sending messages to frontend', [
            'conversation_id' => $conversationId,
            'total_messages' => $messages->count(),
            'messages_with_reply' => $messagesWithReply->count(),
            'sample_reply_data' => $messagesWithReply->first() ? [
                'id' => $messagesWithReply->first()->id,
                'reply_to_message_id' => $messagesWithReply->first()->reply_to_message_id,
                'has_reply_to' => !is_null($messagesWithReply->first()->reply_to),
                'reply_to_content' => $messagesWithReply->first()->reply_to?->content,
                'reply_to_sender' => $messagesWithReply->first()->reply_to?->sender?->full_name
            ] : null
        ]);

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
            Log::error('Gagal update last_read_at di showMessages: ' . $e->getMessage());
        }

        return response()->json($messages->toArray());
    }

    /**
     * Edit message
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

    /**
     * Send message dengan support reply
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

            // Upload files
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

            Log::info('📤 Broadcasting message', [
                'message_id' => $message->id,
                'reply_to_message_id' => $message->reply_to_message_id,
                'has_reply_to' => !is_null($message->reply_to),
                'reply_to_content' => $message->reply_to?->content ?? null,
                'message_array' => $message->toArray()
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
                        Log::info("Deleted file: {$path}");
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

            $this->updateConversationLastMessage($conversationId);

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
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus pesan',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function createConversation(Request $request)
    {
        $request->validate([
            'workspace_id' => 'required|uuid|exists:workspaces,id',
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
                ->where('workspace_id', $request->workspace_id)
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
                'workspace_id' => $request->workspace_id,
                'type'         => $request->type,
                'name'         => $request->type == 'group' ? $request->name : null,
                'created_by'   => $authId,
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

    /**
     * Tandai pesan sebagai sudah dibaca
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
            Log::error('Gagal markAsRead: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}
