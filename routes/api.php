<?php

use App\Helpers\File;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\ShowFileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
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

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::prefix('/auth')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/logout', [AuthController::class, 'logout']);
    });

    Route::get('/{provider}', [AuthController::class, 'redirectToProvider']);
    Route::get('/{provider}/callback', [AuthController::class, 'handleProviderCallback']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('/profile')->group(function () {
        Route::get('/current-user', [ProfileController::class, 'currentUser']);
        Route::patch('/update', [ProfileController::class, 'update']);
    });

    Route::prefix('/rooms')->group(function () {
        Route::get('/personal', [RoomController::class, 'personal']);
        Route::get('/group', [RoomController::class, 'group']);
    });

    Route::prefix('/chats')->group(function () {
        Route::get('/{roomId}/{lastSentAt?}/{lastMessageId?}', [ChatController::class, 'loadChats'])->middleware(['chatroom']);
        Route::post('/send/text', [ChatController::class, 'sendText']);
    });
});

Route::get('/files/{path}', ShowFileController::class)->where('path', '.*');

// Testing
Route::post('/image-store', function (Request $request) {
    try {
        $path = File::store($request->file('image'), "users/IniUserId/chats/IniRoomId");

        return response()->json([
            'path' => $path,
            'url' => File::getAWSUrl($path)
        ], 200);
    } catch (\Throwable $err) {
        return response()->json(['error' => $err->getMessage()]);
    }
});

Route::delete('/image-delete', function () {
    try {
        $var = File::delete('users/IniUserId2/avatar/OcIHUOijWZG2c95ooLwR3vYTz6FqEEckampN2VvK.png');

        return response()->json([
            'var' => $var,
            'message' => 'File deleted successfully'
        ], 200);
    } catch (\Throwable $err) {
        return response()->json(['error' => $err->getMessage()]);
    }
});
