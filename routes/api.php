<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DepartmentApiController;
use App\Http\Controllers\Api\SuratPermohonanController;

Route::middleware('api')->group(function () {
    Route::get('/departments/{department}', [DepartmentApiController::class, 'show']);
});

// Protected upload endpoints - require authentication
Route::middleware(['api'])->group(function () {
    Route::post('/surat-permohonan/upload', [SuratPermohonanController::class, 'uploadAndScan']);
    Route::post('/surat-laporan/upload', [SuratPermohonanController::class, 'uploadLaporan']);
});
