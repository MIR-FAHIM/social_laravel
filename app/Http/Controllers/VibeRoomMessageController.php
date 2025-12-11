<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VibeRoom;
use App\Models\VibeRoomMessage;
use App\Models\VibeRoomParticipant;
use Illuminate\Http\Request;

class VibeRoomMessageController extends Controller
{
    /**
     * Fetch messages inside a vibe room.
     */
    public function getRoomMessages($roomId)
    {
        $room = VibeRoom::find($roomId);

        if (!$room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        $messages = VibeRoomMessage::where('vibe_room_id', $roomId)->with(['sender', 'participant'])
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'messages' => $messages
        ]);
    }

    /**
     * Send a new message.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:vibe_rooms,id',
            'user_id' => 'required|exists:users,id',
            'message_content' => 'required|string',
            'is_anonymous' => 'boolean'
        ]);

        $user = User::find($request->user_id);
        $room = VibeRoom::find($request->room_id);

        if (!$room->is_active) {
            return response()->json(['error' => 'Room is no longer active'], 403);
        }

        // Confirm user is a participant
        $participant = VibeRoomParticipant::where('vibe_room_id', $room->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$participant) {
            return response()->json(['error' => 'Not a participant'], 403);
        }

        $msg = VibeRoomMessage::create([
            'vibe_room_id' => $room->id,
            'user_id' => $user->id,
            'participant_id' => $participant->id,
            'message_content' => $request->message_content,
            'is_anonymous' => $request->is_anonymous ?? false,
            'reactions' => [],
            'guess_progress' => 0,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $msg
        ]);
    }

    /**
     * Add or remove a reaction.
     */
    public function addReaction(Request $request, $messageId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reaction' => 'required|string'
        ]);

        $msg = VibeRoomMessage::find($messageId);

        if (!$msg) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $reactions = $msg->reactions ?? [];

        if (in_array($request->reaction, $reactions)) {
            // remove
            $reactions = array_values(array_filter($reactions, fn($r) => $r !== $request->reaction));
        } else {
            // add
            $reactions[] = $request->reaction;
        }

        $msg->update(['reactions' => $reactions]);

        return response()->json([
            'status' => 'success',
            'reactions' => $reactions
        ]);
    }

    /**
     * Guess identity: each guess increases reveal progress.
     */
    public function guessIdentity(Request $request, $messageId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'guess_name' => 'required|string'
        ]);

        $user = User::find($request->user_id);
        $msg = VibeRoomMessage::find($messageId);

        if (!$msg) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $progress = $msg->guess_progress + 20;
        $msg->guess_progress = min($progress, 100);
        $msg->save();

        return response()->json([
            'status' => 'success',
            'progress' => $msg->guess_progress,
            'revealed' => $msg->guess_progress >= 100 ? $msg->sender?->name : null
        ]);
    }

    /**
     * User reveals his own identity.
     */
    public function revealYourIdentity(Request $request, $messageId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);
        $msg = VibeRoomMessage::find($messageId);

        if (!$msg) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        if ($user->id != $msg->user_id) {
            return response()->json(['error' => 'Only message owner can reveal'], 403);
        }

        $msg->update(['is_anonymous' => false]);

        return response()->json([
            'status' => 'success',
            'revealed_name' => $user->name
        ]);
    }

    /**
     * Flag inappropriate message.
     */
    public function flagMessage(Request $request, $messageId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $msg = VibeRoomMessage::find($messageId);

        if (!$msg) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $msg->update(['is_flagged' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Message flagged for review'
        ]);
    }

    /**
     * Host hides/deletes message.
     */
    public function hideMessage(Request $request, $messageId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);
        $msg = VibeRoomMessage::find($messageId);

        if (!$msg) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        $room = VibeRoom::find($msg->vibe_room_id);

        if ($user->id !== $room->host_user_id) {
            return response()->json(['error' => 'Only host can hide messages'], 403);
        }

        $msg->update(['is_hidden' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Message removed'
        ]);
    }
}
