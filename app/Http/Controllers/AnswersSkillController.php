<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnswerSkillConnect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AnswersSkillController extends Controller
{
    /**
     * Store (add) a new answer
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id'  => 'required|exists:questions_skill_connects,id',
            'answer'       => 'required|string',
            'is_bullet'    => 'nullable|boolean',
            'type'         => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $answer = AnswerSkillConnect::create([
                'question_id'     => $request->question_id,
                'user_id'         => $request->user_id, // fallback if auth not used
                'answer'          => $request->answer,
                'is_bullet'       => $request->boolean('is_bullet', false),
                'type'            => $request->type ?? 'text',
                'is_active'       => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Answer added successfully',
                'data'    => $answer,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding answer',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing answer
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'answer'     => 'nullable|string',
            'is_bullet'  => 'nullable|boolean',
            'type'       => 'nullable|string|max:100',
            'is_active'  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $answer = AnswerSkillConnect::findOrFail($id);

            // Optional: restrict update to owner
            if (Auth::check() && $answer->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this answer',
                ], 403);
            }

            $answer->update($request->only(['answer', 'is_bullet', 'type', 'is_active']));

            return response()->json([
                'success' => true,
                'message' => 'Answer updated successfully',
                'data'    => $answer,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating answer',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an answer
     */
    public function destroy($id)
    {
        try {
            $answer = AnswerSkillConnect::findOrFail($id);

            // Optional: restrict deletion to owner
            if (Auth::check() && $answer->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this answer',
                ], 403);
            }

            $answer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Answer deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting answer',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
