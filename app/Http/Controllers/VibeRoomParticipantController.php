<?php

namespace App\Http\Controllers;

use App\Models\VibeRoom;
use App\Models\VibeRoomParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VibeRoomParticipantController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. Join a Vibe Room
    |--------------------------------------------------------------------------
    */
    public function joinRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vibe_room_id' => 'required|exists:vibe_rooms,id',
            'user_id'      => 'required|integer',
            'is_anonymous' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Prevent duplicate join
        $exists = VibeRoomParticipant::where([
            'vibe_room_id' => $request->vibe_room_id,
            'user_id' => $request->user_id
        ])->first();

        if ($exists) {
            return response()->json(['status' => true, 'message' => 'Already joined', 'data' => $exists]);
        }

        $participant = VibeRoomParticipant::create([
            'vibe_room_id' => $request->vibe_room_id,
            'user_id'      => $request->user_id,
            'role'         => 'guest',
            'is_anonymous' => $request->is_anonymous ?? true, // default anonymous
            'guess_progress' => 0,
            'is_kicked' => false,
            'is_banned' => false,
            'last_active_at' => now(),
        ]);

        return response()->json(['status' => true, 'message' => 'Joined room', 'data' => $participant]);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Leave Room
    |--------------------------------------------------------------------------
    */
    public function leaveRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vibe_room_id' => 'required|exists:vibe_rooms,id',
            'user_id'      => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        VibeRoomParticipant::where([
            'vibe_room_id' => $request->vibe_room_id,
            'user_id' => $request->user_id
        ])->delete();

        return response()->json(['status' => true, 'message' => 'Left room']);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Toggle Anonymous Mode
    |--------------------------------------------------------------------------
    */
    public function toggleAnonymous(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'participant_id' => 'required|exists:vibe_room_participants,id',
            'is_anonymous'   => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $participant = VibeRoomParticipant::find($request->participant_id);
        $participant->is_anonymous = $request->is_anonymous;
        $participant->save();

        return response()->json([
            'status' => true,
            'message' => 'Anonymous mode updated',
            'data' => $participant
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Reveal Identity
    |--------------------------------------------------------------------------
    */
    public function revealIdentity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'participant_id' => 'required|exists:vibe_room_participants,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $participant = VibeRoomParticipant::find($request->participant_id);
        $participant->is_anonymous = false;
        $participant->save();

        return response()->json(['status' => true, 'message' => 'Identity revealed', 'data' => $participant]);
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Guess Identity Progress
    |--------------------------------------------------------------------------
    */
    public function guessIdentity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'participant_id' => 'required|exists:vibe_room_participants,id',
            'progress'       => 'required|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $participant = VibeRoomParticipant::find($request->participant_id);
        $participant->guess_progress = min(100, $participant->guess_progress + $request->progress);

        // If 100%, reveal identity
        if ($participant->guess_progress >= 100) {
            $participant->is_anonymous = false;
        }

        $participant->save();

        return response()->json(['status' => true, 'message' => 'Guess updated', 'data' => $participant]);
    }

    /*
    |--------------------------------------------------------------------------
    | 6. Kick From Room
    |--------------------------------------------------------------------------
    */
    public function kickParticipant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'participant_id' => 'required|exists:vibe_room_participants,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $participant = VibeRoomParticipant::find($request->participant_id);
        $participant->is_kicked = true;
        $participant->save();

        return response()->json(['status' => true, 'message' => 'Participant kicked', 'data' => $participant]);
    }

    /*
    |--------------------------------------------------------------------------
    | 7. List Participants
    |--------------------------------------------------------------------------
    */
    public function roomParticipants($roomId)
    {
        $list = VibeRoomParticipant::where('vibe_room_id', $roomId)
            ->with('user:id,name,profile_pic')
            ->get();

        return response()->json(['status' => true, 'data' => $list]);
    }
}
