<?php

use App\Http\Controllers\API\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/send', [NotificationController::class, 'send']);
    Route::get('/history/{recipientId}', [NotificationController::class, 'history']);
});


