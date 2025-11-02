<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\HarmonyOfLife;
use Illuminate\Support\Facades\Auth;

class HarmonyOfLifeController extends Controller
{
    // Store harmony of life data for a user
    public function store(Request $request)
    {
        try {
            // Retrieve the user by user_id passed in the request
            $user = User::find($request->user_id);
    
            // If the user is not found, return an error response
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
    
            // Validate the input data
            $validatedData = $request->validate([
                'happiness' => 'required|numeric|min:0|max:100',
                'sadness' => 'required|numeric|min:0|max:100',
                'joyfulness' => 'required|numeric|min:0|max:100',
                'excitement' => 'required|numeric|min:0|max:100',
                'calmness' => 'required|numeric|min:0|max:100',
                'fear' => 'required|numeric|min:0|max:100',
                'anger' => 'required|numeric|min:0|max:100',
                'surprise' => 'required|numeric|min:0|max:100',
            ]);
    
            // Calculate the harmony percentage
            $harmony_percentage = array_sum([
                $request->happiness, 
                $request->sadness, 
                $request->joyfulness, 
                $request->excitement, 
                $request->calmness, 
                $request->fear, 
                $request->anger, 
                $request->surprise
            ]) / 8;
    
            // Update or create the harmony data for the given user_id
            $harmony = HarmonyOfLife::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'happiness' => $request->happiness,
                    'sadness' => $request->sadness,
                    'joyfulness' => $request->joyfulness,
                    'excitement' => $request->excitement,
                    'calmness' => $request->calmness,
                    'fear' => $request->fear,
                    'anger' => $request->anger,
                    'surprise' => $request->surprise,
                    'harmony_percentage' => $harmony_percentage,
                ]
            );
    
            return response()->json([
                'status' => 200,
                'message' => 'Harmony of Life data saved successfully', 'harmony' => $harmony], 200);
    
        } catch (\Exception $e) {
            // Log the error for debugging purposes
           
            // Return a 500 error response with the exception message
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }
    


    // Get harmony of life data by user_id
    public function show($user_id)
    {
        $harmony = HarmonyOfLife::where('user_id', $user_id)->first();

        if (!$harmony) {
            return response()->json(['message' => 'No data found for this user'], 404);
        }

        return response()->json(
            [
                "status" =>200,
                "data" => $harmony], 
            200);
    }
}
