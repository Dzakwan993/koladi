<?php

use Illuminate\Support\Facades\Route;

// Halaman default diarahkan ke dashboard
Route::get('/', function () {
    return view('dashboard');
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

