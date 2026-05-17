<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\SharedNoteController;
use App\Http\Controllers\UserController;
// ==========================================
// ROUTES KHÔNG CẦN ĐĂNG NHẬP (GUEST)
// ==========================================
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);


// ==========================================
// ROUTES BẮT BUỘC ĐĂNG NHẬP (CÓ TOKEN)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // 1. Nhóm Cài đặt Tài khoản (ĐÃ BỔ SUNG ĐỦ CHO DEV A)
    Route::post('/user/profile', [AuthController::class, 'updateProfile']); // Upload Avatar & Đổi tên (Tiêu chí 6)
    Route::put('/user/password', [AuthController::class, 'changePassword']); // SỬA CHUẨN: Đổi mật khẩu (Tiêu chí 7)
    Route::put('/users/preferences', [AuthController::class, 'updatePreferences']); // THÊM MỚI: Lưu Theme & Font (Tiêu chí 8)

    // 2. Nhóm Note Chia sẻ (ĐẢO LÊN TRÊN ĐỂ TRÁNH LỖI)
    Route::get('/notes/shared', [SharedNoteController::class, 'sharedWithMe']);
    Route::post('/notes/{id}/share', [SharedNoteController::class, 'share']);

    // 3. Nhóm Note Cơ bản (CRUD)
    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::put('/notes/{id}', [NoteController::class, 'update']);
    Route::delete('/notes/{id}', [NoteController::class, 'destroy']);

    // 4. API Nâng cao cho Note (Mật khẩu bảo mật)
    Route::post('/notes/{id}/password', [NoteController::class, 'setPassword']);
    Route::post('/notes/{id}/unlock', [NoteController::class, 'unlock']);

    // 5. Nhóm Label CRUD
    Route::get('/labels', [LabelController::class, 'index']);
    Route::post('/labels', [LabelController::class, 'store']);
    Route::put('/labels/{id}', [LabelController::class, 'update']);
    Route::delete('/labels/{id}', [LabelController::class, 'destroy']);

    Route::post('/notes/{id}/share', [NoteController::class, 'share']);
    Route::get('/notes/{id}/shared-users', [NoteController::class, 'getSharedUsers']);
    Route::post('/notes/{id}/share/revoke', [NoteController::class, 'revokeShare']);

    Route::get('/user/profile', [UserController::class, 'show']);
    Route::post('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'changePassword']);

    Route::delete('/notes/images/{id}', [NoteController::class, 'deleteImage']);
    // Test Token
    Route::get('/test-auth', function (Request $request) {
        return $request->user();
    });
});