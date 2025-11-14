<?php

use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserWorkspacesController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Auth\GoogleController;
use App\Models\Workspace;

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

    // Halaman Tambah Anggota
    Route::get('/tambah-anggota', function () {
        return view('tambah-anggota');
    })->name('tambah-anggota');

    Route::get('/tambah-anggota', [CompanyController::class, 'showMembers'])->name('tambah-anggota');
    // Hapus anggota perusahaan
    Route::delete('/members/{id}/delete', [CompanyController::class, 'deleteMember'])->name('member.delete');
    // Hapus undangan pending
    Route::delete('/invitation/{id}/delete', [InvitationController::class, 'delete'])->name('invitation.delete');

    // Halaman profil
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

    // Halaman Workspace
    Route::get('/workspace/{id}', function ($id) {
        $workspace = Workspace::findOrFail($id);
        return view('workspace', compact('workspace'));
    })->name('workspace.show');

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
    Route::middleware(['auth'])->group(function () {
        Route::post('/workspace/{id}/pengumuman/store', [PengumumanController::class, 'store'])
            ->name('pengumuman.store');
        Route::get('/pengumuman/anggota/{workspaceId}',[App\Http\Controllers\PengumumanController::class, 'getAnggota'])->name('pengumuman.anggota');

        Route::get('/pengumuman/{pengumuman}', [PengumumanController::class, 'show'])->name('pengumuman.show');

        Route::get('/comments/{pengumuman}', [CommentController::class, 'index'])->name('comments.index');
        Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::post('/comments/reply', [CommentController::class, 'reply'])->name('comments.reply');

        //uploadfile
        Route::post('/upload', [App\Http\Controllers\AttachmentController::class, 'upload'])->name('upload');

        //upload image
        Route::post('/upload-image', [AttachmentController::class, 'uploadImage'])->name('upload.image');

        //untuk edit pengumuman
        Route::get('/pengumuman/{id}/edit-data', [PengumumanController::class, 'getEditData'])->name('pengumuman.edit.data');
        Route::put('/pengumuman/{id}', [PengumumanController::class, 'update'])->name('pengumuman.update');

        Route::delete('/pengumuman/{pengumuman}', [PengumumanController::class, 'destroy'])
            ->name('pengumuman.destroy');

        Route::get('/workspace/{id}/pengumuman', [PengumumanController::class, 'index'])->name('workspace.pengumuman');
    });

    Route::get('/workspace/{workspaceId}', [UserWorkspacesController::class, 'show'])->name('workspace.show');

    //role workspaces
    Route::get('/api/workspaces/{workspace_id}/members', [UserWorkspacesController::class, 'index'])
        ->name('api.workspace.members');

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

    // Halaman Profile
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    // Halaman Pengajuan Cuti Karyawan
    Route::get('/cutikaryawan', function () {
        return view('cutikaryawan');
    })->name('cutikaryawan');

    // Halaman Pengajuan Cuti Manajer
    Route::get('/cutimanajer', function () {
        return view('cutimanajer');
    })->name('cutimanajer');

    // Halaman Chat
    Route::get('/chat', function () {
        return view('chat');
    })->name('chat');

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
