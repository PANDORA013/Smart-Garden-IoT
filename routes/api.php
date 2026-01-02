<?php

use App\Http\Controllers\MonitoringController;
use Illuminate\Support\Facades\Route;

// API Routes untuk ESP32/Arduino
Route::prefix('monitoring')->group(function () {
    // Insert data dari mikrokontroler
    Route::post('/insert', [MonitoringController::class, 'insert']);
    
    // Ambil data terbaru
    Route::get('/latest', [MonitoringController::class, 'latest']);
    
    // Ambil history data
    Route::get('/history', [MonitoringController::class, 'history']);
    
    // Cleanup data lama
    Route::delete('/cleanup', [MonitoringController::class, 'cleanup']);
});
