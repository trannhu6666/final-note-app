<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    /**
     * Get current User details API to render on the Frontend
     */
    public function show(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()
        ]);
    }

    /**
     * Update Profile Information API (Matches 100% with CK.docx file) 🌟
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'display_name' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // 🌟 ASSIGN TO THE CORRECT COLUMNS: display_name and avatar_url
        $user->display_name = $request->display_name;

        if ($request->hasFile('avatar')) {
            // Delete old image based on the avatar_url column name
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
            'message' => 'Account information updated successfully!',
            'data' => $user
        ]);
    }

    /**
     * Change account password API (Matches 100% with password_hash column) 🌟
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        // Note: If your User Model uses another variable to map Auth, make sure to call the correct password_hash field
        $currentHash = $user->password_hash ?? $user->password;

        if (!Hash::check($request->current_password, $currentHash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect!'
            ], 400);
        }

        // Update the password_hash column according to the DB design
        if (Schema::hasColumn('users', 'password_hash')) {
            $user->password_hash = Hash::make($request->new_password);
        } else {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully!'
        ]);
    }
}