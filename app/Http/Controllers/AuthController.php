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
    // Register API
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'display_name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed', // requires password_confirmation to be passed
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 400);
        }

        $user = User::create([
            'email' => $request->email,
            'display_name' => $request->display_name,
            'password_hash' => Hash::make($request->password), // Bcrypt hashing
            'is_active' => false, // Per requirements, default is not activated
        ]);

        // 🌟 NEW LOGIC: CREATE TOKEN AND LOG (SIMULATE SENDING ACTIVATION EMAIL)
        $token = Str::random(40);

        // Save token to Cache for 30 minutes (Linked to user's email)
        Cache::put('verify_' . $request->email, $token, now()->addMinutes(30));

        // Print activation link to laravel.log
        $activationLink = "http://localhost/verify?email={$request->email}&token={$token}";
        Log::info("========================================");
        Log::info("💌 NEW ACCOUNT ACTIVATION EMAIL");
        Log::info("Welcome {$request->display_name}! Please click the following link to activate your account: " . $activationLink);
        Log::info("========================================");

        // Return JSON in the required structure
        return response()->json([
            'status' => 'success',
            'message' => 'Please check your email to activate your account'
        ], 201);
    }

    // Login API
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check password against password_hash column
        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect email or password'
            ], 401);
        }

        // Create Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return JSON in the required structure
        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'user' => [
                    'display_name' => $user->display_name,
                    'avatar_url' => $user->avatar_url,
                    'is_active' => $user->is_active, // Return status for Frontend to toggle banner
                ]
            ]
        ], 200);
    }

    // Forgot Password API - Send OTP
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // Generate random 6-digit OTP
        $otp = rand(100000, 999999);

        // Save to Laravel's password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($otp), // Hash OTP in DB for security
                'created_at' => now()
            ]
        );

        // Send email
        Mail::to($request->email)->send(new SendOtpMail($otp));

        return response()->json(['status' => 'success', 'message' => 'An OTP has been sent to your email.']);
    }

    // Reset Password with OTP API
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $resetRecord = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$resetRecord || !Hash::check($request->otp, $resetRecord->token)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired OTP.'], 400);
        }

        // Update new password
        User::where('email', $request->email)->update([
            'password_hash' => Hash::make($request->password)
        ]);

        // Delete token after use
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['status' => 'success', 'message' => 'Password reset successful.']);
    }

    // Change Password API (For logged in users)
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json(['status' => 'error', 'message' => 'Current password is incorrect.'], 400);
        }

        $user->update([
            'password_hash' => Hash::make($request->new_password)
        ]);

        return response()->json(['status' => 'success', 'message' => 'Password changed successfully.']);
    }

    // VERIFY ACCOUNT API
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        // 1. Retrieve token from Cache to check if it matches
        $cachedToken = Cache::get('verify_' . $request->email);

        if (!$cachedToken || $cachedToken !== $request->token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired activation link (exceeded 30 minutes)!'
            ], 400);
        }

        // 2. If it matches, find User in Database
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found!'
            ], 404);
        }

        // 3. Toggle is_active = 1 (Successfully activated)
        $user->is_active = 1;
        $user->save();

        // 4. Delete the token from Cache so it cannot be reused
        Cache::forget('verify_' . $request->email);

        return response()->json([
            'status' => 'success',
            'message' => 'Account activated successfully!'
        ]);
    }
}