<?php

namespace App\Http\Controllers;

use App\Models\Badges;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BadgesController extends Controller
{
    /**
     * Display a listing of all badges.
     */
    public function index()
    {
        $badges = Badges::orderBy('name')->get();

        return response()->json([
            'status' => 200,
            'data' => $badges,
        ]);
    }

    /**
     * Store a newly created badge in storage (with icon upload).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255|unique:badges,name',
            'role'        => 'required|string|max:255',
            'power'       => 'nullable|string',
            'limitation'  => 'nullable|string',
            'is_active'   => 'boolean',
            'count'       => 'integer|min:0',
            'rules'       => 'nullable|array',
            'tips'        => 'nullable|array',
            'icon'        => 'nullable|file|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Handle icon upload
        $iconPath = null;
        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $iconPath = $file->storeAs('badges', $filename, 'public'); // stored in storage/app/public/badges
        }

        $badge = Badges::create([
            'name'        => $request->name,
            'role'        => $request->role,
            'power'       => $request->power,
            'limitation'  => $request->limitation,
            'is_active'   => $request->boolean('is_active', true),
            'count'       => $request->input('count', 0),
            'rules'       => $request->input('rules'),
            'tips'        => $request->input('tips'),
            'icon'        => $iconPath,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Badge created successfully.',
            'data' => $badge,
        ], 201);
    }

    /**
     * Display a single badge.
     */
    public function show($id)
    {
        $badge = Badges::find($id);

        if (!$badge) {
            return response()->json([
                'status' => 404,
                'message' => 'Badge not found',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $badge,
        ]);
    }

    /**
     * Update a badge and (optionally) replace its icon.
     */
    public function update(Request $request, $id)
    {
        $badge = Badges::find($id);

        if (!$badge) {
            return response()->json([
                'status' => 404,
                'message' => 'Badge not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|string|max:255|unique:badges,name,' . $badge->id,
            'role'        => 'sometimes|string|max:255',
            'power'       => 'nullable|string',
            'limitation'  => 'nullable|string',
            'is_active'   => 'boolean',
            'count'       => 'integer|min:0',
            'rules'       => 'nullable|array',
            'tips'        => 'nullable|array',
            'icon'        => 'nullable|file|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Replace icon if provided
        if ($request->hasFile('icon')) {
            if ($badge->icon && Storage::disk('public')->exists($badge->icon)) {
                Storage::disk('public')->delete($badge->icon);
            }

            $file = $request->file('icon');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $badge->icon = $file->storeAs('badges', $filename, 'public');
        }

        $badge->fill($request->except('icon'))->save();

        return response()->json([
            'status' => 200,
            'message' => 'Badge updated successfully.',
            'data' => $badge,
        ]);
    }

    /**
     * Remove the specified badge.
     */
    public function destroy($id)
    {
        $badge = Badges::find($id);

        if (!$badge) {
            return response()->json([
                'status' => 404,
                'message' => 'Badge not found',
            ], 404);
        }

        if ($badge->icon && Storage::disk('public')->exists($badge->icon)) {
            Storage::disk('public')->delete($badge->icon);
        }

        $badge->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Badge deleted successfully.',
        ]);
    }
}
