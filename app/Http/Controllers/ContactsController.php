<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Contacts;
use App\Models\User;
use App\Services\FCMService;


class ContactsController extends Controller
{
    protected $fcmService;
    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    public function getContacts(Request $request)
    {
        $userId = $request->user()->id;
        $contacts = Contacts::where(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                ->orWhere('receiver_id', $userId);
        })->where('status', 'accepted')->get();
        $contacts->load(['sender', 'receiver']);
        $responseData = [];

        foreach ($contacts as $contact) {
            $userData = $userId == $contact->sender_id ? $contact->receiver : $contact->sender;
            $responseData[] = [
                'id' => $contact->id,
                'user_id' => $userData->id,
                'name' => $userData->full_name,
                'profile_picture' => $userData->profile_picture
            ];
        }
        $pendingContacts = Contacts::where('receiver_id', $userId)
            ->where('status', 'pending')
            ->get()->count();
        return response()->json(['contacts' => $responseData, 'pendingRequestsCount' => $pendingContacts]);
    }

    public function searchByPhoneNumber(Request $request, $phoneNumber)
    {
        $currentUser = $request->user(); // Assuming the user is authenticated

        // Check if the user is searching for their own phone number
        if ($currentUser->phone_number == $phoneNumber) {
            return response()->json(['error' => 'You cannot search for your own phone number']);
        }
        $user = User::where('phone_number', $phoneNumber)->first();
        $responseData = [];
        $status = '';

        if ($user) {
            $currentUser = $request->user(); // Assuming the user is authenticated
            $loggedinUser = $currentUser->id;
            $contacts = Contacts::where(function ($query) use ($loggedinUser, $user) {
                $query->where('sender_id', $loggedinUser)
                    ->where('receiver_id', $user->id);
            })->orWhere(function ($query) use ($loggedinUser, $user) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', $loggedinUser);
            })->first();
            if ($contacts) {

                if ($contacts->status != 'accepted') {
                    if ($contacts->sender_id == $loggedinUser) {
                        $status = 'request sent';
                    } else if ($contacts->receiver_id == $loggedinUser) {
                        $status = 'accept request';
                    }
                } else if ($contacts->status == 'accepted') {
                    $status = 'accpeted';
                }
            }
            $responseData[] = [
                'user_id' => $user->id,
                'full_name' => $user->full_name,
                'status' => $status,
                'profile_picture' => $user->profile_picture
            ];
            return response()->json($responseData);
        } else {

            return response()->json(['error' => 'No users found'], 200);
        }
    }

    public function sendRequest(Request $request)
    {
        $senderId = $request->user()->id;
        $receiverId = $request->input('receiver_id');

        // Create a new contact record
        $contact = Contacts::create([
            'sender_id' => $senderId,
            'receiver_id' => $request->receiver_id,
            'status' => 'pending', // Default status
        ]);

        $receiver = User::find($receiverId);
        $senderName = $request->user()->full_name;
        if ($receiver && $receiver->fcm_token) {
            // Send notification using FCM service
            $title = 'New Contact Request';
            $body = $senderName . ' sent you a new contact request.';
            $target = $receiver->fcm_token; // Assuming fcm_token is stored in the User model
            $response = $this->fcmService->sendNotification($title, $body, $target);
            // Send notification via FCMService

        }

        return response()->json([
            'message' => 'Request sended successfully',
        ], 200);
    }

    public function getPendingRequest(Request $request)
    {
        $userId = $request->user()->id;

        $pendingContacts = Contacts::where('receiver_id', $userId)
            ->where('status', 'pending')
            ->get();
        $pendingContacts->load(['sender', 'receiver']);
        $responseData = [];

        foreach ($pendingContacts as $contact) {
            $userData = $contact->sender;
            $responseData[] = [
                'id' => $contact->id,
                'name' => $userData->full_name,
                'profile_picture' => $userData->profile_picture,
            ];
        }
        return response()->json([
            'pending_requests' => $responseData,
        ]);
    }

    public function removeContact($userId)
    {
        $contact = Contacts::find($userId);
        $authId = Auth::id();
        // Check if the contact exists
        if (!$contact) {
            return response()->json([
                'message' => 'Contact not found',
            ], 200);
        }

        // Delete the contact
        $contact->delete();
        $pendingContacts = Contacts::where('receiver_id', $authId)
            ->where('status', 'pending')
            ->get()->count();
        // Return a JSON response indicating success
        return response()->json([
            'message' => 'Contact removed successfully',
            'pendingRequestCount' => $pendingContacts
        ]);
    }

    public function acceptContact($id)
    {
        $contact = Contacts::find($id);
        $authId = Auth::id();
        // Check if the contact exists
        if (!$contact) {
            return response()->json([
                'message' => 'Contact not found',
            ], 200);
        }

        // Update the status to "accepted"
        $contact->status = 'accepted';
        $contact->save();

        $senderName = $contact->receiver->full_name;

        if ($contact->sender->fcm_token) {
            // Send notification using FCM service
            $title = 'Contact Request Accepted!';
            $body = $senderName . ' accepted your contact request.';
            $target = $contact->sender->fcm_token; // Assuming fcm_token is stored in the User model
            $response = $this->fcmService->sendNotification($title, $body, $target);
            // Send notification via FCMService

        }

        $pendingContacts = Contacts::where('receiver_id', $authId)
            ->where('status', 'pending')
            ->get()->count();
        // Return a JSON response indicating success
        return response()->json([
            'pendingRequestCount' => $pendingContacts,
            'message' => 'Request accepted',
        ]);
    }

    public function recommendedContacts(Request $request)
    {
        $request->validate([
            'contacts' => 'required|array'
        ]);
    
        // Retrieve contacts and the authenticated user ID
        $contacts = $request->contacts;
        $authUserId = auth()->id();
    
        // Get users with matching phone numbers from the contacts array
        $users = User::whereIn('phone_number', $contacts)
                     ->where('id', '!=', $authUserId) // Exclude the authenticated user's data
                     ->get(['id', 'full_name', 'profile_picture', 'phone_number']);

                     $ignoredContacts = Contacts::where(function ($query) use ($authUserId) {
                        $query->where('sender_id', $authUserId)
                              ->orWhere('receiver_id', $authUserId);
                    })
                    
                    ->get(['sender_id', 'receiver_id']);
            
                // Filter out users based on ignored contacts
                $filteredUsers = $users->reject(function ($user) use ($authUserId, $ignoredContacts) {
                    foreach ($ignoredContacts as $contact) {
                        if (($contact->sender_id == $user->id && $contact->receiver_id == $authUserId) ||
                            ($contact->receiver_id == $user->id && $contact->sender_id == $authUserId)) {
                            return true; // Exclude this user
                        }
                    }
                    return false; // Include this user
                });
            
                // Map the filtered users to the desired format
                $recommendedContacts = $filteredUsers->map(function ($user) {
                    return [
                        'user_id' => $user->id,
                        'full_name' => $user->full_name,
                        'profile_picture' => $user->profile_picture,
                        'phone_number' => $user->phone_number,
                    ];
                });
            
                // Return the filtered data as a JSON response
                return response()->json(['recommended_contacts' => $recommendedContacts]);
    }
}
