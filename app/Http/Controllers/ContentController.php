<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ContentReaction;
use Illuminate\Http\Request;
use App\Models\Content;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;
use App\Models\Friendship;
use App\Models\Collection;
use App\Models\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{



    public function postContent(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'text_content' => 'nullable|string',
                'text_title' => 'nullable|string',
                'text_url' => 'nullable|string',
                'isGeneral' => 'nullable|boolean',
                'isDiscussion' => 'nullable|boolean',
                'isNews' => 'nullable|boolean',
                'isEducation' => 'nullable|boolean',
                'single_image' => 'nullable|url',
                'isFired' => 'nullable|boolean',
                'isBurnt' => 'nullable|boolean',
                'score' => 'nullable|integer',
                'view_count' => 'nullable|integer',
                'like_count' => 'nullable|integer',
                'is_authenticated' => 'nullable|boolean',
                'is_debate' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $user = User::where('id', $request->user_id)->first();

            $content = Content::create([
                'user_id' => $user->id, // Use authenticated user's ID
                ...$request->all(), // Safely pass other request fields
            ]);
            return response()->json([
                'status' => 200,
                'message' => 'Content posted successfully',
                'content' => $content
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Model not found',
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllContent(Request $request)
    {
        try {
            $user_id = $request->input('user_id'); // Ensure user_id is passed in the request
            $perPage = $request->input('per_page', 15);

            // Get all content with user and check like status for the current user
            $content = Content::with(['user', 'likes'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Add 'like_status' to each content item
            $content->getCollection()->transform(function ($item) use ($user_id) {
                // Check if the current user has liked this content
                $item->like_status = $item->likes->where('user_id', $user_id)->isNotEmpty();

                                // Get authentication reactions for this content
                $authentications = ContentReaction::where('content_id', $item->id)
                    ->where('reaction_type', 'authenticate')
                    ->get();

                // Calculate total authenticated and average score
                $totalAuthenticated = $authentications->count();
                $averageScore = $totalAuthenticated > 0
                    ? round($authentications->avg('score'), 2)
                    : 0.0;

                // Add authenticate object to content
                $item->authenticate = [
                    'total_authenticated' => $totalAuthenticated,
                    'average_score' => $averageScore
                ];
                return $item;
            });

            return response()->json(
                [
                    "status" => 200,
                    "data" => $content,
                ],
                200
            );
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAuthorWritingContent(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
            $perPage = $request->input('per_page', 15);

            // Ensure user_id is provided
            if (!$user_id) {
                return response()->json(['error' => 'user_id is required'], 400);
            }

            // Retrieve content where is_author_writing is 1 for the specified user
            $content = Content::with(['user', 'likes'])
                ->where('user_id', $user_id)
                ->where('is_author_writting', 1)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            $content->getCollection()->transform(function ($item) use ($user_id) {
                // Check if the current user has liked this content
                $item->like_status = $item->likes->where('user_id', $user_id)->isNotEmpty();

              
                // Get authentication reactions for this content
                $authentications = ContentReaction::where('content_id', $item->id)
                    ->where('reaction_type', 'authenticate')
                    ->get();

                // Calculate total authenticated and average score
                $totalAuthenticated = $authentications->count();
                $averageScore = $totalAuthenticated > 0
                    ? round($authentications->avg('score'), 2)
                    : 0;

                // Add authenticate object to content
                $item->authenticate = [
                    'total_authenticated' => $totalAuthenticated,
                    'average_score' => $averageScore
                ];
                return $item;
            });
            return response()->json([
                "status" => 200,
                "data" => $content,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function collectContent(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
            $content_id = $request->input('content_id');

            // Ensure the user_id and content_id are provided
            if (!$user_id || !$content_id) {
                return response()->json(['error' => 'user_id and content_id are required'], 400);
            }

            // Check if the content is already collected by the user
            $collection = Collection::where('user_id', $user_id)
                ->where('content_id', $content_id)
                ->first();

            if ($collection) {
                return response()->json(['message' => 'Content already collected'], 200);
            }

            // Add the content to the user's collection
            $newCollection = Collection::create([
                'user_id' => $user_id,
                'content_id' => $content_id
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Content collected successfully',
                'data' => $newCollection
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updateAuthorWritingStatus(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
            $content_id = $request->input('content_id');
            $is_author_writting = $request->input('is_author_writting');

            // Ensure user_id, content_id, and is_author_writing are provided
            if (!$user_id || !$content_id || is_null($is_author_writting)) {
                return response()->json(['message' => 'user_id, content_id, and is_author_writing are required'], 400);
            }

            // Fetch the content and verify if it exists
            $content = Content::find($content_id);
            if (!$content) {
                return response()->json(['message' => 'Content not found'], 404);
            }

            // Check if the user has the necessary permission and is the author of the content
            $user = User::find($user_id);
            if (!$user || $user->isAuthor != 1) {
                return response()->json(['message' => 'User does not have author permissions'], 403);
            }

            if ($content->user_id != $user_id) {
                return response()->json(['message' => 'Only the content author can update this field'], 403);
            }

            // Update the is_author_writing field
            $content->is_author_writing = $is_author_writting;
            $content->save();

            return response()->json([
                'status' => 200,
                'message' => 'is_author_writing updated successfully',
                'data' => $content
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCollectionByUser(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
            $perPage = $request->input('per_page', 15); // Default to 15 items per page if not provided

            if (!$user_id) {
                return response()->json(['error' => 'user_id is required'], 400);
            }

            // Get the user's collection with content and likes, paginated
            $collection = Collection::with(['content.user', 'content.likes'])
                ->where('user_id', $user_id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Add 'like_status' to each content item
            $collection->getCollection()->transform(function ($item) use ($user_id) {
                // Check if the current user has liked this content
                $item->content->like_status = $item->content->likes->where('user_id', $user_id)->isNotEmpty();
                return $item;
            });

            // Prepare the response with pagination details
            return response()->json([
                'status' => 200,
                'data' => $collection->items(),  // The actual collection data
                'pagination' => [
                    'current_page' => $collection->currentPage(),
                    'per_page' => $collection->perPage(),
                    'total' => $collection->total(),
                    'last_page' => $collection->lastPage(),
                    'from' => $collection->firstItem(),
                    'to' => $collection->lastItem(),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getContentByUser(Request $request)
    {
        try {
            $user_id = $request->input('user_id'); // Ensure user_id is passed in the request
            $perPage = $request->input('per_page', 15);

            // Get all content with user and check like status for the current user
            $content = Content::with(['user', 'likes'])
                ->where('user_id', $request->user_id,)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Add 'like_status' to each content item
            $content->getCollection()->transform(function ($item) use ($user_id) {
                // Check if the current user has liked this content
                $item->like_status = $item->likes->where('user_id', $user_id)->isNotEmpty();
                return $item;
            });

            return response()->json(
                [
                    "status" => 200,
                    "data" => $content,
                ],
                200
            );
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function giveFireOnContent(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Fetch user and check if user is fireman
            $user = User::findOrFail($request->user_id);

            if (!$user->is_fireman) {
                return response()->json([
                    'status' => 404,
                    'error' => 'Unauthorized',
                    'message' => 'You do not have the fireman privilege to give fire to content.'
                ], 404);
            }

            // Fetch content
            $content = Content::findOrFail($request->content_id);

            // Update isFired to true
            $content->isFired = true;
            $content->save();

            // Log the reaction in content_reactions table
            $reaction = ContentReaction::create([
                'user_id' => $user->id,
                'content_id' => $content->id,
                'reaction_type' => 'fire',
                'isComment' => false, // Assuming it's a direct content fire
            ]);

            // Send notification to the content user
            Notification::create([
                'receiver_id' => $content->user_id,
                'sender_id' => $request->user_id,
                'title' => $user->name . ' fired your content',
                'type' => 'fired',
                'body' => Str::limit($content->text_content, 20, '...'),
                'is_read' => false,
                'content_id' => $content->id,
                'friend_req_id' => null,
            ]);

            // Fetch content user's friends from Friendship table
            $friends = Friendship::where('receiver_id', $content->user_id)->orWhere('sender_id', $content->user_id)->get();

            // Notify each friend
            foreach ($friends as $friend) {
                // Determine the friend id (the other user in the friendship relation)
                $friendId = ($friend->receiver_id == $content->user_id) ? $friend->sender_id : $friend->receiver_id;

                Notification::create([
                    'receiver_id' => $friendId,
                    'sender_id' => $content->user_id,
                    'title' => 'Friends in need',
                    'type' => 'friends_in_need',
                    'body' => 'Someone fired on your friend content',
                    'is_read' => false,
                    'content_id' => $content->id,
                    'friend_req_id' => null,
                ]);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Content has been fired successfully',
                'reaction' => $reaction
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 404,
                'error' => 'Validation failed',
                'message' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'error' => 'Model not found',
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function getContentDetails(Request $request)
    {
        try {
            // Fetch content by ID
            $content = Content::with('user')->findOrFail($request->content_id);
            $content->like_status = $content->likes->where('user_id', $request->user_id)->isNotEmpty();
            // Return content details
            return response()->json([
                'status' => 200,
                'message' => 'Content details retrieved successfully',
                'content' => $content,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'error' => 'Content not found',
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updateContent(Request $request, $id)
    {
        try {
            // Validate only updatable fields; all are optional (partial update)
            $validator = Validator::make($request->all(), [
                'user_id'         => 'required|exists:users,id', // who is attempting the update
                'text_content'    => 'nullable|string',
                'text_title'      => 'nullable|string|max:255',
                'text_url'        => 'nullable|string|max:1000',
                'isGeneral'       => 'nullable|boolean',
                'isDiscussion'    => 'nullable|boolean',
                'isNews'          => 'nullable|boolean',
                'isEducation'     => 'nullable|boolean',
                'single_image'    => 'nullable|url',
                'isFired'         => 'nullable|boolean',
                'isBurnt'         => 'nullable|boolean',
                'score'           => 'nullable|integer|min:0',
                'view_count'      => 'nullable|integer|min:0',
                'like_count'      => 'nullable|integer|min:0',
                'comment_count'   => 'nullable|integer|min:0',
                'is_authenticated' => 'nullable|boolean',
                'is_debate'       => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Load content or 404
            $content = Content::findOrFail($id);

            // Authorization: only owner can edit (tweak if you have roles)
            $actorId = (int) $request->input('user_id');
            if ((int) $content->user_id !== $actorId) {
                return response()->json([
                    'status'  => 403,
                    'error'   => 'Forbidden',
                    'message' => 'You are not allowed to update this content.',
                ], 403);
            }

            // Whitelist fields that can be updated
            $updatable = [
                'text_content',
                'text_title',
                'text_url',
                'isGeneral',
                'isDiscussion',
                'isNews',
                'isEducation',
                'single_image',
                'isFired',
                'isBurnt',
                'score',
                'view_count',
                'like_count',
                'comment_count',
                'is_authenticated',
                'is_debate',
            ];
            $data = $request->only($updatable);

            // Optional: normalize booleans (Laravel will cast if $casts set on model)
            foreach (['isGeneral', 'isDiscussion', 'isNews', 'isEducation', 'isFired', 'isBurnt', 'is_authenticated', 'is_debate'] as $b) {
                if ($request->has($b)) {
                    $data[$b] = filter_var($request->input($b), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                }
            }

            DB::transaction(function () use ($content, $data) {
                $content->fill($data);
                $content->save();
            });

            // Return the updated record (fresh)
            $content->refresh();

            return response()->json([
                'status'  => 200,
                'message' => 'Content updated successfully',
                'content' => $content,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 422,
                'error'   => 'Validation failed',
                'message' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'error'   => 'Not Found',
                'message' => 'Content not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 500,
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function giveAuthenticity(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'content_id' => 'required|exists:contents,id',
                'score' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Fetch user and check if user is guard
            $user = User::findOrFail($request->user_id);

            if (!$user->is_guard) {
                return response()->json([
                    'status' => 403,
                    'error' => 'Unauthorized',
                    'message' => 'You do not have the guard privilege to authenticate content.'
                ], 403);
            }

            // Fetch content
            $content = Content::findOrFail($request->content_id);

            // Check if this user has already authenticated this content
            $existingReaction = ContentReaction::where('user_id', $user->id)
                ->where('content_id', $content->id)
                ->where('reaction_type', 'authenticate')
                ->first();

            if ($existingReaction) {
                // Update the score if already authenticated
                $existingReaction->update([
                    'score' => $request->input('score'),
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Authentication score updated successfully',
                    'reaction' => $existingReaction
                ], 200);
            }

            // Log the reaction in content_reactions table
            $reaction = ContentReaction::create([
                'user_id' => $user->id,
                'content_id' => $content->id,
                'reaction_type' => 'authenticate',
                'score' => $request->input('score'),
                'isComment' => false,
            ]);

            // Send notification to the content user
            Notification::create([
                'receiver_id' => $content->user_id,
                'sender_id' => $request->user_id,
                'title' => $user->name . ' authenticated your content',
                'type' => 'authenticated',
                'body' => Str::limit($content->text_content, 20, '...'),
                'is_read' => false,
                'content_id' => $content->id,
                'friend_req_id' => null,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Content has been authenticated successfully',
                'reaction' => $reaction
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'error' => 'Validation failed',
                'message' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'error' => 'Model not found',
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
