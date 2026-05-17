<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class NoteController extends Controller
{
    // API Lấy danh sách Note (Có hỗ trợ tìm kiếm và lọc theo nhãn)
    public function index(Request $request)
    {
        $query = $request->user()->notes()->with(['labels', 'images']);

        // Lọc theo keyword
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
        }

        // Lọc theo label_id
        if ($request->has('label_id')) {
            $query->whereHas('labels', function($q) use ($request) {
                $q->where('labels.id', $request->label_id);
            });
        }

        $notes = $query->orderBy('updated_at', 'desc')->get();

        // Xử lý logic bảo mật: Ẩn nội dung nếu bị khóa
        $formattedNotes = $notes->map(function ($note) {
            $is_locked = !empty($note->note_password_hash);
            return [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $is_locked ? null : $note->content,
                'is_pinned' => $note->is_pinned,
                'is_locked' => $is_locked,
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
            'title' => 'required|string',
            'content' => 'nullable|string',
        ]);

        $note = $request->user()->notes()->create([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json(['status' => 'success', 'data' => ['id' => $note->id]], 201);
    }

    // API Cập nhật Note (Dùng cho cả thao tác Save và Auto-save)
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string',
            'content' => 'nullable|string',
            'is_pinned' => 'sometimes|boolean'
        ]);

        $note = $request->user()->notes()->findOrFail($id);
        $note->update($request->only(['title', 'content', 'is_pinned']));

        return response()->json(['status' => 'success', 'data' => ['id' => $note->id]]);
    }

    // API Xóa Note
    public function destroy(Request $request, $id)
    {
        $note = $request->user()->notes()->findOrFail($id);
        $note->delete();
        
        return response()->json(['status' => 'success', 'message' => 'Đã xóa note']);
    }

    // API Thiết lập / Tắt Mật khẩu Note
    public function setPassword(Request $request, $id)
    {
        $note = $request->user()->notes()->findOrFail($id);
        
        $request->validate([
            'action' => 'required|in:enable,change,disable',
        ]);

        // Nếu hành động là tắt mật khẩu
        if ($request->action === 'disable') {
            $note->update(['note_password_hash' => null]);
            return response()->json(['status' => 'success', 'message' => 'Đã tắt mật khẩu note']);
        }

        // Nếu hành động là bật hoặc đổi mật khẩu
        $request->validate([
            'new_password' => 'required|string|min:3',
            'confirm_password' => 'required|string|same:new_password'
        ]);

        $note->update(['note_password_hash' => Hash::make($request->new_password)]);
        return response()->json(['status' => 'success', 'message' => 'Đã thiết lập mật khẩu note thành công']);
    }

    // API Mở khóa Note (Lấy nội dung thật)
    public function unlock(Request $request, $id)
    {
        $note = $request->user()->notes()->with('images')->findOrFail($id);
        
        $request->validate([
            'password' => 'required|string'
        ]);

        // Kiểm tra xem note có đang khóa không và mật khẩu có khớp không
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