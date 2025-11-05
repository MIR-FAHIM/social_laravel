<?php

namespace App\Http\Controllers;

use App\Models\ContentFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;

class ContentFlagController extends Controller
{
    /**
     * Store a newly created Content Flag.
     */
    public function addContentFlag(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'flag_name' => 'required|string|max:255|unique:content_flags,flag_name',
                'note'      => 'nullable|string',
                'score'     => 'nullable|integer',
                'is_positive'     => 'nullable|integer',
                'icon'      => 'nullable|file|mimes:jpg,jpeg,png,svg,webp|max:2048',
                'is_active' => 'nullable|boolean|default:true',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $iconPath = null;
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $iconPath = $file->storeAs('content_flags', $filename, 'public'); // stored in storage/app/public/content_flags
            }

            $flag = ContentFlag::create([
                'flag_name' => $request->flag_name,
                'note'      => $request->note,
                'score'     => $request->input('score', 0),
                'icon'      => $iconPath,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return response()->json([
                'status'  => 201,
                'message' => 'Content flag created successfully.',
                'data'    => $flag,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'error'   => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all content flags.
     */
    public function getContentFlag()
    {
        try {
            $flags = ContentFlag::orderBy('flag_name')->get();

            return response()->json([
                'status'  => 200,
                'message' => 'Flags retrieved successfully.',
                'data'    => $flags,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'error'   => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
