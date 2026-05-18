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
Route::post('/auth/forgot', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset', [AuthController::class, 'resetPassword']);
// DÒNG NÀY CHO API KÍCH HOẠT:
Route::post('/auth/verify', [AuthController::class, 'verify']);
// ==========================================
// ROUTES BẮT BUỘC ĐĂNG NHẬP (CÓ TOKEN)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // 1. Nhóm Cài đặt Tài khoản (Đã gom hết về UserController cho chuẩn logic)
    Route::get('/user/profile', [UserController::class, 'show']); // Load dữ liệu profile
    Route::post('/user/profile', [UserController::class, 'updateProfile']); // Upload Avatar & Đổi tên
    Route::put('/user/password', [UserController::class, 'changePassword']); // Đổi mật khẩu
    Route::put('/users/preferences', [AuthController::class, 'updatePreferences']); // Lưu Theme & Font

    // 2. Nhóm Note Chia sẻ & Phân quyền
    Route::get('/notes/shared', [SharedNoteController::class, 'sharedWithMe']);
    Route::post('/notes/{id}/share', [NoteController::class, 'share']); // Gộp về 1 mối NoteController
    Route::get('/notes/{id}/shared-users', [NoteController::class, 'getSharedUsers']);
    Route::post('/notes/{id}/share/revoke', [NoteController::class, 'revokeShare']);

    // 3. Nhóm Note Cơ bản (CRUD) & Hình ảnh
    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::put('/notes/{id}', [NoteController::class, 'update']);
    Route::delete('/notes/{id}', [NoteController::class, 'destroy']);
    Route::delete('/notes/images/{id}', [NoteController::class, 'deleteImage']);

    // 4. API Nâng cao cho Note (Mật khẩu bảo mật)
    Route::post('/notes/{id}/password', [NoteController::class, 'setPassword']);
    Route::post('/notes/{id}/unlock', [NoteController::class, 'unlock']);

    // 5. Nhóm Label CRUD
    Route::get('/labels', [LabelController::class, 'index']);
    Route::post('/labels', [LabelController::class, 'store']);
    Route::put('/labels/{id}', [LabelController::class, 'update']);
    Route::delete('/labels/{id}', [LabelController::class, 'destroy']);

    // 6. Test Token
    Route::get('/test-auth', function (Request $request) {
        return $request->user();
    });
});