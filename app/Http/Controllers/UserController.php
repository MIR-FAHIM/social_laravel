<?php



namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;




class UserController extends Controller
{
    public function uploadImage(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Validate image file
            ]);

            // Get the authenticated user
            $user = User::where('id', $request->user_id)->first();

            // Handle file upload
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/profile_images', $fileName);

                // Update user's profile image path in database
                $user->profile_photo_path = $fileName;
                $user->save();
            }

            return response()->json([
                'message' => 'Image uploaded successfully',
                'user'=> $user,
                'image_path' => Storage::url('profile_images/' . $fileName),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getProfile($id)
    {
        try {
            // Find the user by ID
            $user = User::findOrFail($id);

            // Return the user details
            return response()->json([
                'status' => 200,
                'message' => 'Profile fetched successfully',
                'data' => $user
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function numberExists(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|string',
                'exclude_user_id' => 'nullable|integer',
            ]);

            $phone = $request->input('phone');

            $query = User::where('phone', $phone);
            if ($request->filled('exclude_user_id')) {
                $query->where('id', '!=', $request->input('exclude_user_id'));
            }

            $exists = $query->exists();

            return response()->json([
                'exists' => $exists
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 422,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
