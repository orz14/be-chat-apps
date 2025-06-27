<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\RoomController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/auth')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/current-user', [AuthController::class, 'currentUser']);
    });

    Route::get('/{provider}', [AuthController::class, 'redirectToProvider']);
    Route::get('/{provider}/callback', [AuthController::class, 'handleProviderCallback']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('/rooms')->group(function () {
        Route::get('/personal', [RoomController::class, 'personal']);
        Route::get('/group', [RoomController::class, 'group']);
    });

    Route::prefix('/chats')->group(function () {
        Route::get('/{roomId}/{lastSentAt?}/{lastMessageId?}', [ChatController::class, 'loadChats']);
    });
});
