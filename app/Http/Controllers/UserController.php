<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * API Lấy thông tin chi tiết của User hiện tại để đổ ra Frontend
     */
    public function show(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ]);
    }

    /**
     * API Cập nhật Thông tin Profile (Khớp 100% với file CK.docx) 🌟
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'display_name' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // 🌟 SỬA THEO ĐÚNG ĐỒ ÁN: Gán vào cột display_name và avatar_url
        $user->display_name = $request->display_name;

        if ($request->hasFile('avatar')) {
            // Xóa ảnh cũ dựa theo tên cột avatar_url
            if ($user->avatar_url) {
                $oldPath = str_replace('/storage/', '', $user->avatar_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_url = '/storage/' . $path;
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật thông tin tài khoản thành công!',
            'data' => $user
        ]);
    }

    /**
     * API Đổi mật khẩu tài khoản (Khớp 100% với cột password_hash) 🌟
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        // Lưu ý: Nếu Model User của bạn dùng biến khác để map Auth, hãy đảm bảo gọi đúng trường password_hash
        $currentHash = $user->password_hash ?? $user->password;

        if (!Hash::check($request->current_password, $currentHash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mật khẩu hiện tại không chính xác!'
            ], 400);
        }

        // Cập nhật lại vào cột password_hash theo thiết kế DB
        if (Schema::hasColumn('users', 'password_hash')) {
            $user->password_hash = Hash::make($request->new_password);
        } else {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    }
}