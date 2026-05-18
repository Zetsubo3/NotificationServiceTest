<?php

use App\Http\Controllers\API\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/send', [NotificationController::class, 'send'])->middleware('rate.limit:send,10,60,user_id');
    Route::get('/history/{recipientId}', [NotificationController::class, 'history'])->middleware('rate.limit:history,60,60,user_id');
});


