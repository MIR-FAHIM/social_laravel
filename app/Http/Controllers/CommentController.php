<?php

namespace App\Http\Controllers;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Notification;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Content;
use Illuminate\Http\Request;
use App\Services\FirebaseNotificationService;
class CommentController extends Controller
{

    protected $pushNotificationService;



    public function __construct(FirebaseNotificationService $pushNotificationService)
    {
        $this->pushNotificationService = $pushNotificationService;
    }
   
    public function store(Request $request)
{
    $user_id = $request->user_id; // Ensure user_id is passed in the request
    $content_id = $request->post_content_id; // Ensure post_content_id is passed in the request

    try {
        // Validate incoming request
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'post_content_id' => 'required|exists:contents,id',  // Assuming the content table is 'contents'
            'comment' => 'required|string',
            'comment_type' => 'required|in:comment,debate',
            'color' => 'nullable|in:green,blue',
            'status' => 'required|in:approved,pending,rejected',
        ]);

        // Check if the content is a debate
        $content = Content::findOrFail($request->post_content_id);
        $isDebate = $content->is_debate;

        if ($isDebate && $request->comment_type === 'debate') {
            // Ensure color is valid for debate discussions
            if (!in_array($request->color, ['green', 'blue'])) {
                return response()->json([
                    'message' => 'Invalid color for a debate discussion.',
                ], 400);
            }
        }

        $commentUser = User::findOrFail($user_id);
        $contentUserId = $content->user_id;
        $contentUser = User::findOrFail($contentUserId);

        // Increment the comment count on the content
        $content->increment('comment_count');

        // Create the comment
        $comment = Comment::create([
            'user_id' => $user_id,
            'post_content_id' => $content_id,
            'comment' => $request->comment,
            'comment_type' => $request->comment_type,
            'color' => $request->color,
            'status' => $request->status,
        ]);

        // Only create notification and push if the commenter is not the content owner
        if ($user_id !== $contentUserId) {
            // Create notification in the database
            Notification::create([
                'receiver_id' => $contentUserId,
                'sender_id' => $user_id,
                'title' =>  $commentUser->name . ' commented on your content',
                'type' => 'commented',
                'body' =>  Str::limit($request->comment, 20, '...'),
                'is_read' => false,
                'content_id' => $content_id,
                'friend_req_id' => 0,
            ]);

            // Send a push notification
            $result = $this->pushNotificationService->sendPushNotification(
                $contentUser->fcm_token,
                "Comment",
                $commentUser->name . ' commented on your content'
            );

            // Handle push notification failure (optional)
            if (!$result['success']) {
                // Log or handle the error as needed
            }
        }

        // Return success response
        return response()->json([
            'status' => 200,
            'message' => 'Comment posted successfully',
            'comment' => $comment,
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Handle the case where the content or user is not found
        return response()->json([
            'error' => 'Content or User not found',
            'message' => $e->getMessage(),
        ], 404);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle validation errors
        return response()->json([
            'error' => 'Validation error',
            'message' => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        // Handle general exceptions
        return response()->json([
            'error' => 'Something went wrong',
            'message' => $e->getMessage(),
        ], 500);
    }
}


    // API to get comments by content ID
    public function getCommentsByContent($contentId)
    {
        $comments = Comment::where('post_content_id', $contentId)
            ->with('user') // Get user details along with the comments
            ->get();

        return response()->json([
            'status' => 200,
            'comments' => $comments,
        ], 200);
    }

//vote oon comment
public function voteOnComment(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'comment_id' => 'required|exists:comments,id',
        'vote_type' => 'required|in:upvote,downvote',
    ]);

    try {
        $comment = Comment::findOrFail($request->comment_id);

        // Check if the user has already voted
        $existingVote = CommentVote::where('comment_id', $request->comment_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingVote) {
            // Check if the vote type is the same as the existing vote
            if ($existingVote->vote_type === $request->vote_type) {
                return response()->json([
                    'status' => 200,
                    'message' => 'You have already ' . $request->vote_type . 'd this comment.',
                ], 200);
            } else {
                // Update vote if the type has changed
                $existingVote->vote_type = $request->vote_type;
                $existingVote->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Vote updated successfully',
                ], 200);
            }
        } else {
            // Create a new vote
            CommentVote::create([
                'comment_id' => $request->comment_id,
                'user_id' => $request->user_id,
                'vote_type' => $request->vote_type,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Vote added successfully',
            ], 200);
        }

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'error' => 'Comment not found',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong',
            'message' => $e->getMessage(),
        ], 500);
    }
}



public function getCommentsByUpvotes(Request $request)
{
    $userId = $request->user_id;  // Assume user_id is sent in the request

    $comments = Comment::where('post_content_id', $request->content_id)
        ->with('user')
        ->withCount([
            'votes as upvotes_count' => function ($query) {
                $query->where('vote_type', 'upvote');
            },
            'votes as downvotes_count' => function ($query) {
                $query->where('vote_type', 'downvote');
            },
        ])
        ->with(['votes' => function ($query) use ($userId) {
            $query->where('user_id', $userId);  // Filter votes by current user
        }])
        ->orderByDesc('upvotes_count')  // Sort by calculated upvote count
        ->get();

    // Add `isUpvoted` and `isDownvoted` fields based on the user's vote
    $comments->each(function ($comment) use ($userId) {
        $userVote = $comment->votes->firstWhere('user_id', $userId); // Filter to get the user's specific vote
        $comment->isUpvoted = $userVote && $userVote->vote_type === 'upvote';
        $comment->isDownvoted = $userVote && $userVote->vote_type === 'downvote';
        unset($comment->votes); // Remove votes relationship as it's no longer needed
    });

    return response()->json([
        'status' => 200,
        'comments' => $comments,
    ], 200);
}




}
