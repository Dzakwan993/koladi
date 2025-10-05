<?php

use Illuminate\Support\Facades\Route;

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

// Halaman Workspace
Route::get('/workspace', function () {
    return view('workspace');

});

