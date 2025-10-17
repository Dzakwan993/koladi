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

// routes/web.php
Route::get('/kanban-tugas', function () {
    return view('kanban-tugas');
});

// routes/web.php
Route::get('/dokumen-dan-file', function () {
    return view('dokumen-dan-file');
});

// routes/web.php
Route::get('/kelola-workspace', function () {
    return view('kelola-workspace');
});

