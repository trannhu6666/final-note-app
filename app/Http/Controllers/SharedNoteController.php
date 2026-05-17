<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\SharedNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SharedNoteController extends Controller
{
    // API Chia sẻ Note
    public function share(Request $request, $id)
    {
        // Phải là chủ sở hữu mới được quyền chia sẻ
        $note = $request->user()->notes()->findOrFail($id);
        
        $request->validate([
            'recipient_email' => 'required|email|exists:users,email',
            'permission' => 'required|in:read,edit'
        ]);

        if ($request->recipient_email === $request->user()->email) {
            return response()->json(['status' => 'error', 'message' => 'Không thể chia sẻ cho chính mình'], 400);
        }

        // Dùng updateOrCreate để nếu chia sẻ lại cho cùng 1 người thì chỉ cập nhật quyền
        SharedNote::updateOrCreate(
            ['note_id' => $note->id, 'recipient_email' => $request->recipient_email],
            ['permission' => $request->permission, 'shared_at' => now()]
        );

        return response()->json(['status' => 'success', 'message' => 'Đã chia sẻ ghi chú']);
    }

    // API Lấy danh sách Note được chia sẻ (Shared with me)
    public function sharedWithMe(Request $request)
    {
        $userEmail = $request->user()->email;
        
        // Dùng Query Builder để join 3 bảng: shared_notes, notes và users (để lấy email chủ sở hữu)
        $sharedNotes = DB::table('shared_notes')
            ->join('notes', 'shared_notes.note_id', '=', 'notes.id')
            ->join('users', 'notes.user_id', '=', 'users.id')
            ->where('shared_notes.recipient_email', $userEmail)
            ->select(
                'notes.id',
                'notes.title',
                'users.email as owner_email',
                'shared_notes.permission',
                'shared_notes.shared_at'
            )
            ->orderBy('shared_notes.shared_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $sharedNotes]);
    }
}