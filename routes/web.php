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

// Halaman Workspace
Route::get('/workspace', function () {
    return view('workspace');
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
