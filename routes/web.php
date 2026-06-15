<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

// Root route is handled by Filament now

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/filament-logout', [LoginController::class, 'logout'])->name('filament.app.auth.logout');
