<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// Trang giới thiệu ban đầu
Route::get('/', function () {
    return view('welcome');
});

// Trang chính (Dashboard)
Route::get('/notes', function () {
    return view('notes.index');
});

// Trang ghi chú được chia sẻ
Route::get('/notes/shared', function () {
    return view('notes.shared');
});

// Trang cá nhân
Route::get('/profile/edit', function () {
    return view('profile.edit');
});

// --- AUTH ROUTES ---
Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/logout', function () {
    return redirect('/');
});

// Trang yêu cầu gửi link reset mật khẩu (Giao diện nhập Email)
Route::get('/password/forgot', function () {
    return view('auth.passwords.forgot');
});

// Trang nhập mật khẩu mới (Sau khi có OTP)
Route::get('/password/update', function () {
    return view('auth.passwords.reset');
});
Route::get('/verify', [AuthController::class, 'verify']);