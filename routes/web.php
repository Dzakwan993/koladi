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

// Halaman Jadwal
Route::get('/jadwal', function () {
    return view('jadwal');
});

//halaman buat jadwal
Route::get('/buatJadwal', function () {
    return view('buatJadwal');
})->name('buatJadwal');


Route::get('/events', function () {
    return response()->json([
    ]);
});

