<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Add login route for authentication redirects
Route::get('/login', function () {
    return response()->json(['message' => 'Please authenticate via API'], 401);
})->name('login');
