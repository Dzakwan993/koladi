<?php

use App\Models\User;
use App\Models\ConversationParticipant; // <-- IMPORT MODEL ANDA
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Di sinilah Anda mendaftarkan semua "petugas keamanan"
| (authorization callbacks) untuk aplikasi Anda.
|
*/

// Hapus kode 'App.Models.User.{id}' yang lama dan ganti dengan ini:

// Ini adalah "Petugas Keamanan" (Security Guard) untuk chat privat Anda
// Nama channel 'conversation.{conversationId}' HARUS SAMA PERSIS
// dengan yang ada di NewMessageSent.php dan chat.js
Broadcast::channel('conversation.{conversationId}', function (User $user, string $conversationId) {

    // Logika Pengecekan:
    // "Apakah user yang sedang login ini ($user->id)
    //  terdaftar sebagai peserta di tabel 'conversation_participants'
    //  untuk percakapan ($conversationId) ini?"

    // Kita gunakan model Anda: ConversationParticipant
    return ConversationParticipant::where('user_id', $user->id)
        ->where('conversation_id', $conversationId)
        ->exists();
});
