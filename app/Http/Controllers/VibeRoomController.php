<?php

namespace App\Http\Controllers;

use App\Models\VibeRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VibeRoomController extends Controller
{
    /**
     * Create a new vibe room
     */
    public function create(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'host_user_id'   => 'required|integer|exists:users,id',
            'mood_id'        => 'required|integer|exists:mood_masters,id',
            'room_title'     => 'required|string|max:255',
            'vibe_details'   => 'nullable|string',
            'expire_time'    => 'required|date',
            'allow_guessing' => 'required|boolean',
            'allow_reveal'   => 'required|boolean',
            'color'          => 'nullable|string|max:20',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validate->errors()->first(),
            ], 422);
        }

        $room = VibeRoom::create([
            'host_user_id'   => $request->host_user_id,
            'mood_id'        => $request->mood_id,
            'room_title'     => $request->room_title,
            'vibe_details'   => $request->vibe_details,
            'expire_time'    => Carbon::parse($request->expire_time),
            'allow_guessing' => $request->allow_guessing,
            'allow_reveal'   => $request->allow_reveal,
            'is_active'      => true,
            'color'          => $request->color ?? '#FFFFFF',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Vibe room created successfully',
            'data'    => $room
        ]);
    }

    /**
     * Get all active & non-expired rooms
     */
    public function allActiveRooms()
    {
        $rooms = VibeRoom::active()->with(['host', 'mood'])->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => true,
            'data'   => $rooms
        ]);
    }

    /**
     * Get rooms created by a specific host
     */
    public function hostRooms($userId)
    {
        $rooms = VibeRoom::where('host_user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $rooms
        ]);
    }

    /**
     * Get single room details
     */
    public function show($id)
    {
        $room = VibeRoom::with(['host', 'mood', 'participants'])->find($id);

        if (!$room) {
            return response()->json([
                'status'  => false,
                'message' => 'Room not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $room
        ]);
    }

    /**
     * Update room details
     */
    public function update(Request $request, $id)
    {
        $room = VibeRoom::find($id);

        if (!$room) {
            return response()->json([
                'status'  => false,
                'message' => 'Room not found'
            ], 404);
        }

        $room->update($request->only([
            'room_title',
            'vibe_details',
            'allow_guessing',
            'allow_reveal',
            'color',
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Vibe room updated successfully',
            'data'    => $room
        ]);
    }

    /**
     * Close room (force expire)
     */
    public function closeRoom($id)
    {
        $room = VibeRoom::find($id);

        if (!$room) {
            return response()->json([
                'status'  => false,
                'message' => 'Room not found',
            ], 404);
        }

        $room->update([
            'is_active'    => false,
            'expire_time'  => Carbon::now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Room closed successfully'
        ]);
    }

    /**
     * Delete room
     */
    public function delete($id)
    {
        $room = VibeRoom::find($id);

        if (!$room) {
            return response()->json([
                'status'  => false,
                'message' => 'Room not found',
            ], 404);
        }

        $room->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Room deleted successfully'
        ]);
    }
}
