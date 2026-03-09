<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RpaController;
use App\Http\Controllers\Api\DepartmentApiController;

Route::middleware('api')->group(function () {
    Route::post('/rpa/result', [RpaController::class, 'store']);
    Route::get('/departments/{department}', [DepartmentApiController::class, 'show']);
});
