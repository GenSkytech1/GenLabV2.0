<?php
namespace App\Services;

use Google\Client;

class FCMService
{
    public function sendNotification($token, $title, $body, $data = [])
    {
        // Load Firebase service account file
        $client = new Client();
        $client->setAuthConfig(storage_path('app/' . env('FIREBASE_CREDENTIALS')));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

        // Build message
        $message = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],
                "data" => $data
            ]
        ];

        // Send to FCM v1 endpoint
        $url = "https://fcm.googleapis.com/v1/projects/" . env('FIREBASE_PROJECT_ID') . "/messages:send";

        $response = \Http::withToken($accessToken)
            ->post($url, $message);

        return $response->json();
    }
}
