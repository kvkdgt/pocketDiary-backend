<?php

namespace App\Http\Controllers;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Services\FCMService;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    protected $fcmService;
    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    public function sendNotification(Request $request)
    {
        $title = $request->input('title');
        $body = $request->input('body');
        $target = $request->input('target'); // FCM token or topic

        $response = $this->fcmService->sendNotification($title, $body, $target);

        return response()->json($response);
    }
    public function sendNotificationWithImage(Request $request)
    {
        $title = $request->input('title');
        $body = $request->input('body');
        $target = $request->input('target'); // FCM token or topic
        $image = $request->input('image');
        $response = $this->fcmService->sendNotification($title, $body, $target, $image);

        return response()->json($response);
    }
}
