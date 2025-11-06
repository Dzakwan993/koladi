<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Workspace;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Events\NewMessageSent; // <-- 🔥 PERBAIKAN PENTING ADA DI SINI
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ConversationParticipant;

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
        // DAN tambahkan last_read_at jika ini pertama kalinya
        $mainGroupParticipant = ConversationParticipant::firstOrCreate(
            [
                'conversation_id' => $mainGroup->id,
                'user_id' => $userId
            ],
            ['last_read_at' => now()] // <-- Set last_read_at saat join
        );

        // --- 2. Ambil Percakapan Lain (DM & Grup Kustom) ---
        $otherConversations = Conversation::where('workspace_id', $workspaceId)
            ->where('id', '!=', $mainGroup->id)
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->with(['participants.user', 'lastMessage.sender']) // <-- Gunakan relasi lastMessage()
            ->get(); // Ambil dulu, baru kita hitung unread

        // --- 3. Hitung Unread Count (Bagian Penting) ---
        // Kita gabungkan semua percakapan untuk dihitung
        $allConversations = $otherConversations->push($mainGroup);

        foreach ($allConversations as $conversation) {
            // Ambil data 'last_read_at' dari pivot
            $participantData = $conversation->participants->where('user_id', $userId)->first();
            $lastReadAt = $participantData ? $participantData->last_read_at : null;

            if ($lastReadAt) {
                // Hitung pesan baru (dari orang lain) setelah tanggal 'last_read_at'
                $conversation->unread_count = Message::where('conversation_id', $conversation->id)
                    ->where('sender_id', '!=', $userId)
                    ->where('created_at', '>', $lastReadAt)
                    ->count();
            } else {
                // Jika user belum pernah membaca (null), hitung semua pesan
                $conversation->unread_count = $conversation->messages()->where('sender_id', '!=', $userId)->count();
            }
        }

        // --- 4. Ambil Anggota Tim ---
        $members = $workspace->users()
            ->where('users.id', '!=', $userId)
            ->get();

        // --- 5. Kembalikan Respon ---
        // Pisahkan lagi grup utama dari percakapan lain
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

        // Ambil pesan
        $messages = Message::with('sender')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        // 🔥 Tandai sudah dibaca SAAT user membuka pesan
        // (Ini akan dieksekusi setelah 'markAsRead' dari frontend)
        try {
            ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $userId)
                ->update(['last_read_at' => now()]);
        } catch (\Exception $e) {
            Log::error('Gagal update last_read_at di showMessages: ' . $e->getMessage());
        }

        return response()->json($messages);
    }

    /**
     * Kirim pesan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|uuid|exists:conversations,id',
            'content' => 'required|string',
        ]);

        $userId = Auth::id();

        // Validasi Otorisasi
        $isParticipant = ConversationParticipant::where('conversation_id', $request->conversation_id)
            ->where('user_id', $userId)
            ->exists();

        if (!$isParticipant) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id'       => $userId,
            'content'         => $request->input('content'),
        ]);

        // Load relasi sender agar bisa dikirim ke frontend
        $message->load('sender');

        // 🔥 Broadcast pesan ke WebSocket (Real-time)
        try {
            broadcast(new NewMessageSent($message))->toOthers();
        } catch (\Exception $e) {
            // Opsional: Log error jika broadcast gagal
            Log::error('Broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
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
    public function markAsRead(string $conversationId)
    {
        $userId = Auth::id();

        try {
            ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $userId)
                ->update(['last_read_at' => now()]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Gagal markAsRead: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}
