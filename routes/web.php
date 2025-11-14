<?php

use App\Models\Company;
use App\Models\Workspace;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CompanyChatController;


// 🔥🔥🔥 TAMBAHKAN INI DI SINI (sebelum route lainnya) 🔥🔥🔥
Broadcast::routes(['middleware' => ['web', 'auth']]);

// ✅ TAMBAHKAN INI - Route Landing Page
Route::get('/', function () {
    // Jika sudah login, redirect ke dashboard
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    // Jika belum login, redirect ke halaman masuk
    return redirect()->route('masuk');
});

// Halaman Daftar
Route::get('/daftar', [AuthController::class, 'showRegister'])->name('daftar');
Route::post('/daftar', [AuthController::class, 'register'])->name('daftar.store');

// Halaman Masuk
Route::get('/masuk', [AuthController::class, 'showLogin'])->name('masuk');
Route::post('/masuk', [AuthController::class, 'login'])->name('login');

// Google OAuth Routes
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Kirim undangan
Route::post('/invite/send', [InvitationController::class, 'send'])->name('invite.send');
// Terima undangan (bisa di luar auth, karena penerima belum login)
Route::get('/invite/accept/{token}', [InvitationController::class, 'accept'])->name('invite.accept');


// ✅ UBAH: Pindahkan route hak-akses ke dalam middleware auth
Route::middleware(['auth'])->group(function () {

    // Dashboard - GUNAKAN INI SAJA
    Route::get('/dashboard', [CompanyController::class, 'dashboard'])->name('dashboard');

    // 🆕 TAMBAHKAN INI - Route halaman member removed
    Route::get('/member-removed', [CompanyController::class, 'memberRemoved'])->name('member.removed');

    // Halaman Dashboard Awal Tambah Anggota
    Route::get('/dashboard-awal', function () {
        return view('dashboard-awal');
    })->name('dashboard-awal');

    // Halaman Dashboard Awal Tambah Ruang Kerja
    Route::get('/dashboard-awal-kerja', function () {
        return view('dashboard-awal-kerja');
    })->name('dashboard-awal-kerja');

    // Buat perusahaan
    Route::get('/buat-perusahaan', [CompanyController::class, 'create'])->name('buat-perusahaan.create');
    Route::post('/buat-perusahaan', [CompanyController::class, 'store'])->name('buat-perusahaan');

    // Switch perusahaan
    Route::get('/company/switch/{company}', [CompanyController::class, 'switchCompany'])->name('company.switch');
    Route::put('/company/{id}/update', [CompanyController::class, 'update'])->name('company.update');
    Route::delete('/company/{id}/delete', [CompanyController::class, 'destroy'])->name('company.destroy');

    Route::get('/tambah-anggota', [CompanyController::class, 'showMembers'])->name('tambah-anggota');
    // Hapus anggota perusahaan
    Route::delete('/members/{id}/delete', [CompanyController::class, 'deleteMember'])->name('member.delete');
    // Hapus undangan pending
    Route::delete('/invitation/{id}/delete', [InvitationController::class, 'delete'])->name('invitation.delete');

    // Halaman profil
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

    // --- Workspace ---
    Route::get('/workspace/{workspace}', function (Workspace $workspace) {
        return view('workspace', ['workspace' => $workspace]);
    })->name('workspace');

    // ✅ ROUTE untuk company chat
    Route::get('/company/{company}/chat', function (Company $company) {
        // Validasi apakah user punya akses ke company ini
        $hasAccess = \App\Models\UserCompany::where('user_id', Auth::id())
            ->where('company_id', $company->id)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke perusahaan ini');
        }

        return view('company-chat', ['company' => $company]);
    })->name('company.chat');

    // LANGKAH 1: Rute untuk menampilkan HALAMAN (VIEW) chat
    Route::get('/workspace/{workspace}/chat', function (Workspace $workspace) {
        return view('chat', ['workspace' => $workspace]);
    })->name('chat');

    // LANGKAH 2: Grup semua rute API chat Anda di bawah prefix '/api'
    Route::prefix('api')->name('api.')->group(function () {
        // Rute untuk mengambil daftar chat & anggota (JSON)
        Route::get('/workspace/{workspaceId}/chat', [ChatController::class, 'index'])->name('chat.index');
        // Rute untuk mengambil pesan (JSON)
        Route::get('/chat/{conversationId}/messages', [ChatController::class, 'showMessages'])->name('chat.messages');
        // Rute untuk mengirim pesan (POST)
        Route::post('/chat/send', [ChatController::class, 'store'])->name('chat.store');
        // Rute untuk membuat percakapan baru (POST)
        Route::post('/chat/create', [ChatController::class, 'createConversation'])->name('chat.create');
        // Edit message
        Route::put('/chat/message/{message}', [ChatController::class, 'editMessage'])
            ->middleware('auth');
        // PASTIKAN ada route DELETE
        Route::delete('/chat/message/{message}', [ChatController::class, 'deleteMessage']);
        // Rute untuk menandai telah dibaca (POST)
        Route::post('/chat/{conversationId}/mark-as-read', [ChatController::class, 'markAsRead'])->name('chat.markAsRead');
        // Di Controller Chat, pastikan include avatar
        $conversations = Conversation::with(['participants.user' => function ($query) {
            $query->select('id', 'full_name', 'avatar'); // 🔥 INCLUDE AVATAR
        }])->get();



        // 🆕 TAMBAHKAN: Company Chat Routes
        Route::prefix('company')->group(function () {
            Route::get('/{companyId}/chat-data', [CompanyChatController::class, 'getChatData']);
            Route::get('/chat/{conversationId}/messages', [CompanyChatController::class, 'showMessages']);
            Route::post('/chat/send', [CompanyChatController::class, 'store']);
            Route::put('/chat/message/{message}', [CompanyChatController::class, 'editMessage']);
            Route::delete('/chat/message/{message}', [CompanyChatController::class, 'deleteMessage']);
            Route::post('/chat/create', [CompanyChatController::class, 'createConversation']);
            Route::post('/chat/{conversationId}/mark-as-read', [CompanyChatController::class, 'markAsRead']);
        });
    });

    // Halaman Jadwal
    Route::get('/jadwal', function () {
        return view('jadwal');
    })->name('jadwal');

    // Halaman buat jadwal
    Route::get('/buatJadwal', function () {
        return view('buatJadwal');
    })->name('buatJadwal');

    // isiJadwalOnline
    Route::get('/isiJadwalOnline', function () {
        return view('isiJadwalOnline');
    })->name('isiJadwalOnline');

    // isiJadwalOffline
    Route::get('/isiJadwalOffline', function () {
        return view('isiJadwalOffline');
    })->name('isiJadwalOffline');

    // isiJadwalTidakAdaRapat
    Route::get('/isiJadwalTidakAdaRapat', function () {
        return view('isiJadwalTidakAdaRapat');
    })->name('isiJadwalTidakAdaRapat');

    // notulensi
    Route::get('/notulensi', function () {
        return view('notulensi');
    })->name('notulensi');

    // pengumuman
    Route::get('/pengumuman', function () {
        return view('pengumuman');
    })->name('pengumuman');

    // statistik
    Route::get('/statistik', function () {
        return view('statistik');
    })->name('statistik');

    // statistikRuangKerja
    Route::get('/statistikRuangKerja', function () {
        return view('statistikRuangKerja');
    })->name('statistikRuangKerja');

    // isiPengumuman
    Route::get('/isiPengunguman', function () {
        return view('isiPengunguman');
    })->name('isiPengunguman');

    // Events
    Route::get('/events', function () {
        return response()->json([]);
    })->name('events');

    // kanban-tugas
    Route::get('/kanban-tugas', function () {
        return view('kanban-tugas');
    })->name('kanban-tugas');

    // dokumen-dan-file
    Route::get('/dokumen-dan-file', function () {
        return view('dokumen-dan-file');
    })->name('dokumen-dan-file');

    // ✅ WORKSPACE ROUTES - DIPINDAHKAN KE DALAM AUTH GROUP
    Route::get('/kelola-workspace', [WorkspaceController::class, 'index'])->name('kelola-workspace');
    Route::post('/workspace', [WorkspaceController::class, 'store'])->name('workspace.store');
    Route::put('/workspace/{id}', [WorkspaceController::class, 'update'])->name('workspace.update');
    Route::delete('/workspace/{id}', [WorkspaceController::class, 'destroy'])->name('workspace.destroy');
    Route::post('/workspace/{workspaceId}/members', [WorkspaceController::class, 'manageMembers'])->name('workspace.manage-members');
    Route::get('/workspace/{workspaceId}/members', [WorkspaceController::class, 'getMembers'])->name('workspace.get-members');
    Route::get('/workspace-available-users', [WorkspaceController::class, 'getAvailableUsers'])->name('workspace.available-users');

    // Halaman Pengajuan Cuti Karyawan
    Route::get('/cutikaryawan', function () {
        return view('cutikaryawan');
    })->name('cutikaryawan');

    // Halaman Pengajuan Cuti Manajer
    Route::get('/cutimanajer', function () {
        return view('cutimanajer');
    })->name('cutimanajer');


    // Halaman Insight
    Route::get('/insight', function () {
        return view('insight');
    })->name('insight');

    // Halaman isi Insight
    Route::get('/isi-insight', function () {
        return view('isi-insight');
    })->name('isi-insight');

    // mindmap
    Route::get('/mindmap', function () {
        return view('mindmap');
    })->name('mindmap');


    // Halaman Pembayaran
    Route::get('/pembayaran', function () {
        return view('pembayaran');
    })->name('pembayaran');

    // Logout
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

    // ✅ TAMBAHKAN: Route untuk hak akses (pindahkan ke dalam middleware)
    Route::get('/hak-akses', [UserController::class, 'hakAkses'])->name('hakAkses');
    Route::post('/update-user-roles', [UserController::class, 'updateUserRoles'])->name('user.updateRoles');
});
