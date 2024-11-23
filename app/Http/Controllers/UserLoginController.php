<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserLoginController extends Controller
{


public function login(Request $request)

   {
    // Validate the input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string',
        'fcm_token' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Find the user by email
    $user = User::where('email', $request->email)->first();

    // If user is not found
    if (!$user) {
        return response()->json(['error' => 'Invalid email or password.'], 401);
    }

    // Check if the password matches
    
    if (Hash::check($request->password, $user->password)) {
        // Return success response
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'status' => 200,
          
            'message' => 'Logged in successfully.',
            'user' => $user
        ], 200);
    } else {
        // If password does not match
        return response()->json(['error' => 'Invalid email or password.'], 401);
    }

}
}
