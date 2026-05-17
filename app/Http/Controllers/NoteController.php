<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class NoteController extends Controller
{
    // API Lấy danh sách Note
    public function index(Request $request)
    {
        $query = $request->user()->notes()->with(['labels', 'images']);

        // SỬA LỖI BẢO MẬT: Bọc nhóm tìm kiếm bằng Closure function
        if ($request->has('search') && $request->search !== null) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('content', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->has('label_id') && $request->label_id !== null) {
            $query->whereHas('labels', function ($q) use ($request) {
                $q->where('labels.id', $request->label_id);
            });
        }

        $notes = $query->orderBy('updated_at', 'desc')->get();

        $formattedNotes = $notes->map(function ($note) {
            $is_locked = !empty($note->note_password_hash);
            return [
                'id' => $note->id,
                'title' => $note->title ?? 'Untitled',
                'content' => $is_locked ? null : $note->content,
                'is_pinned' => (bool) $note->is_pinned,
                'is_locked' => $is_locked,
                'is_shared' => false, // Gắn cờ để Frontend hiển thị icon
                'updated_at' => $note->updated_at,
                'labels' => $note->labels,
                'images' => $is_locked ? [] : $note->images,
            ];
        });

        return response()->json(['status' => 'success', 'data' => $formattedNotes]);
    }

    // API Tạo mới Note
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'is_pinned' => 'nullable|boolean'
        ]);

        $note = $request->user()->notes()->create([
            'title' => $request->title,
            'content' => $request->input('content'),
            'is_pinned' => $request->is_pinned ?? false,
        ]);

        return response()->json(['status' => 'success', 'data' => ['id' => $note->id]], 201);
    }

    // API Cập nhật Note
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'is_pinned' => 'sometimes|boolean'
        ]);

        $note = $request->user()->notes()->findOrFail($id);

        // SỬA Ở ĐÂY: Có thể truyền mảng trực tiếp cho hàm update
        $note->update([
            'title' => $request->input('title', $note->title),
            'content' => $request->input('content', $note->content),
            'is_pinned' => $request->input('is_pinned', $note->is_pinned),
        ]);

        return response()->json(['status' => 'success', 'data' => ['id' => $note->id]]);
    }

    public function destroy(Request $request, $id)
    {
        $note = $request->user()->notes()->findOrFail($id);
        $note->delete();
        return response()->json(['status' => 'success', 'message' => 'Đã xóa note']);
    }

    public function setPassword(Request $request, $id)
    {
        $note = $request->user()->notes()->findOrFail($id);
        $request->validate(['action' => 'required|in:enable,change,disable']);

        if ($request->action === 'disable') {
            $note->update(['note_password_hash' => null]);
            return response()->json(['status' => 'success', 'message' => 'Đã tắt mật khẩu note']);
        }

        $request->validate([
            'new_password' => 'required|string|min:3',
            'confirm_password' => 'required|string|same:new_password'
        ]);

        $note->update(['note_password_hash' => Hash::make($request->new_password)]);
        return response()->json(['status' => 'success', 'message' => 'Đã thiết lập mật khẩu note']);
    }

    public function unlock(Request $request, $id)
    {
        $note = $request->user()->notes()->with('images')->findOrFail($id);
        $request->validate(['password' => 'required|string']);

        if (empty($note->note_password_hash) || !Hash::check($request->password, $note->note_password_hash)) {
            return response()->json(['status' => 'error', 'message' => 'Mật khẩu không chính xác'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'content' => $note->content,
                'images' => $note->images
            ]
        ]);
    }
}