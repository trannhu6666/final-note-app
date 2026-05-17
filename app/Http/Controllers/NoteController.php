<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
    /**
     * API 1: Lấy danh sách Note - Trả về đúng phân hệ giao diện 🌟
     */
    public function index(Request $request)
    {
        // 1. Lấy từ khóa tìm kiếm từ thanh Search của Frontend gửi lên
        $searchKeyword = $request->query('search');

        if ($request->query('type') === 'shared' || $request->has('shared')) {
            // LUỒNG 1: TRANG SHARED
            $sharedRecords = DB::table('shared_notes')
                ->where('recipient_email', $request->user()->email)
                ->get();

            $sharedNoteIds = $sharedRecords->pluck('note_id')->toArray();

            $query = Note::whereIn('id', $sharedNoteIds)
                ->where('user_id', '!=', $request->user()->id)
                ->with(['labels', 'images']);

            // 🌟 LOGIC TÌM KIẾM: Lọc theo Tiêu đề hoặc Nội dung
            if (!empty($searchKeyword)) {
                $query->where(function ($q) use ($searchKeyword) {
                    $q->where('title', 'like', "%{$searchKeyword}%")
                        ->orWhere('content', 'like', "%{$searchKeyword}%");
                });
            }

            $notes = $query->latest()->get();

            $notes->each(function ($note) use ($sharedRecords) {
                $record = $sharedRecords->firstWhere('note_id', $note->id);
                $note->permission = $record->permission;
                $note->shared_at = $record->shared_at;

                $owner = \App\Models\User::find($note->user_id);
                $note->owner_email = $owner ? $owner->email : 'Unknown User';
            });

        } else {
            // LUỒNG 2: TRANG CHỦ (All Notes)
            $query = Note::where('user_id', $request->user()->id)
                ->with(['labels', 'images']);

            // 🌟 LOGIC TÌM KIẾM: Lọc theo Tiêu đề hoặc Nội dung
            if (!empty($searchKeyword)) {
                $query->where(function ($q) use ($searchKeyword) {
                    $q->where('title', 'like', "%{$searchKeyword}%")
                        ->orWhere('content', 'like', "%{$searchKeyword}%");
                });
            }

            // 🌟 LOGIC LỌC NHÃN (Nếu user bấm vào menu Label bên trái)
            if ($request->has('label_id') && $request->label_id !== '') {
                $query->whereHas('labels', function ($q) use ($request) {
                    $q->where('labels.id', $request->label_id);
                });
            }

            $notes = $query->latest()->get();
        }

        // Xử lý gắn trạng thái khóa bảo mật
        $notes->each(function ($note) {
            $note->is_locked = !empty($note->note_password_hash);
            if ($note->is_locked) {
                $note->content = '🔒 Content is password protected...';
            }
        });

        return response()->json([
            'status' => 'success',
            'data' => $notes
        ]);
    }

    /**
     * API 2: Tạo mới Note kèm xử lý Upload mảng hình ảnh
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'is_pinned' => 'nullable|boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $note = $request->user()->notes()->create([
            'title' => $request->title,
            'content' => $request->input('content'),
            'is_pinned' => $request->is_pinned ?? false,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('notes', 'public');
                $note->images()->create([
                    'image_url' => '/storage/' . $path
                ]);
            }
        }
        if ($request->has('sync_labels')) {
            $note->labels()->sync($request->input('labels', []));
        }

        return response()->json([
            'status' => 'success',
            'data' => $note->load(['labels', 'images'])
        ], 201);
    }

    /**
     * API 3: Cập nhật Note - Cho phép cả chủ note và người được chia sẻ quyền 'edit' chỉnh sửa
     */
    public function update(Request $request, $id)
    {
        $note = Note::findOrFail($id);

        $sharedRecord = DB::table('shared_notes')
            ->where('note_id', $id)
            ->where('recipient_email', $request->user()->email)
            ->first();

        if ($note->user_id !== $request->user()->id && (!$sharedRecord || $sharedRecord->permission !== 'edit')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn không có quyền chỉnh sửa ghi chú này!'
            ], 403);
        }
        if ($request->has('sync_labels')) {
            $note->labels()->sync($request->input('labels', []));
        }

        $request->validate([
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'is_pinned' => 'nullable|boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $note->update([
            'title' => $request->input('title', $note->title),
            'content' => $request->input('content', $note->content),
            'is_pinned' => $request->input('is_pinned', $note->is_pinned),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('notes', 'public');
                $note->images()->create([
                    'image_url' => '/storage/' . $path
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $note->load(['labels', 'images'])
        ]);
    }

    /**
     * API 4: Khóa Note bằng mật khẩu (Chỉ chủ Note mới được đổi/đặt pass)
     */
    public function setPassword(Request $request, $id)
    {
        $note = $request->user()->notes()->findOrFail($id);

        $request->validate([
            'new_password' => 'required|string|min:4',
            'confirm_password' => 'required|string|same:new_password',
            'action' => 'required|string'
        ]);

        if ($request->action === 'enable') {
            $note->update([
                'note_password_hash' => Hash::make($request->new_password)
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật mật khẩu bảo mật Note thành công!'
        ]);
    }

    /**
     * API 5: Xác thực mật khẩu để mở khóa xem nội dung Note
     */
    public function unlock(Request $request, $id)
    {
        $note = Note::findOrFail($id); // Tìm note bất kể ai là chủ

        // Kiểm tra xem User hiện tại có quyền đụng vào note này không (Là chủ HOẶC được share)
        $isOwner = $note->user_id === $request->user()->id;
        $isShared = DB::table('shared_notes')
            ->where('note_id', $id)
            ->where('recipient_email', $request->user()->email)
            ->exists();

        if (!$isOwner && !$isShared) {
            return response()->json(['status' => 'error', 'message' => 'Bạn không có quyền truy cập ghi chú này!'], 403);
        }

        $request->validate([
            'password' => 'required|string'
        ]);

        if (!Hash::check($request->password, $note->note_password_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mật khẩu không chính xác!'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'content' => $note->content
            ]
        ]);
    }

    /**
     * API 6: Xóa ghi chú
     */
    public function destroy(Request $request, $id)
    {
        $note = Note::with('images')->findOrFail($id);

        // Trường hợp 1: Nếu là CHỦ SỞ HỮU -> Xóa vĩnh viễn
        if ($note->user_id === $request->user()->id) {
            if ($note->images && $note->images->count() > 0) {
                foreach ($note->images as $image) {
                    $filePath = str_replace('/storage/', '', $image->image_url);
                    Storage::disk('public')->delete($filePath);
                }
            }
            $note->delete();
            return response()->json(['status' => 'success', 'message' => 'Xóa ghi chú thành công!']);
        }

        // Trường hợp 2: Nếu là NGƯỜI ĐƯỢC CHIA SẺ -> Chỉ xóa quyền xem của mình khỏi DB
        $sharedRecord = DB::table('shared_notes')
            ->where('note_id', $id)
            ->where('recipient_email', $request->user()->email);

        if ($sharedRecord->exists()) {
            $sharedRecord->delete();
            return response()->json(['status' => 'success', 'message' => 'Đã gỡ ghi chú khỏi danh sách chia sẻ!']);
        }

        return response()->json(['status' => 'error', 'message' => 'Bạn không có quyền xóa ghi chú này!'], 403);
    }

    /**
     * API 7: Chia sẻ Note (Tạo mới hoặc Cập nhật quyền)
     */
    public function share(Request $request, $id)
    {
        $note = $request->user()->notes()->findOrFail($id);

        $request->validate([
            'recipient_email' => 'required|email',
            'permission' => 'required|in:view,edit'
        ]);

        // Chặn người dùng tự share cho chính mình
        if (strtolower($request->recipient_email) === strtolower($request->user()->email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn đang là chủ Note này, không thể tự chia sẻ cho chính mình!'
            ]);
        }

        DB::table('shared_notes')->updateOrInsert(
            ['note_id' => $note->id, 'recipient_email' => $request->recipient_email],
            [
                'permission' => $request->permission,
                'shared_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Chia sẻ thành công!']);
    }

    /**
     * API 8: Lấy danh sách những người đang được chia sẻ Note này
     */
    public function getSharedUsers(Request $request, $id)
    {
        $note = $request->user()->notes()->findOrFail($id);

        $shares = DB::table('shared_notes')
            ->where('note_id', $id)
            ->where('recipient_email', '!=', $request->user()->email) // 🌟 Ép loại bỏ email Chủ Note
            ->get();

        return response()->json(['status' => 'success', 'data' => $shares]);
    }

    /**
     * API 9: Thu hồi quyền chia sẻ (Revoke)
     */
    public function revokeShare(Request $request, $id)
    {
        $note = $request->user()->notes()->findOrFail($id);
        DB::table('shared_notes')
            ->where('note_id', $id)
            ->where('recipient_email', $request->recipient_email)
            ->delete();

        return response()->json(['status' => 'success', 'message' => 'Đã thu hồi quyền truy cập!']);
    }
    /**
     * API 12: Xóa một hình ảnh cụ thể ra khỏi Note (Khớp bảng note_images) 🌟
     */
    public function deleteImage(Request $request, $id)
    {
        // Tìm hình ảnh trong bảng trung gian note_images theo đúng file thiết kế CK.docx
        $image = DB::table('note_images')->where('id', $id)->first();

        if (!$image) {
            return response()->json(['status' => 'error', 'message' => 'Hình ảnh không tồn tại!'], 404);
        }

        // Bảo mật: Kiểm tra xem user hiện tại có quyền sửa note này không
        $note = Note::findOrFail($image->note_id);
        $sharedRecord = DB::table('shared_notes')
            ->where('note_id', $note->id)
            ->where('recipient_email', $request->user()->email)
            ->first();

        if ($note->user_id !== $request->user()->id && (!$sharedRecord || $sharedRecord->permission !== 'edit')) {
            return response()->json(['status' => 'error', 'message' => 'Bạn không có quyền chỉnh sửa ghi chú này!'], 403);
        }

        // 1. Xóa file vật lý khỏi ổ đĩa Storage để tránh rác server
        $filePath = str_replace('/storage/', '', $image->image_url);
        Storage::disk('public')->delete($filePath);

        // 2. Xóa dòng dữ liệu trong bảng note_images
        DB::table('note_images')->where('id', $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa hình ảnh thành công!'
        ]);
    }
}