<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Content;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Models\ContentLike;
use Illuminate\Http\Request;
use App\Services\FirebaseNotificationService;

class ContentLikeController extends Controller
{
    // Add like
    protected $pushNotificationService;
    public function __construct(FirebaseNotificationService $pushNotificationService)
    {
        $this->pushNotificationService = $pushNotificationService;
    }
    public function like(Request $request)
    {
        try {
            $user_id = $request->user_id; // Ensure user_id is passed in the request
            $content_id = $request->content_id; // Ensure content_id is passed in the request
    
            // Check if the content exists
            $content = Content::findOrFail($content_id);
            $user = User::findOrFail($user_id);
            $contentUserId = $content->user_id;
            $contentUser = User::find($contentUserId);
            // Check if the user has already liked the content
            $existing_like = ContentLike::where('user_id', $user_id)
                                        ->where('content_id', $content_id)
                                        ->first();
            
            if ($existing_like) {
                return response()->json([
                    'status' => 400,
                    'message' => 'You have already liked this content'
                ], 400);
            }
    
            // Add the like
            $like = new ContentLike();
            $like->user_id = $user_id;
            $like->content_id = $content_id;
            $like->save();
    
            // Increment like_count in Content
            $content->increment('like_count');
    
            // Create notification
            Notification::create([
                'receiver_id' => $content->user_id,
                'sender_id' => $request->user_id,
                'title' => $user->name . ' liked your content',
                'type' => 'liked',
                'body' => Str::limit($content->text_content, 20, '...'),
                'is_read' => false,
                'content_id' => $request->content_id,
                'friend_req_id' => null,
            ]);
    
           // Send push notification if applicable (optional)
            if ($request->user_id !== $contentUserId ) {
                $result = $this->pushNotificationService->sendPushNotification(
                    $contentUser->fcm_token,
                    "Content Like",
                    $user->name . ' liked your content',
                );
                
                if (!$result['success']) {
                    // Log or handle the error as needed
                }
            }
    
            return response()->json([
                'status' => 200,
                'liked_by' => $user->name,
                'liked_to' => $content->user_id,
                'message' => $user->name . ' liked your content',
                'like_count' => $content->like_count  // Return updated like count
            ], 200);
    
        } catch (\Exception $e) {
            // Log the error for debugging
        
    
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while liking the content',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    // Undo like
    public function unlike(Request $request)
    {
        $user_id = $request->user_id; // Ensure user_id is passed in the request
        $content_id = $request->content_id; // Ensure content_id is passed in the request

        // Check if the content exists
        $content = Content::findOrFail($content_id);

        // Check if the user has liked the content
        $existing_like = ContentLike::where('user_id', $user_id)
                                    ->where('content_id', $content_id)
                                    ->first();
        
        if (!$existing_like) {
            return response()->json([
                'status'=> 400,
                'message' => 'You have not liked this content'
            ], 400);
        }

        // Remove the like
        $existing_like->delete();

        // Decrement like_count in Content
        $content->decrement('like_count');

        return response()->json([
            'status' => 200,
            'message' => 'Like removed successfully',
            'like_count' => $content->like_count  // Return updated like count
        ], 200);
    }

    public function getContentLikes(Request $request)
{
    try {
        $content_id = $request->content_id; // Ensure content_id is passed in the request

        // Validate content_id
        $request->validate([
            'content_id' => 'required|exists:contents,id'
        ]);

        // Fetch the content
        $content = Content::findOrFail($content_id);

        // Fetch the users who liked this content
        $likedUsers = ContentLike::where('content_id', $content_id)
            ->with('user:id,name,email,profile_photo_path') // Assuming user relation exists in ContentLike
            ->get();

        // Map the result to return user details
        $result = $likedUsers->map(function ($like) {
            return [
                'user_id' => $like->user->id,
                'name' => $like->user->name,
                'email' => $like->user->email,
                'image' => $like->user->profile_photo_path
            ];
        });

        return response()->json([
            'status' => 200,
            'data' => $result,
            'message' => 'Users who liked the content retrieved successfully'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'An error occurred while retrieving likes',
            'error' => $e->getMessage()
        ], 500);
    }
}

}

