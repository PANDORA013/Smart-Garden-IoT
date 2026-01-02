<?php

use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Route;

// ===== DEVICE AUTO-PROVISIONING =====
// Arduino check-in untuk mendapatkan konfigurasi otomatis
Route::get('/device/check-in', [DeviceController::class, 'checkIn']);

// ===== DEVICE MANAGEMENT (CRUD) =====
Route::prefix('devices')->group(function () {
    Route::get('/', [DeviceController::class, 'index']);
    Route::get('/{id}', [DeviceController::class, 'show']);
    Route::put('/{id}', [DeviceController::class, 'update']);
    Route::delete('/{id}', [DeviceController::class, 'destroy']);
    Route::post('/{id}/preset', [DeviceController::class, 'applyPreset']);
    Route::post('/{id}/mode', [DeviceController::class, 'updateMode']); // NEW: Update mode operasi
});

// ===== MONITORING API (DATA SENSOR) =====
Route::prefix('monitoring')->group(function () {
    // Insert data dari mikrokontroler
    Route::post('/insert', [MonitoringController::class, 'insert']);
    
    // Ambil data terbaru
    Route::get('/latest', [MonitoringController::class, 'latest']);
    
    // Ambil history data (untuk chart)
    Route::get('/history', [MonitoringController::class, 'history']);
    
    // Statistics untuk dashboard
    Route::get('/stats', [MonitoringController::class, 'stats']);
    
    // Logs untuk activity log
    Route::get('/logs', [MonitoringController::class, 'logs']);
    
    // Toggle relay (kontrol manual)
    Route::post('/relay/toggle', [MonitoringController::class, 'toggleRelay']);
    
    // Cleanup data lama
    Route::delete('/cleanup', [MonitoringController::class, 'cleanup']);
});
