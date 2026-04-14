<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DatasetController;

// Halaman Login (GET)
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');

// Proses Login (POST)
Route::post('/login', [AuthController::class, 'login'])->name('login.proses');

// Proses Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Halaman Dashboard (Hanya bisa diakses jika sudah login)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::resource('produk', ProductController::class);
Route::resource('dataset', DatasetController::class);