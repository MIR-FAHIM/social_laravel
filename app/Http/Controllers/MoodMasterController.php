<?php

namespace App\Http\Controllers;

use App\Models\MoodMaster;
use Illuminate\Http\Request;

class MoodMasterController extends Controller
{
    /**
     * GET /moods
     * Fetch all moods (active + inactive)
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => MoodMaster::orderBy('id', 'desc')->get()
        ]);
    }

    /**
     * GET /moods/active
     * Fetch only active moods
     */
    public function active()
    {
        return response()->json([
            'success' => true,
            'data' => MoodMaster::active()->orderBy('id', 'desc')->get()
        ]);
    }

    /**
     * POST /moods
     * Create a new mood
     */
    public function store(Request $request)
    {
        $request->validate([
            'mood_name'  => 'required|string|max:255',
            'mood_icon'  => 'nullable|string|max:255',
            'mood_color' => 'nullable|string|max:30',
            'description'=> 'nullable|string',
            'is_active'  => 'boolean'
        ]);

        $mood = MoodMaster::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Mood created successfully.',
            'data'    => $mood
        ], 201);
    }

    /**
     * GET /moods/{id}
     * Fetch single mood details
     */
    public function show($id)
    {
        $mood = MoodMaster::find($id);

        if (!$mood) {
            return response()->json([
                'success' => false,
                'message' => 'Mood not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $mood
        ]);
    }

    /**
     * PUT/PATCH /moods/{id}
     * Update mood information
     */
    public function update(Request $request, $id)
    {
        $mood = MoodMaster::find($id);

        if (!$mood) {
            return response()->json([
                'success' => false,
                'message' => 'Mood not found.'
            ], 404);
        }

        $request->validate([
            'mood_name'  => 'sometimes|required|string|max:255',
            'mood_icon'  => 'nullable|string|max:255',
            'mood_color' => 'nullable|string|max:30',
            'description'=> 'nullable|string',
            'is_active'  => 'boolean'
        ]);

        $mood->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Mood updated successfully.',
            'data'    => $mood
        ]);
    }

    /**
     * DELETE /moods/{id}
     * Delete mood
     */
    public function destroy($id)
    {
        $mood = MoodMaster::find($id);

        if (!$mood) {
            return response()->json([
                'success' => false,
                'message' => 'Mood not found.'
            ], 404);
        }

        $mood->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mood deleted successfully.'
        ]);
    }
}
