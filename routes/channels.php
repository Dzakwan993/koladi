<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ConversationParticipant;
use App\Models\UserCompany;

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

// 🔥 CHANNEL UNTUK CHAT (sudah ada)
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Cek apakah user adalah participant dari conversation ini
    return ConversationParticipant::where('conversation_id', $conversationId)
        ->where('user_id', $user->id)
        ->exists();
});

// 🆕 PRESENCE CHANNEL UNTUK ACTIVE USERS DI COMPANY
Broadcast::channel('presence-company.{companyId}', function ($user, $companyId) {
    // Cek apakah user adalah member dari company ini
    $userCompany = UserCompany::where('user_id', $user->id)
        ->where('company_id', $companyId)
        ->with('role')
        ->first();

    if (!$userCompany) {
        return false;
    }

    // Return data user yang akan ditampilkan di UI
    return [
        'id' => $user->id,
        'name' => $user->full_name,
        'email' => $user->email,
        'avatar' => $user->avatar ?? "https://ui-avatars.com/api/?name=" . urlencode($user->full_name) . "&background=225ad6&color=fff",
        'role' => $userCompany->role->name ?? 'Member'
    ];
});

// ✅ TAMBAHKAN INI - Private channel untuk notifikasi user
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (string) $user->id === (string) $userId;
});
