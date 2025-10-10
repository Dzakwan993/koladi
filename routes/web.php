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


Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/masuk'); // arahkan ke halaman masuk
})->name('logout');
