<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\WorkerAuthController;
use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WorkerController;
use App\Http\Controllers\Api\NotificationController;

// Customer auth
Route::prefix('user-auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Worker auth
Route::prefix('worker')->group(function () {
    Route::post('/register', [WorkerAuthController::class, 'register']);
    Route::post('/login',    [WorkerAuthController::class, 'login']);
});

// Services (public)
Route::get('/services',      [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);


Route::middleware('auth:sanctum')->group(function () {

    // Customer auth
    Route::prefix('user-auth')->group(function () {
        Route::post('/logout',            [AuthController::class, 'logout']);
        Route::get('/get-current-user',   [AuthController::class, 'me']);
    });

    // Worker auth
    Route::prefix('worker')->group(function () {
        Route::post('/logout',            [WorkerAuthController::class, 'logout']);
        Route::get('/me',                 [WorkerController::class, 'me']);
        Route::put('/update',             [WorkerController::class, 'update']);
        Route::post('/toggle-availability',[WorkerController::class, 'toggleAvailability']);
        Route::get('/my-services',        [WorkerController::class, 'myServices']);
        Route::post('/my-services',       [WorkerController::class, 'addService']);
        Route::delete('/my-services/{id}',[WorkerController::class, 'deleteService']);
    });

    // Addresses
    Route::get('/addresses',         [AddressController::class, 'index']);
    Route::post('/addresses',        [AddressController::class, 'store']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);

    // Bookings
    Route::get('/bookings/user',           [BookingController::class, 'userBookings']);
    Route::get('/bookings/worker',         [BookingController::class, 'workerBookings']);
    Route::get('/bookings/{id}',           [BookingController::class, 'show']);
    Route::put('/bookings/{id}/accept',    [BookingController::class, 'accept']);
    Route::put('/bookings/{id}/reject',    [BookingController::class, 'reject']);
    Route::put('/bookings/{id}/complete',  [BookingController::class, 'complete']);
    Route::put('/bookings/{id}/cancel',    [BookingController::class, 'cancel']);

    // Reviews
    Route::get('/reviews/worker/{workerId}', [ReviewController::class, 'workerReviews']);
    Route::post('/reviews',                  [ReviewController::class, 'store']);

    // Messages
    Route::get('/conversations',              [MessageController::class, 'getConversationList']);
    Route::get('/conversations/{workerId}',   [MessageController::class, 'getConversation']);
    Route::post('/messages',                  [MessageController::class, 'sendMessage']);
    Route::put('/messages/{id}/read',         [MessageController::class, 'markAsRead']);

    // Notifications
    Route::get('/notifications',              [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read',    [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all',     [NotificationController::class, 'markAllAsRead']);

    // Payments
    Route::post('/payment/intent',            [PaymentController::class, 'createPaymentIntent']);
    Route::post('/booking/confirm',           [PaymentController::class, 'confirmBooking']);
});
