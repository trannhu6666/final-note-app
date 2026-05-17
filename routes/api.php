<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\SharedNoteController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Group các route cần phải có Token mới gọi được
Route::middleware('auth:sanctum')->group(function () {
    // Chúng ta sẽ viết các API liên quan đến Note ở đây sau
    Route::get('/test-auth', function (Request $request) {
        return $request->user();
    });
});

// Routes không cần đăng nhập
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Routes cần đăng nhập (nằm bên trong middleware auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Nhóm Note (Checkpoint 3)
    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::put('/notes/{id}', [NoteController::class, 'update']);
    Route::delete('/notes/{id}', [NoteController::class, 'destroy']);

    // Nhóm Label (Checkpoint 3)
    Route::get('/labels', [LabelController::class, 'index']);
    Route::post('/labels', [LabelController::class, 'store']);
    Route::put('/labels/{id}', [LabelController::class, 'update']);
    Route::delete('/labels/{id}', [LabelController::class, 'destroy']);

    // API Nâng cao cho Note (Mật khẩu)
    Route::post('/notes/{id}/password', [NoteController::class, 'setPassword']);
    Route::post('/notes/{id}/unlock', [NoteController::class, 'unlock']);

    // API Chia sẻ Note
    Route::post('/notes/{id}/share', [SharedNoteController::class, 'share']);
    Route::get('/notes/shared', [SharedNoteController::class, 'sharedWithMe']);
});