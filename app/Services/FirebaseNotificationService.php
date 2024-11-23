<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $firebase = (new Factory)
            ->withServiceAccount(base_path('config/firebase_credentials.json'));

        $this->messaging = $firebase->createMessaging();
    }

    public function sendPushNotification($token, $title, $body)
    {
        try {
            $message = CloudMessage::fromArray([
                'token' =>  $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ]);

            // Attempt to send the notification
            $this->messaging->send($message);

            return ['success' => true, 'message' => 'Push notification sent successfully'];

        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            // Handle Firebase messaging-specific errors
            return ['success' => false, 'error' => 'Firebase Messaging error', 'message' => $e->getMessage()];

        } catch (\Exception $e) {
            // Handle general errors
            return ['success' => false, 'error' => 'An unexpected error occurred', 'message' => $e->getMessage()];
        }
    }
}
