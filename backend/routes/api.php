<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Menu\MenuController;
use App\Http\Controllers\Api\Order\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| RESTful API with Laravel Sanctum authentication
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Password reset routes
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Public menu routes (view only)
    Route::get('/menu', [MenuController::class, 'index']);
    Route::get('/menu/{id}', [MenuController::class, 'show']);
    Route::get('/menu/category/{category}', [MenuController::class, 'byCategory']);
});

// Protected routes (require Sanctum authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/me', [AuthController::class, 'me']);

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        // Menu management (admin only)
        Route::post('/menu', [MenuController::class, 'store']);
        Route::put('/menu/{id}', [MenuController::class, 'update']);
        Route::delete('/menu/{id}', [MenuController::class, 'destroy']);
    });

    // Admin & Cashier routes
    Route::middleware('role:admin,cashier')->group(function () {
        // Order management (admin & cashier can update status and payment)
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::put('/orders/{id}/payment-status', [OrderController::class, 'updatePaymentStatus']);
    });

    // Authenticated user routes (all roles can access)
    // Orders - customers see only their orders, admin/cashier see all
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Customer only routes
    Route::middleware('role:customer')->group(function () {
        // Customer routes (view menu, place orders, order history)
    });
});
