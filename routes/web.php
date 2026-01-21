<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('universal-dashboard');
});

Route::get('/spa-dashboard', function () {
    return view('spa-dashboard');
});
