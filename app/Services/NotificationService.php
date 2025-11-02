<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function storeNotification($userId, $sentTo, $title, $body, $contentId = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'sent_to' => $sentTo,
            'content_id' => $contentId,
            'title' => $title,
            'body' => $body,
        ]);
    }

    public function getUserNotifications($userId)
    {
 
        return Notification::where('receiver_id', $userId)->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function markNotificationAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->is_read = true;
            $notification->save();
            return true;
        }
        return false;
    }

    public function deleteNotification($notificationId)
{
    // Find the notification by ID
    $notification = Notification::find($notificationId);

    if ($notification) {
        // Delete the notification
        $notification->delete();
        return true; // Return true if the deletion was successful
    }
    
    return false; // Return false if the notification was not found
}
public function updateNotification($notificationId, $data)
{
    $notification = Notification::find($notificationId);

    if ($notification) {
        // Update notification fields with the data provided
        if (isset($data['title'])) {
            $notification->title = $data['title'];
        }
        if (isset($data['body'])) {
            $notification->body = $data['body'];
        }
        if (isset($data['is_read'])) {
            $notification->is_read = $data['is_read'];
        }
   
        if (isset($data['type'])) {
            $notification->type = $data['type'];
        }
        
        // Save the updated notification
        $notification->save();

        return $notification; // Return the updated notification
    }

    return null; // Notification not found
}
}
