<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Account 1 — Note Owner (Để is_active = false để test cái banner unverified của bạn)
        User::create([
            'email' => 'user1@tdtu.edu.vn',
            'display_name' => 'Note Owner',
            'password_hash' => Hash::make('password123'), // Mã hóa bcrypt bắt buộc theo đề bài
            'is_active' => false, // false để Frontend hiện thông báo màu vàng cảnh báo chưa active
            'theme_pref' => 'light',
            'font_size_pref' => 'medium'
        ]);

        // 2. Account 2 — Collaborator (Để is_active = true để làm nick phụ vào sửa bài tập realtime)
        User::create([
            'email' => 'user2@tdtu.edu.vn',
            'display_name' => 'Collaborator',
            'password_hash' => Hash::make('password123'), // Mã hóa bcrypt
            'is_active' => true, // Tài khoản này đã kích hoạt
            'theme_pref' => 'light',
            'font_size_pref' => 'medium'
        ]);
    }
}