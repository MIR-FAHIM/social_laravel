<?php

namespace App\Http\Controllers;

use App\Models\AddFlagOnContent;
use App\Models\ContentFlag;
use App\Models\Content;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddFlagOnContentController extends Controller
{
    /**
     * POST /flags/add
     * body: content_id, flag_id, flagged_by, comment? (optional)
     */
    public function addFlagOnContent(Request $request)
    {
        $data = $request->validate([
            'content_id' => ['required', 'integer', 'exists:contents,id'],
            'flag_id'    => ['required', 'integer', 'exists:content_flags,id'],
            'flagged_by' => ['required', 'integer', 'exists:users,id'],
            'comment'    => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            // Duplicate check: same user cannot flag the same content twice
            $exists = AddFlagOnContent::where('content_id', $data['content_id'])
                ->where('flagged_by', $data['flagged_by'])
                ->first();

            if ($exists) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'You have already flagged this content.',
                    'data'    => $exists->load(['content', 'flag', 'user']),
                ], 409);
            }

            $flag = DB::transaction(function () use ($data) {
                return AddFlagOnContent::create([
                    'content_id'  => $data['content_id'],
                    'flag_id'     => $data['flag_id'],
                    'flagged_by'  => $data['flagged_by'],
                    'is_reviewed' => false,
                    'comment'     => $data['comment'] ?? null,
                    'review_note' => null,
                ]);
            });

            return response()->json([
                'status'  => 201,
                'message' => 'Flag added successfully.',
                'data'    => $flag->load(['content', 'flag', 'user']),
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Failed to add flag.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /flags/remove
     * body: content_id, user_id (must match flagged_by)
     * optional: flag_id (if you want to be stricter)
     */
    public function removeFlagOnContent(Request $request)
    {
        $data = $request->validate([
            'content_id' => ['required', 'integer', 'exists:contents,id'],
            'user_id'    => ['required', 'integer', 'exists:users,id'],
            'flag_id'    => ['nullable', 'integer', 'exists:content_flags,id'],
        ]);

        try {
            $q = AddFlagOnContent::where('content_id', $data['content_id'])
                ->where('flagged_by', $data['user_id']);

            if (!empty($data['flag_id'])) {
                $q->where('flag_id', $data['flag_id']);
            }

            $flag = $q->first();

            if (!$flag) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'No flag found for this user and content.',
                ], 404);
            }

            $flag->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Flag removed successfully.',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Failed to remove flag.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PATCH /flags/comment
     * body: flag_id OR (content_id + user_id), comment
     * Only the same flagging user can update their comment (simple rule).
     */
    public function addComment(Request $request)
    {
        $data = $request->validate([
            'flag_id'    => ['nullable', 'integer', 'exists:add_flag_on_contents,id'],
            'content_id' => ['nullable', 'integer', 'exists:contents,id'],
            'user_id'    => ['required', 'integer', 'exists:users,id'],
            'comment'    => ['required', 'string', 'max:2000'],
        ]);

        try {
            // Resolve the flag record either by id or composite keys
            $flag = null;

            if (!empty($data['flag_id'])) {
                $flag = AddFlagOnContent::where('id', $data['flag_id'])->first();
            } elseif (!empty($data['content_id'])) {
                $flag = AddFlagOnContent::where('content_id', $data['content_id'])
                    ->where('flagged_by', $data['user_id'])
                    ->first();
            }

            if (!$flag) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Flag record not found.',
                ], 404);
            }

            // Permission: only the flag owner can change their comment
            if ((int)$flag->flagged_by !== (int)$data['user_id']) {
                return response()->json([
                    'status'  => 403,
                    'message' => 'You are not allowed to update this comment.',
                ], 403);
            }

            $flag->comment = $data['comment'];
            $flag->save();

            return response()->json([
                'status'  => 200,
                'message' => 'Comment updated.',
                'data'    => $flag->fresh()->load(['content', 'flag', 'user']),
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Failed to update comment.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
