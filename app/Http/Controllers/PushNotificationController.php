<?php
 
namespace App\Http\Controllers;
use Illuminate\Validation\ValidationException;
use Exception;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\User; 
class PushNotificationController extends Controller
{
    public function sendPushNotification($userId)
    {
 $path = '/home/biswasandbrother/public_html/socialmedia.biswasandbrothers.com/config/firebase_credentials.json';

//  $pathTest = '/home/biswasandbrother/public_html/socialmedia.biswasandbrothers.com/config/test.text';
// file_put_contents($pathTest, 'This is a test file.');
// $content = file_get_contents($pathTest);
//   return response()->json(['message' => $content], 200);
        try {
             $user = User::find($userId);
              
            $firebase = (new Factory)
                ->withServiceAccount($path);
                 
            $messaging = $firebase->createMessaging();
           
            // Example device token (replace with real device token from your app)
            $message = CloudMessage::fromArray([
                'token' => $user->fcm_token,  
                'notification' => [
                    'title' => 'Hello from Firebase',
                    'body' => 'This is a test notification controller.'
                ],
            ]);
           
            // Attempt to send the notification
            $messaging->send($message);
    
            return response()->json(['message' => 'Push notification sent successfully'], 200);
        
        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            // Handle Firebase messaging-specific errors
            return response()->json([
                'error' => 'Firebase Messaging error',
                'message' => $e->getMessage()
            ], 500);
    
        } catch (Exception $e) {
            // Handle general errors
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
}