<?php

use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Route;

// Device auto-provisioning
Route::get('/device/check-in', [DeviceController::class, 'checkIn']);

// Device management
Route::prefix('devices')->group(function () {
    Route::get('/', [DeviceController::class, 'index']);
    Route::get('/{id}', [DeviceController::class, 'show']);
    Route::put('/{id}', [DeviceController::class, 'update']);
    Route::delete('/{id}', [DeviceController::class, 'destroy']);
    Route::post('/{id}/preset', [DeviceController::class, 'applyPreset']);
    Route::post('/{id}/mode', [DeviceController::class, 'updateMode']);
    Route::post('/{id}/calibrate', [MonitoringController::class, 'calibrateSensor']);
    Route::post('/{id}/calibrate/reset', [MonitoringController::class, 'resetCalibration']);
});

// Monitoring API
Route::prefix('monitoring')->group(function () {
    Route::post('/insert', [MonitoringController::class, 'insert']);
    Route::get('/latest', [MonitoringController::class, 'latest']);
    Route::get('/history', [MonitoringController::class, 'history']);
    Route::get('/stats', [MonitoringController::class, 'stats']);
    Route::get('/logs', [MonitoringController::class, 'logs']);
    Route::post('/relay/toggle', [MonitoringController::class, 'toggleRelay']);
    Route::delete('/cleanup', [MonitoringController::class, 'cleanup']);
});

// Backward compatibility
Route::get('/monitoring', [MonitoringController::class, 'api_show']);
Route::post('/settings/update', [MonitoringController::class, 'updateSettings']);
