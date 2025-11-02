<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ProfileImage;
use Illuminate\Support\Facades\Storage;

class ProfileImageController extends Controller
{
    /**
     * Upload up to 5 profile images
     */
    public function uploadProfileImages(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'images' => 'required|array|max:5',  // Limit to 5 images
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',  // Validate images
        ]);

        $user_id = $request->user_id; // Assuming user is authenticated

        // Get existing image count for the user
        $existingImagesCount = ProfileImage::where('user_id', $user_id)->count();

        if ($existingImagesCount + count($request->images) > 4) {
            return response()->json(['error' => 'Cannot upload more than 4 images.'], 422);
        }

        foreach ($request->file('images') as $index => $image) {
            // Save the image in storage
            $path = $image->store('profile_images', 'public');

            // Save image details in the database
            ProfileImage::create([
                'user_id' => $user_id,
                'image_path' => $path,
                'serial' => $existingImagesCount + $index + 1,  // Ensure order is continuous
            ]);
        }

        return response()->json([
            'status'=> 200,
            'message' => 'Images uploaded successfully'], 200);
    }

    /**
     * Get the profile images in custom order
     */
    public function getProfileImages(Request $request)
    {
        try {
            // Assuming the user_id is passed in the request
            $user_id = $request->user_id;
    
            // Fetch images for the user ordered by serial_order
            $images = ProfileImage::where('user_id', $user_id)
                ->orderBy('serial', 'asc')
                ->get();
    
            // If no images are found, return a meaningful message
            if ($images->isEmpty()) {
                return response()->json([
                    "status" =>404,
                    'message' => 'No profile images found for this user.'
                ], 404);
            }
    
            // Return the images in JSON format
            return response()->json([
                "status" => 200,
               "data" => $images,],
                 200);
            
        } catch (\Exception $e) {
            // If an exception occurs, return a generic error message
            return response()->json([
                'error' => 'An error occurred while fetching profile images.',
                'message' => $e->getMessage()  // For debugging purposes (remove in production)
            ], 500);
        }
    }
    

    /**
     * Update the order of images
     */
    public function updateImageOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'image_order' => 'required|array',  // List of image IDs in the new order
        ]);
        $user_id = $request->user_id;
        foreach ($request->image_order as $order => $image_id) {
            // Ensure the image belongs to the authenticated user
            ProfileImage::where('id', $image_id)
                ->where('user_id', $user_id)
                ->update(['serial' => $order + 1]);
        }

        return response()->json(['message' => 'Image order updated'], 200);
    }

    /**
     * Delete a profile image
     */
    public function deleteProfileImage(Request $request)
    {
        $user_id = $request->user_id;
        $id = $request->id;
    
        try {
            $image = ProfileImage::where('id', $id)
                ->where('user_id', $user_id)
                ->first();
    
            if (!$image) {
                return response()->json(['status'=>404,'message' => 'Image not found or you are not authorized.'], 404);
            }
    
            // Delete the image from storage
            Storage::disk('public')->delete($image->image_path);
    
            // Delete the image record from the database
            $image->delete();
    
            // Re-order the remaining images
            $this->reorderImagesAfterDeletion($user_id);
    
            return response()->json(['status'=>200,
            'message' => 'Image deleted successfully'], 200);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
           
    
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Re-order images after one is deleted
     */
    private function reorderImagesAfterDeletion($user_id)
    {
        $images = ProfileImage::where('user_id', $user_id)
            ->orderBy('serial', 'asc')
            ->get();

        foreach ($images as $index => $image) {
            $image->serial = $index + 1;
            $image->save();
        }
    }
}
