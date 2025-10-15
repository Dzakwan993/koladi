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
Route::get('/pengunguman', function () {
    return view('/pengunguman');
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

