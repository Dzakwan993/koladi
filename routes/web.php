<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;

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

// ✅ GRUP ROUTES YANG BUTUH AUTH
Route::middleware(['auth'])->group(function () {

    // Dashboard - GUNAKAN INI SAJA
    Route::get('/dashboard', [CompanyController::class, 'dashboard'])->name('dashboard');

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

    // Halaman Workspace
    Route::get('/workspace', function () {
        return view('workspace');
    })->name('workspace');

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

    // kelola-workspace
    Route::get('/kelola-workspace', function () {
        return view('kelola-workspace');
    })->name('kelola-workspace');

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

    // Halaman Pembayaran
    Route::get('/pembayaran', function () {
        return view('pembayaran');
    })->name('pembayaran');

    // Logout
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
});
