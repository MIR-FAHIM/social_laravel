<?php

namespace App\Http\Controllers;
use App\Models\LifeEvent;
use Illuminate\Http\Request;

class LifeEventController extends Controller
{
    //
    public function addEvent(Request $request)
    {
        try {
            // Attempt to create the event
            $event = LifeEvent::create($request->all());
    
            // Return success response if event creation is successful
            return response()->json([
                'status' => 200,
                'success' => true, 
                'message'=> "Event added successfully",
                'event' => $event
            ], 200);
    
        } catch (\Exception $e) {
            // Catch any exceptions and return an error response
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to create event.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
public function getEventsByUser($user_id)
{
    $events = LifeEvent::where('user_id', $user_id)->get();
    return response()->json([ 'status' => 200,'success' => true, 'events' => $events], 200);
}
public function updateEvent(Request $request, $id)
{
    $event = LifeEvent::findOrFail($id);
    if ($event->user_id == $request->user_id) {
        $event->update($request->all());
        return response()->json([ 'status' => 200,'success' => true, 'event' => $event], 200);
    }
    return response()->json([ 'status' => 200,'success' => false, 'message' => 'Unauthorized'], 403);
}
public function deleteEvent($id)
{
    $event = LifeEvent::where('id', $id)->first();
    if ($event) {
        $event->delete();
        return response()->json([ 'status' => 200,'success' => true, 'message' => 'Event deleted'], 200);
    }
    return response()->json([ 'status' => 200,'success' => false, 'message' => 'Event not found'], 404);
}


}
