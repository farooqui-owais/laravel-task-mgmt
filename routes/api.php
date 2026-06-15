<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function (): void {

    // Public authentication endpoints
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.login');

    // Protected endpoints
    Route::middleware('auth:sanctum')->group(function (): void {

        // Auth
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('api.v1.me');

        

        // Users (admin only – enforced via Policy)
        Route::apiResource('users', UserController::class)->except(['show']);

        // Tasks
        Route::apiResource('tasks', TaskController::class);
    });
});
