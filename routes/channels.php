<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ConversationParticipant;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Di sini kita daftarkan semua channel broadcasting yang memerlukan
| autorisasi. Ini memberi aplikasi kita keamanan untuk memastikan hanya
| user yang berhak yang bisa mendengarkan channel private/presence.
|
*/

// 🔥 CHANNEL UNTUK CHAT
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Cek apakah user adalah participant dari conversation ini
    return ConversationParticipant::where('conversation_id', $conversationId)
        ->where('user_id', $user->id)
        ->exists();
});
