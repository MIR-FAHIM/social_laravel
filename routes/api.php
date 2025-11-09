<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserSignupController;
use App\Http\Controllers\ContentImagesController;
use App\Http\Controllers\AddFlagOnContentController;
use App\Http\Controllers\UserLoginController;
use App\Http\Controllers\LifeEventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileImageController;
use App\Http\Controllers\IconController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HarmonyOfLifeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ContentLikeController;
use App\Http\Controllers\ContentFlagController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\BadgesController;
use App\Http\Controllers\AnswersSkillController;
use App\Http\Controllers\QuestionsSkillController;
// --------------- User Authentication Module ---------------

// User signup route
Route::post('/sign_up', [UserSignupController::class, 'signUp']);

// User login route
Route::post('/login', [UserLoginController::class, 'login']);

// User profile route
Route::get('/profile/{id}', [UserController::class, 'getProfile']);

// --------------- Friendship Module ---------------

// Send friend request
Route::post('/friend-request/send', [FriendRequestController::class, 'sendFriendRequest']);

// Recommend friends based on mutual connections
Route::get('/recommendFriends', [FriendRequestController::class, 'recommendFriends']);

// Accept friend request
Route::post('/friend-request/accept', [FriendRequestController::class, 'acceptFriendRequest']);

// Find friends based on search term (name, email, or mobile)
Route::post('/find-friends', [FriendRequestController::class, 'findFriends']);

// Get the list of all friends for a specific user
Route::get('/getFriendShipList', [FriendRequestController::class, 'getFriendshipListByUserId']);

// --------------- Content Module ---------------

// Upload images for content
Route::post('/uploadContentImages', [ContentImagesController::class, 'uploadContentImages']);

// Post content (may need authentication)
Route::post('/post_content', [ContentController::class, 'postContent']);
Route::post('/update-content/{id}', [ContentController::class, 'updateContent']);
Route::post('/giveFireOnContent', [ContentController::class, 'giveFireOnContent']);
Route::post('/updateAuthorWrittingStatus', [ContentController::class, 'updateAuthorWritingStatus']);

// Get all content (pagination might be needed)
Route::get('/content', [ContentController::class, 'getAllContent']);
Route::get('/getAuthorWritingContent', [ContentController::class, 'getAuthorWritingContent']);
Route::get('/getConentLikesUser', [ContentLikeController::class, 'getContentLikes']);



// Get content posted by a specific user
Route::get('/getContentByUser', [ContentController::class, 'getContentByUser']);

// Get content details
Route::get('/getContentDetails', [ContentController::class, 'getContentDetails']);

// Like content
Route::post('/content/addLike', [ContentLikeController::class, 'like']);

// Remove like from content
Route::post('/content/removeLike', [ContentLikeController::class, 'unlike']);

// Add a comment to content
Route::post('/addComment', [CommentController::class, 'store']);
Route::post('/commentOnVote', [CommentController::class, 'voteOnComment']);
Route::get('/getCommentByVote', [CommentController::class, 'getCommentsByUpvotes']);

// Get all comments for a specific content
Route::get('/getcomments/{contentId}', [CommentController::class, 'getCommentsByContent']);

// Add content to a collection
Route::post('/makeCollection', [ContentController::class, 'collectContent']);

// Get the user's content collection
Route::get('/getCollection', [ContentController::class, 'getCollectionByUser']);

// --------------- Notification Module ---------------

// Send push notification to a specific user
Route::get('/send_push_notification/{user_id}', [PushNotificationController::class, 'sendPushNotification']);

// Get all notifications for a user
Route::get('/getNotifications/{user_id}', [NotificationController::class, 'getNotifications']);

// Mark a notification as read
Route::get('/markNotificationRead/{notiId}', [NotificationController::class, 'markNotificationRead']);

// --------------- Harmony of Life Module ---------------

// Add harmony of life entry
Route::post('/addharmony', [HarmonyOfLifeController::class, 'store']);

// Get harmony of life data for a specific user
Route::get('/getharmony/{user_id}', [HarmonyOfLifeController::class, 'show']);

// --------------- Icons Module ---------------

// Add an icon
Route::post('/add_icons', [IconController::class, 'addIcon']);

// Get icons by type
Route::get('/get_icons/{type}', [IconController::class, 'getIconsByType']);

// Update an icon
Route::put('/icons/{id}', [IconController::class, 'updateIcon']);

// Delete an icon
Route::delete('/icons/{id}', [IconController::class, 'deleteIcon']);

// --------------- Notes Module ---------------

Route::prefix('notes')->group(function () {
    // Create a new note
    Route::post('add/', [NoteController::class, 'store']);
    
    // Get all notes
    Route::get('get/', [NoteController::class, 'index']);
    
    // Get a single note by ID
    Route::get('get/{id}', [NoteController::class, 'show']);
    
    // Update a note
    Route::post('update/{id}', [NoteController::class, 'update']);
    
    // Delete a note
    Route::get('delete/{id}', [NoteController::class, 'destroy']);
});

// --------------- Life Events Module ---------------

// Add a life event
Route::post('/addevents', [LifeEventController::class, 'addEvent']);

// Get all life events for a user
Route::get('/getEvents/{user_id}', [LifeEventController::class, 'getEventsByUser']);

// Update a life event
Route::put('/updateEvents/{id}', [LifeEventController::class, 'updateEvent']);

// Delete a life event
Route::delete('/deleteEvents/{id}', [LifeEventController::class, 'deleteEvent']);

// --------------- Example Route (for completeness) ---------------

// Example route to get user details (adjust as needed)
Route::get('/user', function (Request $request) {
    return $request->user();
});


// profile image
Route::post('/upload-profile-images', [ProfileImageController::class, 'uploadProfileImages']);
Route::post('/set-default-profile-images', [ProfileImageController::class, 'setDefaultProfileImage']);
Route::post('/upload-single-profile-images', [UserController::class, 'uploadImage']);
Route::get('/profile-images', [ProfileImageController::class, 'getProfileImages']);
Route::post('/update-image-order', [ProfileImageController::class, 'updateImageOrder']);
Route::delete('/delete-profile-image', [ProfileImageController::class, 'deleteProfileImage']);




Route::prefix('badges')->group(function () {
    Route::get('/get-badges', [BadgesController::class, 'index']);
    Route::post('/add-badge', [BadgesController::class, 'store']);
    Route::post('/by-user', [BadgesController::class, 'listByUser']);
    Route::get('/show-badge/{id}', [BadgesController::class, 'show']);
    Route::post('/update/{id}', [BadgesController::class, 'update']); // or use PUT/PATCH
    Route::delete('delete/{id}', [BadgesController::class, 'destroy']);
});




Route::post('/content-flags/add', [ContentFlagController::class, 'addContentFlag']);
Route::post('/add-flag-on-content', [AddFlagOnContentController::class, 'addFlagOnContent']);
Route::get('/content-flags/get', [ContentFlagController::class, 'getContentFlag']);



Route::prefix('skill-connect')->group(function () {
    Route::get('/fetch-questions', [QuestionsSkillController::class, 'index']);     // Get all questions
    Route::get('/get-questions/{id}', [QuestionsSkillController::class, 'show']); // Get one question
    Route::post('/store-questions', [QuestionsSkillController::class, 'store']);    // Add a question
    Route::post('/update-questions/{id}', [QuestionsSkillController::class, 'update']); // Update
    Route::get('/delete-questions/{id}', [QuestionsSkillController::class, 'destroy']); // Delete
});




Route::prefix('skill-connect')->group(function () {
    Route::post('/add-answers', [AnswersSkillController::class, 'store']);      // Add answer
    Route::post('/update-answers/{id}', [AnswersSkillController::class, 'update']); // Update answer
    Route::get('/delete-answers/{id}', [AnswersSkillController::class, 'destroy']); // Delete answer
});