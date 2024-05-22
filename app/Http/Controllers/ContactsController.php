<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contacts;
use App\Models\User;


class ContactsController extends Controller
{
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
                'profile_picture'=>$userData->profile_picture
            ];
        }
        return response()->json(['contacts' => $responseData]);
    }

    public function searchByPhoneNumber(Request $request, $phoneNumber)
    {
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
                'profile_picture'=>$user->profile_picture
            ];
            return response()->json($responseData);
        } else {

            return response()->json(['error' => 'No users found'], 200);
        }
    }

    public function sendRequest(Request $request)
    {
        $senderId = $request->user()->id;

        // Create a new contact record
        $contact = Contacts::create([
            'sender_id' => $senderId,
            'receiver_id' => $request->receiver_id,
            'status' => 'pending', // Default status
        ]);

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

        // Check if the contact exists
        if (!$contact) {
            return response()->json([
                'message' => 'Contact not found',
            ], 200);
        }

        // Delete the contact
        $contact->delete();

        // Return a JSON response indicating success
        return response()->json([
            'message' => 'Contact removed successfully',
        ]);
    }

    public function acceptContact($id)
    {
        $contact = Contacts::find($id);

        // Check if the contact exists
        if (!$contact) {
            return response()->json([
                'message' => 'Contact not found',
            ], 200);
        }

        // Update the status to "accepted"
        $contact->status = 'accepted';
        $contact->save();

        // Return a JSON response indicating success
        return response()->json([
            'message' => 'Request accepted',
        ]);
    }
}
