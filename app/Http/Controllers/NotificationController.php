<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get notifications for a user
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */


     public function getNotifications($userId)
    {
     
        try {
            $notifications = $this->notificationService->getUserNotifications($userId);
          
           
            $unreadCount = $notifications->where('is_read', false)->count();
            return response()->json([
                'status' => 200,
                'notifications' => $notifications,
               
                'unread_count' => $unreadCount,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while retrieving notifications',
                'message' => $e->getMessage(),
              
            ], 500);
        }
    }

    public function markNotificationRead($notiId)
    {
        try {
            $notifications = $this->notificationService->markNotificationAsRead($notiId);
         
            return response()->json([
                'status' => 200,
                'message' => 'Notification Readed',
               
               
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while retrieving notifications',
                'message' => $e->getMessage(),
              
            ], 500);
        }
    }
}
