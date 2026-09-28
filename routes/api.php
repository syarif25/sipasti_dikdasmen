<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


use App\Http\Controllers\Api\IntegrasiSipastiController;
Route::get("/integrasi/pengajuan-dikdasmen", [IntegrasiSipastiController::class, "getPengajuanSelesai"]);
Route::post("/integrasi/pengajuan/{id}/terima", [IntegrasiSipastiController::class, "terima"]);
Route::post("/integrasi/pengajuan/{id}/lanjut", [IntegrasiSipastiController::class, "lanjut"]);
Route::post("/integrasi/pengajuan/{id}/lanjut-dikti", [IntegrasiSipastiController::class, "lanjutDikti"]);
Route::post("/integrasi/pengajuan/{id}/arsip", [IntegrasiSipastiController::class, "arsipBerkas"]);
Route::post("/integrasi/pengajuan/{id}/kembali", [IntegrasiSipastiController::class, "kembali"]);
Route::post("/integrasi/pengajuan/{id}/finish", [IntegrasiSipastiController::class, "finish"]);
Route::get("/integrasi/pengajuan/{id}/log", [IntegrasiSipastiController::class, "log"]);
Route::get("/integrasi/arsip", [IntegrasiSipastiController::class, "arsip"]);
