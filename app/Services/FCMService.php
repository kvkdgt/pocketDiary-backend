<?php

namespace App\Services;

use Google\Client as GoogleClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class FCMService
{
    protected $client;
    protected $googleClient;

    public function __construct()
    {
        $this->client = new GuzzleClient();
        $this->googleClient = new GoogleClient();
        $this->googleClient->setAuthConfig(storage_path('app/google-service-account.json'));
        $this->googleClient->addScope('https://www.googleapis.com/auth/firebase.messaging');
    }

    public function sendNotification($title, $body, $target)
    {
        $url = 'https://fcm.googleapis.com/v1/projects/karmtrack-8e020/messages:send';
        
        $notification = [
            'message' => [
                'token' => $target, // or 'topic' => 'topicName', 'condition' => "'foo' in topics && 'bar' in topics"
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                // 'data' => [
                //     // Custom data payload
                // ],
            ],
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type' => 'application/json',
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $notification,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseBody = json_decode($response->getBody()->getContents(), true);
                return response()->json([
                    'error' => $responseBody['error']['message'] ?? 'An error occurred',
                    'details' => $responseBody,
                ], $response->getStatusCode());
            }
            return response()->json(['error' => 'An unknown error occurred'], 500);
        }
    }
    public function sendNotificationWithImage($title, $body, $target, $image = null)
    {
        $url = 'https://fcm.googleapis.com/v1/projects/karmtrack-8e020/messages:send';
        
        // Building the notification payload
        $notification = [
            'message' => [
                'token' => $target, // or 'topic' => 'topicName', 'condition' => "'foo' in topics && 'bar' in topics"
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'image' => $image, // Include image URL here
                ],
                // Optionally, include custom data
                // 'data' => [
                //     // Custom data payload
                // ],
            ],
        ];
    
        // Headers for authorization and content type
        $headers = [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type' => 'application/json',
        ];
    
        try {
            // Sending the notification via a POST request
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $notification,
            ]);
    
            // Return the response as an array
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Handle request errors and provide details
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseBody = json_decode($response->getBody()->getContents(), true);
                return response()->json([
                    'error' => $responseBody['error']['message'] ?? 'An error occurred',
                    'details' => $responseBody,
                ], $response->getStatusCode());
            }
            return response()->json(['error' => 'An unknown error occurred'], 500);
        }
    }
    
    protected function getAccessToken()
    {
        $this->googleClient->fetchAccessTokenWithAssertion();
        $token = $this->googleClient->getAccessToken();
        return $token['access_token'];
    }
}
