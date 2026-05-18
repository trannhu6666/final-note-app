<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\SharedNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SharedNoteController extends Controller
{
    // Share Note API
    public function share(Request $request, $id)
    {
        // Must be the owner to have permission to share
        $note = $request->user()->notes()->findOrFail($id);

        $request->validate([
            'recipient_email' => 'required|email|exists:users,email',
            'permission' => 'required|in:view,edit' // Updated 'read' to 'view' to match your frontend dropdown options perfectly
        ]);

        if ($request->recipient_email === $request->user()->email) {
            return response()->json(['status' => 'error', 'message' => 'You cannot share a note with yourself!'], 400);
        }

        // Use updateOrCreate so if reshared with the same person, it only updates the permissions
        SharedNote::updateOrCreate(
            ['note_id' => $note->id, 'recipient_email' => $request->recipient_email],
            ['permission' => $request->permission, 'shared_at' => now()]
        );

        return response()->json(['status' => 'success', 'message' => 'Note shared successfully!']);
    }

    // Get list of shared notes (Shared with me) API
    public function sharedWithMe(Request $request)
    {
        $userEmail = $request->user()->email;

        // Use Query Builder to join 3 tables: shared_notes, notes, and users (to get owner email)
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