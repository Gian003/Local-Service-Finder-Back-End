<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\WorkerAuthController;
use App\Http\Controllers\Api\MessageController;
use App\Models\Message;
use Illuminate\Support\Facades\Route;

//Customer Routes
Route::prefix('user-auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/get-current-user', [AuthController::class, 'getCurrentUser']);
    });
});

//Worker Routes
Route::prefix('worker-auth')->group(function () {
    Route::post('/register', [WorkerAuthController::class, 'register']);
    Route::post('/login', [WorkerAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [WorkerAuthController::class, 'logout']);
    });
});

//Service Routes
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);

//Message Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/conversations', [MessageController::class, 'getConversationList']);
    Route::get('/conversations/{workerId}', [MessageController::class, 'getConversation']);
    Route::post('/messages', [MessageController::class, 'sendMessage']);
    Route::put('/messages/{messageId}/read', [MessageController::class, 'markAsRead']);
});
