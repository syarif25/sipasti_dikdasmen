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
    
    Route::patch('tahun-akademik/{id}/activate', [\App\Http\Controllers\Master\TahunAkademikController::class, 'activate'])->name('tahun-akademik.activate');
    Route::resource('tahun-akademik', \App\Http\Controllers\Master\TahunAkademikController::class)->except(['create', 'show', 'edit']);
});

Route::middleware(['auth', 'superadmin'])->group(function () {
    Route::resource('pengguna', \App\Http\Controllers\PenggunaController::class)->except(['create', 'show', 'edit']);
    Route::resource('manajemen-pengajuan', \App\Http\Controllers\ManajemenPengajuanController::class)->except(['create', 'show', 'edit']);
});
Route::middleware(['auth'])->group(function () {
    // Pengajuan & Aksi Eskalasi
    Route::post('pengajuan/{id}/terima', [\App\Http\Controllers\PengajuanController::class, 'terima'])->name('pengajuan.terima');
    Route::post('pengajuan/{id}/teruskan', [\App\Http\Controllers\PengajuanController::class, 'teruskan'])->name('pengajuan.teruskan');
    Route::post('pengajuan/{id}/kembalikan', [\App\Http\Controllers\PengajuanController::class, 'kembalikan'])->name('pengajuan.kembalikan');
    Route::get('pengajuan/{id}/timeline', [\App\Http\Controllers\PengajuanController::class, 'timeline'])->name('pengajuan.timeline');
    Route::resource('pengajuan', \App\Http\Controllers\PengajuanController::class);

    // Arsip
    Route::resource('arsip', \App\Http\Controllers\ArsipController::class)->only(['index']);
});
