<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    });
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate']);
});

Route::middleware(['auth', 'superadmin'])->prefix('master-data')->name('master.')->group(function () {
    Route::resource('lembaga', \App\Http\Controllers\Master\LembagaController::class)->except(['create', 'show', 'edit']);
    Route::resource('jabatan', \App\Http\Controllers\Master\JabatanController::class)->except(['create', 'show', 'edit']);
    Route::resource('jenis-surat', \App\Http\Controllers\Master\JenisSuratController::class)->except(['create', 'show', 'edit']);
    Route::resource('tahun-akademik', \App\Http\Controllers\Master\TahunAkademikController::class)->except(['create', 'show', 'edit']);
});
