<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;

// Halaman default diarahkan ke dashboard
Route::get('/', function () {
    return view('dashboard');
});

// Halaman Daftar
Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
Route::post('/daftar', [AuthController::class, 'register']);

// Halaman Masuk
Route::get('/masuk', [AuthController::class, 'showLogin'])->name('masuk');
Route::post('/masuk', [AuthController::class, 'login']);

// Halaman Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Halaman Buat Perusahaan
Route::get('/buat-perusahaan', [CompanyController::class, 'create'])->name('companies.create');
Route::post('/buat-perusahaan', [CompanyController::class, 'store'])->name('companies.store');

// Halaman Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Halaman Workspace
Route::get('/workspace', function () {
    return view('workspace');

});

