<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // API Đăng ký
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'display_name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed', // yêu cầu truyền lên password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $user = User::create([
            'email' => $request->email,
            'display_name' => $request->display_name,
            'password_hash' => Hash::make($request->password), // Mã hóa bcrypt
            'is_active' => false, // Theo yêu cầu, mặc định chưa kích hoạt
        ]);

        // 🌟 LOGIC MỚI: TẠO TOKEN VÀ GHI LOG (GIẢ LẬP GỬI EMAIL KÍCH HOẠT)
        $token = Str::random(40);

        // Lưu token vào Cache 30 phút (Gắn với email của user)
        Cache::put('verify_' . $request->email, $token, now()->addMinutes(30));

        // In link kích hoạt ra file laravel.log
        $activationLink = "http://localhost/verify?email={$request->email}&token={$token}";
        Log::info("========================================");
        Log::info("💌 BỨC THƯ KÍCH HOẠT TÀI KHOẢN MỚI");
        Log::info("Chào mừng {$request->display_name}! Vui lòng nhấp vào link sau để kích hoạt: " . $activationLink);
        Log::info("========================================");

        // Trả về JSON đúng cấu trúc yêu cầu
        return response()->json([
            'status' => 'success',
            'message' => 'Vui lòng kiểm tra email để kích hoạt tài khoản'
        ], 201);
    }

    // API Đăng nhập
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Tìm user theo email
        $user = User::where('email', $request->email)->first();

        // Kiểm tra mật khẩu dựa trên cột password_hash
        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email hoặc mật khẩu không chính xác'
            ], 401);
        }

        // Tạo Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Trả về JSON đúng cấu trúc yêu cầu
        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'user' => [
                    'display_name' => $user->display_name,
                    'avatar_url' => $user->avatar_url,
                    'is_active' => $user->is_active, // Trả thêm trạng thái để Frontend biết mà bật/tắt banner
                ]
            ]
        ], 200);
    }

    // API Quên mật khẩu - Gửi mã OTP
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // Tạo mã OTP ngẫu nhiên 6 số
        $otp = rand(100000, 999999);

        // Lưu vào bảng password_reset_tokens của Laravel
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($otp), // Mã hóa OTP trong DB cho bảo mật
                'created_at' => now()
            ]
        );

        // Gửi email
        Mail::to($request->email)->send(new SendOtpMail($otp));

        return response()->json(['status' => 'success', 'message' => 'Mã OTP đã được gửi đến email của bạn.']);
    }

    // API Đặt lại mật khẩu bằng OTP
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $resetRecord = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$resetRecord || !Hash::check($request->otp, $resetRecord->token)) {
            return response()->json(['status' => 'error', 'message' => 'OTP không hợp lệ hoặc đã hết hạn.'], 400);
        }

        // Cập nhật mật khẩu mới
        User::where('email', $request->email)->update([
            'password_hash' => Hash::make($request->password)
        ]);

        // Xóa token sau khi dùng xong
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['status' => 'success', 'message' => 'Đặt lại mật khẩu thành công.']);
    }

    // API Đổi mật khẩu (Dành cho user đã đăng nhập)
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json(['status' => 'error', 'message' => 'Mật khẩu hiện tại không đúng.'], 400);
        }

        $user->update([
            'password_hash' => Hash::make($request->new_password)
        ]);

        return response()->json(['status' => 'success', 'message' => 'Đổi mật khẩu thành công.']);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        // 1. Móc cái token trong Cache ra kiểm tra xem có khớp không
        $cachedToken = Cache::get('verify_' . $request->email);

        if (!$cachedToken || $cachedToken !== $request->token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Link kích hoạt không hợp lệ hoặc đã hết hạn (quá 30 phút)!'
            ], 400);
        }

        // 2. Nếu khớp, tìm User trong Database
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người dùng!'
            ], 404);
        }

        // 3. Gạt công tắc is_active = 1 (Kích hoạt thành công)
        $user->is_active = 1;
        $user->save();

        // 4. Xóa cái token trong Cache đi để không xài lại được nữa
        Cache::forget('verify_' . $request->email);

        return response()->json([
            'status' => 'success',
            'message' => 'Tài khoản đã được kích hoạt thành công!'
        ]);
    }
}