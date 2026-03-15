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
    // Validate input
    $validator = Validator::make($request->all(), [
        'email' => 'required|string',
        'password' => 'required|string',
        'fcm_token' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $loginInput = $request->email;

    // Detect if input is mobile number (11 digits)
    if (preg_match('/^[0-9]{11}$/', $loginInput)) {
        $user = User::where('mobile', $loginInput)->first();
    } else {
        $user = User::where('email', $loginInput)->first();
    }

    // User not found
    if (!$user) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Not Registered'
        ], 401);
    }

    // Check password
    if (Hash::check($request->password, $user->password)) {

        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Logged in successfully.',
            'user' => $user
        ], 200);

    } else {
        return response()->json([
            'error' => 'Invalid email/mobile or password.'
        ], 401);
    }
}
}
