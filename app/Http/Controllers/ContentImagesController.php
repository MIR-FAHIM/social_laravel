<?php

namespace App\Http\Controllers;
use App\Models\Content;
use App\Models\User;
use App\Models\ContentImage;
use Illuminate\Http\Request;

class ContentImagesController extends Controller
{
   public function uploadContentImages(Request $request)
{
    try {
        // Validate the input
        $validatedData = $request->validate([
            'content_id' => 'required|exists:contents,id',
            'user_id' => 'required',
            'media' => 'required',
            'media.*' => 'mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:50000', // Validate each media file (image or video)
        ]);

        // Find the content by ID
        $content = Content::findOrFail($request->content_id); // Use findOrFail for better error handling
        $user = User::findOrFail($request->user_id);

        // Handle file uploads
        if ($request->hasFile('media')) {
            $mediaFiles = $request->file('media');
            $mediaPaths = [];

            // If only one media file is uploaded
            if (count($mediaFiles) == 1) {
                // Store the single media file and update `single_image` field in the content table
                $mediaPath = $mediaFiles[0]->store('uploads/contents', 'public');
                
                // Save the media path (image or video) to `single_image`
                $content->update(['single_image' => $mediaPath]);

            } else {
                // Handle multiple media files
                foreach ($mediaFiles as $index => $mediaFile) {
                    $mediaPath = $mediaFile->store('uploads/contents', 'public');

                    // Set the first image/video as the `single_image`
                    if ($index == 0) {
                        $content->update(['single_image' => $mediaPath]);
                    }

                    // Save all media to the `content_media` table
                    ContentImage::create([
                        'content_id' => $content->id,
                        'user_id' => $user->id,
                        'image_path' => $mediaPath,
                        'media_type' => str_contains($mediaFile->getMimeType(), 'image') ? 'image' : 'video', // Save type for distinction
                    ]);

                    $mediaPaths[] = $mediaPath;
                }
            }

            return response()->json([
                'message' => 'Media uploaded successfully',
                'single_image' => $content->single_image, // Only `single_image` will be used
                'media_paths' => $mediaPaths
            ], 201);
        }

        return response()->json(['message' => 'No media uploaded'], 400);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Handle if content or user is not found
        return response()->json([
            'message' => 'Content or User not found',
            'error' => $e->getMessage()
        ], 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle validation errors
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        // Handle general errors
        return response()->json([
            'message' => 'An error occurred during upload',
            'error' => $e->getMessage()
        ], 500);
    }
}

    
    
}
