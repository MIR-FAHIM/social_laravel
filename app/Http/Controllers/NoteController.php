<?php

// app/Http/Controllers/NoteController.php
namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    // Create a new note
    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'note' => 'required|string',
                'user_id' => 'required|exists:users,id',
                'content_id' => 'nullable|exists:contents,id',
                'status' => 'boolean',
            ]);
    
            // Create the note
            $note = Note::create($validatedData);
    
            // Return success response
            return response()->json([
                "status" => 200,
                'message' => 'Note created successfully',
                'note' => $note
            ], 200);
            
        } catch (\Exception $e) {
            // Catch any exceptions and return a generic error response
            return response()->json([
                "status" => 500,
                'message' => 'An error occurred while creating the note.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Get all notes by user or content
    public function index(Request $request)
    {
        $notes = Note::where('user_id', $request->user_id)
                    ->orWhere('content_id', $request->content_id)
                    ->get();

        return response()->json(
            [
"status" => 200,
                "data"=>$notes
            ]);
    }

    // Get a specific note
    public function show($id)
    {
        $note = Note::findOrFail($id);
        return response()->json($note);
    }

    // Update a note
    public function update(Request $request, $id)
    {
        $note = Note::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'string|max:255',
            'note' => 'string',
            'status' => 'boolean',
        ]);

        $note->update($validatedData);

        return response()->json([
            'message' => 'Note updated successfully',
            'note' => $note
        ]);
    }

    // Delete a note
    public function destroy($id)
    {
        $note = Note::findOrFail($id);
        $note->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Note deleted successfully'
        ]);
    }
}

