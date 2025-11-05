<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Workspace;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Events\NewMessageSent;
use Illuminate\Support\Facades\DB;
// <-- TAMBAHAN: Import Event untuk real-time broadcast
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ConversationParticipant;

class ChatController extends Controller
{
    /**
     * Ambil semua percakapan user di workspace tertentu.
     */
    // Di dalam ChatController.php

    public function index(string $workspaceId)
    {
        $userId = Auth::id();

        // 1. Ambil data workspace (kita butuh namanya)
        $workspace = Workspace::findOrFail($workspaceId);

        // 2. Ambil atau Buat Grup Utama (misal: "Koladi")
        // Ini adalah logika 'findOrCreate' sederhana
        $mainGroup = Conversation::firstOrCreate(
            [
                'workspace_id' => $workspaceId,
                'type' => 'group',
                'name' => $workspace->name // "Koladi"
            ],
            [
                // Anda bisa set 'created_by' jika perlu, misal oleh admin
                'created_by' => $workspace->user_id // Asumsi owner workspace
            ]
        );

        // 3. Pastikan user ini (dan semua anggota) adalah peserta grup utama
        // Catatan: Idealnya, ini dilakukan saat user ditambahkan ke workspace
        ConversationParticipant::firstOrCreate(
            [
                'conversation_id' => $mainGroup->id,
                'user_id' => $userId
            ]
            // 'is_admin' bisa ditambahkan di sini jika perlu
        );

        // 4. Ambil SEMUA percakapan user (DM atau grup lain)
        // KITA KECUALIKAN grup utama agar tidak duplikat
        $otherConversations = Conversation::with(['participants.user', 'messages' => fn($q) => $q->latest()->limit(1), 'messages.sender'])
            ->where('workspace_id', $workspaceId)
            ->where('id', '!=', $mainGroup->id) // <-- Penting: Jangan ambil grup utama lagi
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId))
            ->orderByRaw('CASE WHEN (SELECT count(*) FROM messages WHERE conversation_id = conversations.id) > 0 THEN (SELECT created_at FROM messages WHERE conversation_id = conversations.id ORDER BY created_at DESC LIMIT 1) ELSE conversations.created_at END DESC')
            ->get();

        // 5. Ambil List Anggota Workspace (untuk memulai DM baru)
        $members = $workspace->users() // <-- Gunakan relasi 'users()'
            ->where('users.id', '!=', $userId)
            ->get();

        // 6. Load relasi untuk mainGroup (agar formatnya sama)
        $mainGroup->load(['participants.user', 'messages' => fn($q) => $q->latest()->limit(1), 'messages.sender']);

        // 7. Gabungkan hasilnya dalam format JSON yang baru
        return response()->json([
            'main_group' => $mainGroup,           // Grup "Koladi"
            'conversations' => $otherConversations, // DM / Grup lain
            'members' => $members,                // List anggota
        ]);
    }

    /**
     * Ambil pesan berdasarkan conversation.
     */
    public function showMessages(string $conversationId)
    {
        $userId = Auth::id();

        // 1. Validasi dulu (Kode ini sudah Anda tambahkan, bagus!)
        $isParticipant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->exists();

        if (!$isParticipant) {
            return response()->json(['error' => 'Akses ditolak'], 403); // 403 Forbidden
        }

        // 2. Jika aman, baru ambil pesan
        $messages = Message::with('sender')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        // TODO: Tandai pesan sebagai "dibaca" (is_read = true)

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

        // <-- TAMBAHAN: Validasi Otorisasi
        // Cek apakah user ini adalah peserta percakapan
        $isParticipant = ConversationParticipant::where('conversation_id', $request->conversation_id)
            ->where('user_id', $userId)
            ->exists();

        if (!$isParticipant) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }
        // --- Akhir Validasi Otorisasi ---

        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id'       => $userId,
            'content'         => $request->input('content'),
        ]);

        // Load relasi sender agar bisa dikirim ke frontend
        $message->load('sender');

        // // <-- TAMBAHAN: Broadcast pesan ke WebSocket (Real-time)
        // // Pastikan Anda sudah membuat event: php artisan make:event NewMessageSent
        // // Event ini akan diterima oleh user LAIN yang sedang membuka chat
        // try {
        //     broadcast(new NewMessageSent($message))->toOthers();
        // } catch (\Exception $e) {
        //     // Opsional: Log error jika broadcast gagal (misal: server Reverb/Pusher mati)
        //     Log::error('Broadcast failed: ' . $e->getMessage());
        // }
        // // --- Akhir Broadcast ---

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
            'name' => 'nullable|string|max:100', // Batasi panjang nama grup
            'participants' => 'required|array|min:1',
            'participants.*' => 'required|uuid|exists:users,id', // Validasi tiap ID
        ]);

        $authId = Auth::id();

        // <-- TAMBAHAN: Pencegahan Duplikasi DM (Chat Pribadi)
        if ($request->type == 'private' && count($request->participants) == 1) {
            $otherUserId = $request->participants[0];

            // Tidak boleh chat dengan diri sendiri
            if ($otherUserId == $authId) {
                return response()->json(['error' => 'Tidak dapat membuat percakapan dengan diri sendiri.'], 422);
            }

            // Cari conversation 'private' di workspace ini
            // yang anggotanya HANYA 2 orang ini
            $existing = Conversation::where('type', 'private')
                ->where('workspace_id', $request->workspace_id)
                ->whereHas('participants', fn($q) => $q->where('user_id', $authId))
                ->whereHas('participants', fn($q) => $q->where('user_id', $otherUserId))
                ->has('participants', '=', 2)
                ->first();

            if ($existing) {
                // JANGAN BUAT BARU. Kembalikan data conversation yang sudah ada.
                return response()->json([
                    'success'      => true,
                    'conversation' => $existing->load('participants.user'),
                    'existed'      => true // Flag untuk frontend
                ]);
            }
        }
        // --- Akhir Pencegahan Duplikasi DM ---

        DB::beginTransaction();

        try {
            $conversation = Conversation::create([
                'workspace_id' => $request->workspace_id,
                'type'         => $request->type,
                'name'         => $request->type == 'group' ? $request->name : null, // Hanya grup yg punya nama
                'created_by'   => $authId,
            ]);

            // Tambahkan peserta
            $participantIds = $request->participants;
            foreach ($participantIds as $participantId) {
                // Hindari duplikasi jika user mengirim ID-nya sendiri di array
                if ($participantId == $authId) continue;

                ConversationParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id'         => $participantId,
                    'is_admin'        => false, // Default
                ]);
            }

            // Tambahkan si pembuat percakapan (creator)
            ConversationParticipant::firstOrCreate([
                'conversation_id' => $conversation->id,
                'user_id'         => $authId,
            ], [
                'is_admin' => true // Pembuat grup otomatis jadi admin
            ]);

            DB::commit();

            // <-- TAMBAHAN: Load relasi agar data yg dikembalikan lengkap
            $conversation->load('participants.user');

            // TODO: Broadcast "NewConversation" agar muncul di list chat peserta lain

            return response()->json([
                'success' => true,
                'conversation' => $conversation,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
