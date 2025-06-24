<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Product routes (WooCommerce focused)
    Route::get('/products', [ProductController::class, 'index']); // Get WooCommerce products
    Route::post('/products', [ProductController::class, 'store']); // Create product in WooCommerce
    Route::get('/products/{productId}', [ProductController::class, 'show']); // Get single WooCommerce product
    Route::put('/products/{productId}', [ProductController::class, 'update']); // Update WooCommerce product
    Route::delete('/products/{productId}', [ProductController::class, 'destroy']); // Delete WooCommerce product
});
