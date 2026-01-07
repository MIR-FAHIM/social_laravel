<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserSignupController extends Controller
{
    public function signUp(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            // Add validation for other fields if needed
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'profile_photo_path' => $request->profile_photo_url,
            'password' => Hash::make($request->password),
            // Set default values for other fields if needed
        ]);

        return response()->json([
            'status' =>200,
            'message' => 'User registered successfully', 'user' => $user], 201);
    }
}
