<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuestionsSkillConnect;
use Illuminate\Support\Facades\Validator;

class QuestionsSkillController extends Controller
{
    /**
     * Display a listing of all SkillConnect questions.
     */
    public function index(Request $request)
    {
        try {
            $onlyActive = $request->boolean('active', true);
            $questions = QuestionsSkillConnect::get();

            return response()->json([
                'success' => true,
                'message' => 'Questions fetched successfully',
                'data' => $questions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching questions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question'     => 'required|string|max:255',
            'hint_answer'  => 'nullable|string',
            'order'        => 'nullable|integer',
            'is_active'    => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $question = QuestionsSkillConnect::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Question added successfully',
                'data'    => $question,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding question',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a single question with related answers.
     */
    public function show($id)
    {
        try {
            $question = QuestionsSkillConnect::with('answers.user:id,name,email')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $question,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Optional: Update question (for admin or dashboard).
     */
    public function update(Request $request, $id)
    {
        try {
            $question = QuestionsSkillConnect::findOrFail($id);
            $question->update($request->only(['question', 'hint_answer', 'order', 'is_active']));

            return response()->json([
                'success' => true,
                'message' => 'Question updated successfully',
                'data'    => $question,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating question',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Optional: Delete a question.
     */
    public function destroy($id)
    {
        try {
            $question = QuestionsSkillConnect::findOrFail($id);
            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting question',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
