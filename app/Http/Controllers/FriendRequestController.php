<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FriendRequest;
use App\Models\Notification;
use App\Models\Friendship;
use App\Services\FirebaseNotificationService;
use App\Services\NotificationService;
use App\Models\User;
class FriendRequestController extends Controller
{
    protected $pushNotificationService;
    protected $notificationService;
    public function __construct(FirebaseNotificationService $pushNotificationService,  NotificationService $notificationService)
    {
        $this->pushNotificationService = $pushNotificationService;
        $this->notificationService = $notificationService;
    }



    public function sendFriendRequest(Request $request)
    {
        try {
          
            $sender_id = $request->sender_id; // Sender ID
            $receiver_id = $request->receiver_id; // Receiver ID
    
            // Check if users exist
            $sender = User::findOrFail($sender_id);
            $receiver = User::findOrFail($receiver_id);
    
            // Check if a request already exists
            $existingRequest = FriendRequest::where('sender_id', $sender_id)
                                            ->where('receiver_id', $receiver_id)
                                            ->first();
    
            if ($existingRequest) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Friend request already sent'
                ], 400);
            }
    
            // Create friend request
            $friendRequest = new FriendRequest();
            $friendRequest->sender_id = $sender_id;
            $friendRequest->receiver_id = $receiver_id;
            $friendRequest->status = 'pending';
            $friendRequest->save();
            if ($request->receiver_id !== $request->sender_id ) {
                $result = $this->pushNotificationService->sendPushNotification(
                    $receiver->fcm_token,
                    "View Request",
                    $sender->name . ' sent view request to your profile.',
                );
                
                if (!$result['success']) {
                    // Log or handle the error as needed
                }
            }

        Notification::create([
            'receiver_id' => $receiver_id,
            'sender_id' => $sender_id,
            'title' => "View Request",
            'type' => "view_request",
            'body' =>$sender->name . ' sent view request to your profile.',
            'is_read' => false,
            'content_id' => null,
            'friend_req_id' => $friendRequest->id,
        ]);
            return response()->json([
                'status' => 200,
                'message' => 'Friend request sent successfully'
            ], 200);
        } catch (\Exception $e) {
           
    
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while sending the friend request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
 
//aceept friend request
    public function acceptFriendRequest(Request $request)
{
    try {
        $notification_id = $request->notification_id;
        $request_id = $request->request_id; // Friend Request ID
        $receiver_id = $request->receiver_id; // Receiver ID (person accepting)
       
        $receiver = User::findOrFail($receiver_id);
        $requestRow = FriendRequest::findOrFail($request_id);
        $notification_user_id =  $requestRow->sender_id;
        $notification_user = User::findOrFail($notification_user_id);
        $notification_user_fcm =  $notification_user->fcm_token;
        // Find the friend request
        $friendRequest = FriendRequest::where('id', $request_id)
                                      ->where('receiver_id', $receiver_id)
                                      ->first();

        if (!$friendRequest) {
            return response()->json([
                'status' => 404,
                'message' => 'Friend request not found'
            ], 404);
        }

        if ($friendRequest->status !== 'pending') {
            return response()->json([
                'status' => 400,
                'message' => 'Friend request already accepted or declined'
            ], 400);
        }

        // Accept the request
        $friendRequest->status = 'accepted';
        $friendRequest->save();

        // Create friendship record (optional, depending on your app logic)
        // This could be added to a separate 'friendships' table if you track friendships
        Friendship::create([
            'sender_id' => $friendRequest->sender_id,
            'receiver_id' => $friendRequest->receiver_id
        ]);
        if ($request->receiver_id !== $request->sender_id ) {
            $result = $this->pushNotificationService->sendPushNotification(
                $notification_user_fcm,
                "View Your Friend Profile",
                 'You can now view ' . $receiver->name .' profile.',
            );
            
            if (!$result['success']) {
                // Log or handle the error as needed
            }
        }
        
        $data = [
            'title' => 'View Your Friend Now',
            'body' => 'You can now view ' . $notification_user->name .' profile.',
            'is_read' => 1,
            'type' => 'view_accepted'
        ];
        
        $updatedNotification = $this->notificationService->updateNotification($notification_id, $data);
        
        
        Notification::create([
            'sender_id' => $request->receiver_id,
            'receiver_id' => $notification_user->id,
            'title' => "View Your Friend Now",
            'type' => "view_accepted",
            'body' =>'You can now view ' . $receiver->name .' profile.',
            'is_read' => false,
            'content_id' =>null,
            'friend_req_id' => null,
        ]);
        return response()->json([
            'status' => 200,
            'message' => 'Friend request accepted successfully'
        ], 200);

    } catch (\Exception $e) {
      

        return response()->json([
            'status' => 500,
            'message' => 'An error occurred while accepting the friend request',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function getFriendshipListByUserId(Request $request)
{
    try {
        $user_id = $request->user_id; // The ID of the user whose friendships are being fetched

        // Fetch friendships where the user is either the sender or the receiver
        $friendships = Friendship::where('sender_id', $user_id)
                                ->orWhere('receiver_id', $user_id)
                                ->with(['sender', 'receiver']) // Assuming you have sender and receiver relations set up
                                ->get();

        // Check if any friendships are found
        if ($friendships->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No friendships found for this user',
            ], 404);
        }

        // Format the response to include user details of both sender and receiver
        $friendList = $friendships->map(function ($friendship) use ($user_id) {
            // Check if the user is the sender or receiver and get the other user
            $friend = $friendship->sender_id == $user_id ? $friendship->receiver : $friendship->sender;
            return [
                'friendship_id' => $friendship->id,
                'friend_id' => $friend->id,
                'friend_name' => $friend->name,
                'friend_email' => $friend->email,
                'friend_profile_image' => $friend->profile_photo_path, // Adjust the field name accordingly
                'created_at' => $friendship->created_at,
            ];
        });

        return response()->json([
            'status' => 200,
            'message' => 'Friendship list retrieved successfully',
            'data' => $friendList,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'An error occurred while fetching the friendship list',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function findFriends(Request $request)
{
    try {
        $searchTerm = $request->input('search_term'); // Input to search by email, mobile, or name
        $user_id = $request->input('user_id'); // The requesting user’s ID

        // Validate inputs
        $request->validate([
            'search_term' => 'required|string',
            'user_id' => 'required|exists:users,id'
        ]);

        // Find users by email, mobile, or name
        $users = User::where(function ($query) use ($searchTerm) {
            $query->where('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('mobile', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('name', 'LIKE', "%{$searchTerm}%");
        })->get();

        // Map through each user and check if they are already friends with the requesting user
        $result = $users->map(function ($user) use ($user_id) {
            // Check if friendship exists
            $is_friend = Friendship::where(function ($query) use ($user, $user_id) {
                $query->where('sender_id', $user_id)
                      ->where('receiver_id', $user->id);
            })->orWhere(function ($query) use ($user, $user_id) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', $user_id);
            })->exists();

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'image' => $user->profile_photo_path,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'is_friend' => $is_friend
            ];
        });

        return response()->json([
            'status' => 200,
            'data' => $result,
            'message' => 'Friends found successfully'
        ], 200);
    } catch (\Exception $e) {
       

        return response()->json([
            'status' => 500,
            'message' => 'An error occurred while finding friends',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function checkFriendStatus(Request $request)
{
    try {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'other_user_id' => 'required|exists:users,id',
        ]);

        $userId = (int) $request->input('user_id');
        $otherId = (int) $request->input('other_user_id');

        // Check if a friendship record exists (either direction)
        $friendship = Friendship::where(function ($q) use ($userId, $otherId) {
            $q->where('sender_id', $userId)->where('receiver_id', $otherId);
        })->orWhere(function ($q) use ($userId, $otherId) {
            $q->where('sender_id', $otherId)->where('receiver_id', $userId);
        })->first();

        $isFriend = (bool) $friendship;

        // Check for any friend request between the two users (most recent)
        $friendRequest = FriendRequest::where(function ($q) use ($userId, $otherId) {
            $q->where('sender_id', $userId)->where('receiver_id', $otherId);
        })->orWhere(function ($q) use ($userId, $otherId) {
            $q->where('sender_id', $otherId)->where('receiver_id', $userId);
        })->orderBy('created_at', 'desc')->first();

        $requestStatus = 'Add Friend';
        $pendingDirection = null;
        $friendRequestId = null;

        if ($friendRequest) {
            $friendRequestId = $friendRequest->id;
            if ($friendRequest->status === 'pending') {
                $requestStatus = 'pending';
                $pendingDirection = $friendRequest->sender_id == $userId ? 'sent' : 'received';
            } elseif ($friendRequest->status === 'accepted') {
                $requestStatus = 'accepted';
            } elseif ($friendRequest->status === 'declined') {
                $requestStatus = 'declined';
            }
        } elseif ($isFriend) {
            $requestStatus = 'accepted';
        }

        return response()->json([
            'status' => 200,
            'data' => [
                'is_friend' => $isFriend,
                'friendship_id' => $friendship->id ?? null,
                'request_status' => $requestStatus,
                'pending_direction' => $pendingDirection,
                'friend_request_id' => $friendRequestId,
            ],
            'message' => 'Friend status retrieved'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'An error occurred while checking friend status',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function recommendFriends(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);

    try {
        $userId = (int) $request->input('user_id');

        // 1) Current friends of the user
        $friends = Friendship::query()
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->get()
            ->map(function ($f) use ($userId) {
                return $f->sender_id == $userId ? $f->receiver_id : $f->sender_id;
            })
            ->unique()
            ->values();

        $friendIds = $friends->all();

        // 2) Edges touching any of my friends
        $fofEdges = Friendship::query()
            ->where(function ($q) use ($friendIds) {
                $q->whereIn('sender_id', $friendIds)
                  ->orWhereIn('receiver_id', $friendIds);
            })
            ->get();

        // 3) Candidate IDs from friends-of-friends
        $candidates = $fofEdges->map(function ($f) {
                return [$f->sender_id, $f->receiver_id];
            })
            ->flatten()
            ->reject(function ($id) use ($userId, $friendIds) {
                return $id == $userId || in_array($id, $friendIds, true);
            });

        $ranked = $candidates->countBy()->sortDesc();

        // Top 15 candidate user IDs by mutual-count
        $candidateIds = $ranked->keys()->take(15)->values();

        // 4) Fallback: if no recommendation found, return all users except him and existing friends
        if ($candidateIds->isEmpty()) {
            $fallbackUsers = User::query()
                ->where('id', '!=', $userId)
                ->whereNotIn('id', $friendIds)
                ->latest()
                ->take(15)
                ->get();

            if ($fallbackUsers->isEmpty()) {
                return response()->json([
                    'status'  => 200,
                    'data'    => [],
                    'message' => 'No users found',
                ], 200);
            }

            $fallbackIds = $fallbackUsers->pluck('id');

            $pendingRequests = FriendRequest::query()
                ->where('status', 'pending')
                ->where(function ($q) use ($userId, $fallbackIds) {
                    $q->where(function ($q2) use ($userId, $fallbackIds) {
                        $q2->where('sender_id', $userId)
                           ->whereIn('receiver_id', $fallbackIds);
                    })->orWhere(function ($q2) use ($userId, $fallbackIds) {
                        $q2->whereIn('sender_id', $fallbackIds)
                           ->where('receiver_id', $userId);
                    });
                })
                ->get();

            $isRequestedMap = [];
            foreach ($pendingRequests as $r) {
                $otherId = $r->sender_id == $userId ? $r->receiver_id : $r->sender_id;
                $isRequestedMap[$otherId] = true;
            }

            $result = $fallbackUsers->map(function (User $u) use ($isRequestedMap) {
                return [
                    'user_id'      => $u->id,
                    'name'         => $u->name,
                    'image'        => $u->profile_photo_path,
                    'email'        => $u->email,
                    'mobile'       => $u->mobile,
                    'is_friend'    => false,
                    'is_requested' => (bool) ($isRequestedMap[$u->id] ?? false),
                ];
            });

            return response()->json([
                'status'  => 200,
                'data'    => $result,
                'message' => 'Fallback user list found successfully',
            ], 200);
        }

        // 5) Pending friend requests with recommended candidates
        $pendingRequests = FriendRequest::query()
            ->where('status', 'pending')
            ->where(function ($q) use ($userId, $candidateIds) {
                $q->where(function ($q2) use ($userId, $candidateIds) {
                    $q2->where('sender_id', $userId)
                       ->whereIn('receiver_id', $candidateIds);
                })->orWhere(function ($q2) use ($userId, $candidateIds) {
                    $q2->whereIn('sender_id', $candidateIds)
                       ->where('receiver_id', $userId);
                });
            })
            ->get();

        $isRequestedMap = [];
        foreach ($pendingRequests as $r) {
            $otherId = $r->sender_id == $userId ? $r->receiver_id : $r->sender_id;
            $isRequestedMap[$otherId] = true;
        }

        // 6) Pull user profiles in ranking order
        $users = User::query()
            ->whereIn('id', $candidateIds)
            ->get()
            ->sortByDesc(fn ($u) => $ranked->get($u->id, 0))
            ->values();

        // 7) Build response
        $result = $users->map(function (User $u) use ($isRequestedMap) {
            return [
                'user_id'      => $u->id,
                'name'         => $u->name,
                'image'        => $u->profile_photo_path,
                'email'        => $u->email,
                'mobile'       => $u->mobile,
                'is_friend'    => false,
                'is_requested' => (bool) ($isRequestedMap[$u->id] ?? false),
            ];
        });

        return response()->json([
            'status'  => 200,
            'data'    => $result,
            'message' => 'Recommended friends found successfully',
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'status'  => 500,
            'message' => 'An error occurred while recommending friends',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
    
}
