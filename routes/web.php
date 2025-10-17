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
