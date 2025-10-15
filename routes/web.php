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

// Halaman Tambah Anggota
Route::get('/tambah-anggota', function () {
    return view('tambah-anggota');
    
});