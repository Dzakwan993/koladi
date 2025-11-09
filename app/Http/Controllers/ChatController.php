<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Workspace;
use App\Models\Attachment;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Events\MessageDeleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ConversationParticipant;
use Illuminate\Support\Facades\Storage;
use App\Events\NewMessageSent; // <-- 🔥 PERBAIKAN PENTING ADA DI SINI

class ChatController extends Controller
{
    /**
     * Ambil semua percakapan user di workspace tertentu.
     * (Versi UPGRADE dengan unread count & last message)
     */
    public function index(string $workspaceId)
    {
        $userId = Auth::id();
        $workspace = Workspace::findOrFail($workspaceId);

        // --- 1. Ambil atau Buat Grup Utama ---
        $mainGroup = Conversation::firstOrCreate(
            [
                'workspace_id' => $workspaceId,
                'type' => 'group',
                'name' => $workspace->name
            ],
            ['created_by' => $workspace->user_id]
        );

        // Pastikan user adalah partisipan grup utama
        $mainGroupParticipant = ConversationParticipant::firstOrCreate(
            [
                'conversation_id' => $mainGroup->id,
                'user_id' => $userId
            ],
            ['last_read_at' => now()]
        );

        // --- 2. Ambil Percakapan Lain (DM & Grup Kustom) ---
        $otherConversations = Conversation::where('workspace_id', $workspaceId)
            ->where('id', '!=', $mainGroup->id)
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->with([
                'participants.user',
                // 'lastMessage.sender', // 🔥 COMMENT DULU jika pakai accessor
                // 'lastMessage.attachments'
            ])
            ->get();

        // --- 3. Hitung Unread Count & Manual Load Last Message ---
        $allConversations = $otherConversations->push($mainGroup);

        foreach ($allConversations as $conversation) {
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

            // 🔥 MANUAL: Load last message dengan sender & attachments
            $conversation->last_message = $conversation->messages()
                ->with('sender', 'attachments')
                ->orderBy('created_at', 'DESC')
                ->first();
        }

        // --- 4. Ambil Anggota Tim ---
        $members = $workspace->users()
            ->where('users.id', '!=', $userId)
            ->get();

        // --- 5. Kembalikan Respon ---
        return response()->json([
            'main_group' => $allConversations->find($mainGroup->id),
            'conversations' => $allConversations->where('id', '!=', $mainGroup->id)->values(),
            'members' => $members,
        ]);
    }

    /**
     * Ambil pesan berdasarkan conversation.
     */
    public function showMessages(string $conversationId)
    {
        $userId = Auth::id();

        // Validasi partisipan
        $isParticipant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->exists();

        if (!$isParticipant) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        // Ambil pesan dengan attachments
        $messages = Message::with(['sender', 'attachments'])
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        // 🔥 Tandai pesan sebagai sudah dibaca
        try {
            ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $userId)
                ->update(['last_read_at' => now()]);

            // Update is_read untuk pesan orang lain
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

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|uuid|exists:conversations,id',
            'content' => 'nullable|string',
            'files.*' => 'nullable|file|max:10240',
        ]);

        $userId = Auth::id();

        // Validasi Otorisasi
        $isParticipant = ConversationParticipant::where('conversation_id', $request->conversation_id)
            ->where('user_id', $userId)
            ->exists();

        if (!$isParticipant) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        DB::beginTransaction();

        try {
            $messageType = 'text';

            // Buat pesan
            $message = Message::create([
                'conversation_id' => $request->conversation_id,
                'sender_id'       => $userId,
                'content'         => $request->input('content') ?? '',
                'message_type'    => $messageType,
            ]);

            // Upload files (jika ada)
            if ($request->hasFile('files')) {
                $messageType = 'file';
                $message->update(['message_type' => 'file']);

                foreach ($request->file('files') as $file) {
                    // Generate unique filename
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    // Simpan ke storage
                    $path = $file->storeAs('chat_files', $filename, 'public');

                    // 🔥 PERBAIKAN: PASTIKAN URL LENGKAP
                    $fileUrl = url(Storage::url('chat_files/' . $filename));

                    // Simpan ke database
                    Attachment::create([
                        'attachable_type' => Message::class,
                        'attachable_id'   => $message->id,
                        'file_url'        => $fileUrl,  // 🔥 SEKARANG SELALU URL LENGKAP
                        'file_name'       => $file->getClientOriginalName(),
                        'file_size'       => $file->getSize(),
                        'file_type'       => $file->getMimeType(),
                        'uploaded_by'     => $userId,
                    ]);
                }
            }

            // Load relasi
            $message->load(['sender', 'attachments']);

            // Broadcast event
            event(new NewMessageSent($message));

            DB::commit();

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

    /**
     * Hapus pesan
     */
/**
 * Hapus pesan (soft delete) dan broadcast ke semua participant
 */
public function deleteMessage(Message $message)
{
    $userId = Auth::id();

    // Validasi: hanya sender yang bisa hapus pesannya sendiri
    if ($message->sender_id !== $userId) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    try {
        DB::beginTransaction();

        // 🔥 SOFT DELETE - hanya update deleted_at
        $message->update([
            'deleted_at' => now(),
            'content' => null // Kosongkan content
        ]);

        // Hapus attachments jika ada
        Attachment::where('attachable_type', Message::class)
                 ->where('attachable_id', $message->id)
                 ->delete();

        DB::commit();

        // 🔥 BROADCAST EVENT DELETE KE SEMUA PARTICIPANT
        event(new MessageDeleted($message));

        return response()->json(['success' => true]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Gagal hapus pesan: ' . $e->getMessage());
        return response()->json(['error' => 'Gagal menghapus pesan'], 500);
    }
}

    /**
     * Buat percakapan baru (grup atau DM).
     */
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

        // Pencegahan Duplikasi DM (Chat Pribadi)
        if ($request->type == 'private' && count($request->participants) == 1) {
            $otherUserId = $request->participants[0];

            if ($otherUserId == $authId) {
                return response()->json(['error' => 'Tidak dapat membuat percakapan dengan diri sendiri.'], 422);
            }

            // Cari conversation 'private'
            $existing = Conversation::where('type', 'private')
                ->where('workspace_id', $request->workspace_id)
                ->whereHas('participants', fn($q) => $q->where('user_id', $authId))
                ->whereHas('participants', fn($q) => $q->where('user_id', $otherUserId))
                ->has('participants', '=', 2) // <-- Perbaikan untuk PostgreSQL
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

            // Tambahkan peserta
            $participantIds = $request->participants;
            foreach ($participantIds as $participantId) {
                if ($participantId == $authId) continue;

                ConversationParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id'         => $participantId,
                    'is_admin'        => false,
                    'last_read_at'    => null, // <-- User lain belum membaca
                ]);
            }

            // Tambahkan si pembuat percakapan (creator)
            ConversationParticipant::firstOrCreate([
                'conversation_id' => $conversation->id,
                'user_id'         => $authId,
            ], [
                'is_admin' => true,
                'last_read_at' => now() // <-- Pembuat otomatis sudah "membaca"
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
     * 🔥 FUNGSI BARU UNTUK ME-RESET 'UNREAD COUNT' 🔥
     * Ini dipanggil oleh JavaScript saat user mengklik chat di sidebar.
     */
    /**
     * Tandai pesan sebagai sudah dibaca
     */
    public function markAsRead(string $conversationId)
    {
        $userId = Auth::id();

        try {
            // Update last_read_at participant
            ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $userId)
                ->update(['last_read_at' => now()]);

            // 🔥 UPDATE STATUS BACA PESAN MENGGUNAKAN is_read & read_at
            Message::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', $userId) // Hanya pesan dari orang lain
                ->where('is_read', false) // Yang belum dibaca
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
