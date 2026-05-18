<?php

use App\Http\Controllers\API\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/send', [NotificationController::class, 'send']);
Route::get('/history/{recipientId}', [NotificationController::class, 'history']);
