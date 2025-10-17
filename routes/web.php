<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Halaman default diarahkan ke dashboard
Route::get('/', function () {
    return view('dashboard');
});

// Halaman Daftar
Route::get('/daftar', function () {
    return view('daftar');
});

// Halaman Masuk
Route::get('/masuk', function () {
    return view('masuk');
});

// Halaman Buat Perusahaan
Route::get('/buat-perusahaan', function () {
    return view('buat-perusahaan');
});

// Halaman Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
});

// Halaman Dashboard Awal Tambah Anggota
Route::get('/dashboard-awal', function () {
    return view('dashboard-awal');
});

// Halaman Dashboard Awal Tambah Ruang Kerja
Route::get('/dashboard-awal-kerja', function () {
    return view('dashboard-awal-kerja');
});

// Halaman Tambah Anggota
Route::get('/tambah-anggota', function () {
    return view('tambah-anggota');
});

// Halaman Workspace
Route::get('/workspace', function () {
    return view('workspace');

});

// Halaman Jadwal
Route::get('/jadwal', function () {
    return view('jadwal');
});

//halaman buat jadwal
Route::get('/buatJadwal', function () {
    return view('buatJadwal');
})->name('buatJadwal');

//isiJadwalOnline
Route::get('/isiJadwalOnline', function () {
    return view('isiJadwalOnline');
});

//isiJadwalOffline
Route::get('/isiJadwalOffline', function () {
    return view('isiJadwalOffline');
});

//isiJadwalTidakAdaRapat
Route::get('/isiJadwalTidakAdaRapat', function () {
    return view('isiJadwalTidakAdaRapat');
});

//notulensi
Route::get('/notulensi', function () {
    return view('/notulensi');
});

//pengunguman
Route::get('/pengumuman', function () {
    return view('/pengumuman');
});

//statistik
Route::get('/statistik', function () {
    return view('/statistik');
});

//statistikRuangKerja
Route::get('/statistikRuangKerja', function () {
    return view('/statistikRuangKerja');
});

//pengunguman
Route::get('/pengunguman', function () {
    return view('/pengunguman');
});

//isiPengunguman
Route::get('/isiPengunguman', function () {
    return view('/isiPengunguman');
});

Route::get('/events', function () {
    return response()->json([
    ]);
});

// routes/web.php
Route::get('/kanban-tugas', function () {
    return view('kanban-tugas');
});

// routes/web.php
Route::get('/dokumen-dan-file', function () {
    return view('dokumen-dan-file');
});

// routes/web.php
Route::get('/kelola-workspace', function () {
    return view('kelola-workspace');
});

// Halaman Profile
Route::get('/profile', function () {
    return view('profile');

});

// Halaman Pengajuan Cuti Karyawan
Route::get('/cutikaryawan', function () {
    return view('cutikaryawan');
});

// Halaman Pengajuan Cuti Manajer
Route::get('/cutimanajer', function () {
    return view('cutimanajer');

});

// Halaman Chat
Route::get('/chat', function () {
    return view('chat');

});

// Halaman Insigght
Route::get('/insight', function () {
    return view('insight');

});

// Halaman isi Insigght
Route::get('/isi-insight', function () {
    return view('isi-insight');
})->name('isi-insight');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/masuk'); // arahkan ke halaman masuk
})->name('logout');
