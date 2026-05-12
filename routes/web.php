<?php

use Illuminate\Support\Facades\Route;

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

// Trang yêu cầu gửi link reset mật khẩu (Email)
Route::get('/password/reset', function () {
    // Trỏ đúng vào thư mục auth/passwords/email.blade.php
    return view('auth.passwords.email');
});

// Trang nhập mật khẩu mới (Sau khi có OTP/Link)
Route::get('/password/update', function () {
    return view('auth.passwords.reset');
});