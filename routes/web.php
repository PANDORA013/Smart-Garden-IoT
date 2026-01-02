<?php

use Illuminate\Support\Facades\Route;

// Dashboard SPA (Single Page Application) dengan Navbar Biru
Route::get('/', function () {
    return view('spa-dashboard');
});

// Alternative: Dashboard Universal (versi lama)
Route::get('/classic', function () {
    return view('universal-dashboard');
});
