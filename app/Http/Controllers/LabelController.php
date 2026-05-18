<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    // Lấy danh sách Label (Get all labels)
    public function index(Request $request)
    {
        $labels = $request->user()->labels()->withCount('notes')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $labels
        ]);
    }

    // Tạo Label mới (Create new label)
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $label = $request->user()->labels()->create(['name' => $request->name]);
        return response()->json(['status' => 'success', 'data' => $label], 201);
    }

    // Cập nhật tên Label (Update label name)
    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $label = $request->user()->labels()->findOrFail($id);
        $label->update(['name' => $request->name]);
        return response()->json(['status' => 'success', 'data' => $label]);
    }

    // Xóa Label (Delete label)
    public function destroy(Request $request, $id)
    {
        $label = $request->user()->labels()->findOrFail($id);
        $label->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Label deleted successfully.'
        ]);
    }
}