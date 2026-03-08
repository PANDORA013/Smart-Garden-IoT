<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

// Devices
Route::prefix('devices')->name('devices.')->group(function () {
    Route::get('/', function () { return view('devices.index'); })->name('index');
});

// Monitoring
Route::prefix('monitoring')->name('monitoring.')->group(function () {
    Route::get('/', function () { return view('monitoring.index'); })->name('index');
});

// Logs
Route::prefix('logs')->name('logs.')->group(function () {
    Route::get('/', function () { return view('logs.index'); })->name('index');
});

// Settings
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', function () { return view('settings.index'); })->name('index');
});

// Help
Route::prefix('help')->name('help.')->group(function () {
    Route::get('/', function () { return view('dashboard.index'); })->name('index');
});

// Legacy
Route::get('/universal-dashboard', function () { return view('universal-dashboard'); })->name('legacy.universal-dashboard');
