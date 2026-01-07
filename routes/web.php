<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('universal-dashboard');
});

// Route untuk React SPA (Minimalist Design)
Route::get('/spa-dashboard', function () {
    return view('spa-dashboard');
});
