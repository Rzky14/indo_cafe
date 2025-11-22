<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\AuthController;
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
});

// Protected routes (require Sanctum authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/me', [AuthController::class, 'me']);

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        // Admin routes will be added here (user management, reports, etc)
    });

    // Admin & Cashier routes
    Route::middleware('role:admin,cashier')->group(function () {
        // Shared routes for admin and cashier (orders, payments, etc)
    });

    // Customer only routes
    Route::middleware('role:customer')->group(function () {
        // Customer routes (view menu, place orders, order history)
    });
});
