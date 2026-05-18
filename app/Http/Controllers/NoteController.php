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
     * API 1: Get Notes list - Return the correct interface module 🌟
     */
    public function index(Request $request)
    {
        // 1. Get the search keyword sent from the Frontend Search bar
        $searchKeyword = $request->query('search');

        if ($request->query('type') === 'shared' || $request->has('shared')) {
            // FLOW 1: SHARED PAGE
            $sharedRecords = DB::table('shared_notes')
                ->where('recipient_email', $request->user()->email)
                ->get();

            $sharedNoteIds = $sharedRecords->pluck('note_id')->toArray();

            $query = Note::whereIn('id', $sharedNoteIds)
                ->where('user_id', '!=', $request->user()->id)
                ->with(['labels', 'images']);

            // 🌟 SEARCH LOGIC: Filter by Title or Content
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
            // FLOW 2: HOMEPAGE (All Notes)
            $query = Note::where('user_id', $request->user()->id)
                ->with(['labels', 'images']);

            // 🌟 SEARCH LOGIC: Filter by Title or Content
            if (!empty($searchKeyword)) {
                $query->where(function ($q) use ($searchKeyword) {
                    $q->where('title', 'like', "%{$searchKeyword}%")
                        ->orWhere('content', 'like', "%{$searchKeyword}%");
                });
            }

            // 🌟 LABEL FILTER LOGIC (If user clicks on the Label menu on the left)
            if ($request->has('label_id') && $request->label_id !== '') {
                $query->whereHas('labels', function ($q) use ($request) {
                    $q->where('labels.id', $request->label_id);
                });
            }

            $notes = $query->latest()->get();
        }

        // Handle attaching security lock status
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
     * API 2: Create a new Note with Image array upload handling
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
     * API 3: Update Note - Allow both note owner and shared users with 'edit' permission to modify
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
                'message' => 'You do not have permission to edit this note!'
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
     * API 4: Lock Note with a password (Only the Note owner can change/set the password)
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
            'message' => 'Note security password updated successfully!'
        ]);
    }

    /**
     * API 5: Verify password to unlock and view Note content
     */
    public function unlock(Request $request, $id)
    {
        $note = Note::findOrFail($id); // Find the note regardless of who the owner is

        // Check if the current User has permission to access this note (Is Owner OR is shared)
        $isOwner = $note->user_id === $request->user()->id;
        $isShared = DB::table('shared_notes')
            ->where('note_id', $id)
            ->where('recipient_email', $request->user()->email)
            ->exists();

        if (!$isOwner && !$isShared) {
            return response()->json(['status' => 'error', 'message' => 'You do not have permission to access this note!'], 403);
        }

        $request->validate([
            'password' => 'required|string'
        ]);

        if (!Hash::check($request->password, $note->note_password_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect password!'
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
     * API 6: Delete note
     */
    public function destroy(Request $request, $id)
    {
        $note = Note::with('images')->findOrFail($id);

        // Case 1: If OWNER -> Delete permanently
        if ($note->user_id === $request->user()->id) {
            if ($note->images && $note->images->count() > 0) {
                foreach ($note->images as $image) {
                    $filePath = str_replace('/storage/', '', $image->image_url);
                    Storage::disk('public')->delete($filePath);
                }
            }
            $note->delete();
            return response()->json(['status' => 'success', 'message' => 'Note deleted successfully!']);
        }

        // Case 2: If SHARED USER -> Only remove their view access from DB
        $sharedRecord = DB::table('shared_notes')
            ->where('note_id', $id)
            ->where('recipient_email', $request->user()->email);

        if ($sharedRecord->exists()) {
            $sharedRecord->delete();
            return response()->json(['status' => 'success', 'message' => 'Note removed from shared list!']);
        }

        return response()->json(['status' => 'error', 'message' => 'You do not have permission to delete this note!'], 403);
    }

    /**
     * API 7: Share Note (Create new or Update permissions)
     */
    public function share(Request $request, $id)
    {
        $note = $request->user()->notes()->findOrFail($id);

        $request->validate([
            'recipient_email' => 'required|email',
            'permission' => 'required|in:view,edit'
        ]);

        // Prevent users from sharing with themselves
        if (strtolower($request->recipient_email) === strtolower($request->user()->email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are the owner of this Note, you cannot share it with yourself!'
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

        return response()->json(['status' => 'success', 'message' => 'Shared successfully!']);
    }

    /**
     * API 8: Get a list of users this Note is shared with
     */
    public function getSharedUsers(Request $request, $id)
    {
        $note = $request->user()->notes()->findOrFail($id);

        $shares = DB::table('shared_notes')
            ->where('note_id', $id)
            ->where('recipient_email', '!=', $request->user()->email) // 🌟 Force removal of the Note Owner's email
            ->get();

        return response()->json(['status' => 'success', 'data' => $shares]);
    }

    /**
     * API 9: Revoke share access
     */
    public function revokeShare(Request $request, $id)
    {
        $note = $request->user()->notes()->findOrFail($id);
        DB::table('shared_notes')
            ->where('note_id', $id)
            ->where('recipient_email', $request->recipient_email)
            ->delete();

        return response()->json(['status' => 'success', 'message' => 'Access revoked successfully!']);
    }

    /**
     * API 12: Delete a specific image from a Note (Matches note_images table) 🌟
     */
    public function deleteImage(Request $request, $id)
    {
        // Find the image in the note_images pivot table according to the CK.docx design file
        $image = DB::table('note_images')->where('id', $id)->first();

        if (!$image) {
            return response()->json(['status' => 'error', 'message' => 'Image does not exist!'], 404);
        }

        // Security: Check if the current user has permission to edit this note
        $note = Note::findOrFail($image->note_id);
        $sharedRecord = DB::table('shared_notes')
            ->where('note_id', $note->id)
            ->where('recipient_email', $request->user()->email)
            ->first();

        if ($note->user_id !== $request->user()->id && (!$sharedRecord || $sharedRecord->permission !== 'edit')) {
            return response()->json(['status' => 'error', 'message' => 'You do not have permission to edit this note!'], 403);
        }

        // 1. Delete physical file from Storage to avoid server clutter
        $filePath = str_replace('/storage/', '', $image->image_url);
        Storage::disk('public')->delete($filePath);

        // 2. Delete data row in note_images table
        DB::table('note_images')->where('id', $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Image deleted successfully!'
        ]);
    }
}