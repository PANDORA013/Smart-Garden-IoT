<?php

use Illuminate\Support\Facades\Route;

// Route utama menggunakan React SPA (Minimalist Design)
Route::get('/', function () {
    return view('spa-dashboard');
});

// Route untuk dashboard lama (backup)
Route::get('/old-dashboard', function () {
    return view('universal-dashboard');
});
