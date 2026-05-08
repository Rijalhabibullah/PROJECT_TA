<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\ClassificationHistoryController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
*/

// Classification endpoints
Route::prefix('classification')->group(function () {
    
    // Health check klasifikasi (alias test untuk backward compatibility)
    Route::get('/health', [ClassificationController::class, 'health']);
    Route::get('/info', [ClassificationController::class, 'info']);
    Route::get('/test', [ClassificationController::class, 'testConnection']);
    
    // Klasifikasi gambar (hanya analisis)
    Route::post('/classify', [ClassificationController::class, 'classify']);

    // Klasifikasi gambar dari URL
    Route::post('/classify-from-url', [ClassificationController::class, 'classifyFromUrl']);
    
    // Klasifikasi dan simpan gambar
    Route::post('/classify-and-save', [ClassificationController::class, 'classifyAndSave']);
});

// Mobile auth endpoint
Route::post('/mobile/login', [AuthController::class, 'mobileLogin']);
Route::post('/mobile/register', [AuthController::class, 'mobileRegister']);

// History endpoints
Route::prefix('classifications')->group(function () {
    
    // Get all classifications
    Route::get('/', [ClassificationHistoryController::class, 'index']);
    
    // Get classification detail
    Route::get('/{id}', [ClassificationHistoryController::class, 'show']);
    
    // Delete classification
    Route::delete('/{id}', [ClassificationHistoryController::class, 'destroy']);
    
    // Get statistics
    Route::get('/stats/summary', [ClassificationHistoryController::class, 'stats']);
});

// Products endpoint for mobile
Route::get('/products', [ProductController::class, 'apiIndex']);

