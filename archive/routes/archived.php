<?php
// routes/archived.php
// ========================================
// 🗄️ ARCHIVED ROUTES - Untuk Referensi
// File ini TIDAK di-load oleh aplikasi
// Simpan untuk dokumentasi atau kebutuhan future
// ========================================

use Illuminate\Support\Facades\Route;

// LEAVE MANAGEMENT ROUTES (Archived: 2025-01-15)
// Reason: Fitur cuti ditunda, akan dipakai nanti
Route::get('/cutikaryawan', function () {
    return view('archive.cutikaryawan');
})->name('cutikaryawan');

Route::get('/cutimanajer', function () {
    return view('archive.cutimanajer');
})->name('cutimanajer');

// INSIGHT ROUTES (Archived: 2025-01-15)
// Reason: Fitur insight belum jadi, design ulang
Route::get('/insight', function () {
    return view('archive.insight');
})->name('insight');

Route::get('/isi-insight', function () {
    return view('archive.isi-insight');
})->name('isi-insight');

// JADWAL OFFLINE ROUTES (Archived: 2025-01-15)
Route::get('/isiJadwalOffline', function () {
    return view('archive.isiJadwalOffline');
})->name('isiJadwalOffline');

Route::get('/isiJadwalOnline', function () {
    return view('archive.isiJadwalOnline');
})->name('isiJadwalOnline');

Route::get('/isiJadwalTidakAdaRapat', function () {
    return view('archive.isiJadwalTidakAdaRapat');
})->name('isiJadwalTidakAdaRapat');
