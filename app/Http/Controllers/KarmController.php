<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Karm;
use App\Models\User;
use App\Models\Contacts;
use App\Models\BrahminsForkarm;
use Carbon\Carbon;
use App\Services\FCMService;


class KarmController extends Controller

{
    protected $fcmService;
    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    public function getKarmData(Request $request)
    {
        $user = Auth::user();
        $filter = $request->filter; // either 'upcoming' or 'previous'
        $userId = Auth::id();
        $query = Karm::with(['createdBy', 'brahminsForKarm' => function($q) use ($user) {
            $q->where('brahmin_id', $user->id)->where('status', 'accepted');
        }])
        ->where(function($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhereHas('brahminsForKarm', function($q) use ($user) {
                  $q->where('brahmin_id', $user->id)->where('status', 'accepted');
              });
        });
    
        if ($filter == 'upcoming') {
            $query->where('prayog_date', '>', Carbon::today());
        } elseif ($filter == 'previous') {
            $query->where('prayog_date', '<', Carbon::today());
        }
    
        // Order by prayog_date in ascending order
        $query->orderBy('prayog_date', 'asc');
    
        $karms = $query->get()->map(function ($karm) {
            return [
                'prayog_id' => $karm->id,
                'prayog_name' => $karm->prayog_name,
                'place' => $karm->place,
                'date' => $karm->prayog_date,
                'tomorrow_flag' => Carbon::parse($karm->prayog_date)->isSameDay(Carbon::tomorrow()),
                'created_by_id' => $karm->created_by,
                'created_by_name' => $karm->createdBy->full_name
            ];
        });
        $pendingKarmCount = BrahminsForKarm::whereIn('status', ['Pending', 'Rejected'])
        ->whereHas('karm', function ($query) {
            $query->where('prayog_date', '>=', Carbon::today());
        })
        ->where('brahmin_id', $userId)
        ->with(['karm.createdBy', 'user'])
        ->get()->count();
        $pendingContacts = Contacts::where('receiver_id', $userId)
        ->where('status', 'pending')
        ->get()->count();


     


        $todaysQuery = Karm::with(['createdBy', 'brahminsForKarm' => function($q) use ($user) {
            $q->where('brahmin_id', $user->id)->where('status', 'accepted');
        }])
        ->where('prayog_date', Carbon::today())
        ->where(function($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhereHas('brahminsForKarm', function($q) use ($user) {
                  $q->where('brahmin_id', $user->id)->where('status', 'accepted');
              });
        })
        ->orderBy('prayog_date', 'asc');
    
        $todaysKarms = $todaysQuery->get()->map(function ($karm) {
            return [
                'prayog_id' => $karm->id,
                'prayog_name' => $karm->prayog_name,
                'place' => $karm->place,
                'date' => $karm->prayog_date,
                'created_by_id' => $karm->created_by,
                'created_by_name' => $karm->createdBy->full_name
            ];
        });
    
        return response()->json(['pendingKarmCount' => $pendingKarmCount, 'karm' => $karms,'pendingContactRequests'=>$pendingContacts,  'todaysKarms' => $todaysKarms]);

        
    }
    public function AddKarm(Request $request)
    {
        $userId = Auth::user()->id;
        $userName = Auth::user()->full_name;

        $user = Karm::create([
            'prayog_name' => $request->prayog_name,
            'prayog_date' => $request->prayog_date,
            'place' => $request->place,
            'remarks' => $request->remarks,
            'created_by' => $userId,
            'manual_brahmins' => $request->manual_brahmins
        ]);
        if($request->brahmins){
        foreach ($request->brahmins as $brahmin) {
            $brahminUser = User::find($brahmin);
            if ($brahminUser) {
            $brahminsforKarm = BrahminsForkarm::create([
                'brahmin_id' => $brahmin,
                'karm_id' => $user->id,
                'status' => 'Pending',
            ]);
            if ($brahminUser->fcm_token) {
                // Prepare notification details
                $title = 'New Karm Request';
                $body = $userName . ' sent you a new Karm request.';
                $target = $brahminUser->fcm_token;
    
                // Send notification via FCMService
                $response = $this->fcmService->sendNotification($title, $body, $target);
            }

        }
        }
    }

        return response()->json(['message' => 'Karm added', 'karm' => $user]);
    }

    public function getPendingKarmsForAuthUser()
    {
        $userId = Auth::id(); // Get the authenticated user's ID

        // Query to get the data
        $data = BrahminsForKarm::whereIn('status', ['Pending', 'Rejected'])
            ->whereHas('karm', function ($query) {
                $query->where('prayog_date', '>=', Carbon::today());
            })
            ->where('brahmin_id', $userId)
            ->with(['karm.createdBy', 'user'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'karm_id' => $item->karm_id,
                    'brahmin_id' => $item->brahmin_id,
                    'status' => $item->status,
                    'prayog_date' => $item->karm->prayog_date,
                    'prayog_name' => $item->karm->prayog_name,
                    'place' => $item->karm->place,
                    'acharya' => $item->karm->createdBy->full_name,
                    'brahmin_name' => $item->user->full_name
                ];
            });
        return response()->json(['message' => 'pending request fetched', 'requests' => $data]);
    }

    public function acceptKarm($id)
    {
        $karm = BrahminsForkarm::find($id);

        // Check if the contact exists
        if (!$karm) {
            return response()->json([
                'error' => 'Karm not found',
            ], 200);
        }

        // Update the status to "accepted"
        $karm->status = 'accepted';
        $karm->save();

        // Return a JSON response indicating success
        return response()->json([
            'message' => 'Request accepted',
        ]);
    }

    public function rejectKarm($id)
    {
        $karm = BrahminsForkarm::find($id);

        // Check if the contact exists
        if (!$karm) {
            return response()->json([
                'error' => 'Karm not found',
            ], 200);
        }

        // Update the status to "accepted"
        $karm->status = 'Rejected';
        $karm->save();

        // Return a JSON response indicating success
        return response()->json([
            'message' => 'Request Rejected',
        ]);
    }

    public function updateKarm(Request $request, $id)
    {
        $userId = Auth::user()->id;
    
        // Find the Karm record by its ID
        $karm = Karm::find($id);
    
        if (!$karm) {
            return response()->json(['message' => 'Karm not found'], 404);
        }
    
        // Update the Karm record
        $karm->update([
            'prayog_name' => $request->prayog_name,
            'prayog_date' => $request->prayog_date,
            'place' => $request->place,
            'remarks' => $request->remarks,
            'manual_brahmins' => $request->manual_brahmins
        ]);
    
        // If brahmins are provided, update the BrahminsForKarm records
        if ($request->has('brahmins')) {
            $existingBrahminsForKarm = BrahminsForKarm::where('karm_id', $id)->get()->keyBy('brahmin_id');
    
            foreach ($request->brahmins as $brahminId) {
                if (!$existingBrahminsForKarm->has($brahminId)) {
                    // Create new BrahminsForKarm record if it doesn't exist
                    BrahminsForKarm::create([
                        'brahmin_id' => $brahminId,
                        'karm_id' => $karm->id,
                        'status' => 'Pending',
                    ]);
                }
            }
    
            // Optionally, you can remove brahmins that are no longer in the updated list
            $updatedBrahminIds = collect($request->brahmins);
            BrahminsForKarm::where('karm_id', $id)
                ->whereNotIn('brahmin_id', $updatedBrahminIds)
                ->delete();
        }
    
        return response()->json(['message' => 'Karm updated', 'karm' => $karm]);
    }
    
    public function getKarmById($id)
    {
        $userId = Auth::id(); // Get the authenticated user's ID

        // Fetch the Karm record along with its associated createdBy and brahminsForkarm.user relationships
        $karm = Karm::with(['createdBy', 'brahminsForkarm.user'])->find($id);
    
        // Check if the Karm record exists
        if (!$karm) {
            return response()->json(['message' => 'Karm not found'], 404);
        }
    
        // Find the authenticated user's status in BrahminsForKarm
        $authUserStatus = BrahminsForKarm::where('karm_id', $id)
                                         ->where('brahmin_id', $userId)
                                         ->value('status');
    
        // Add the authenticated user's status to the Karm object
        $karm->auth_user_status = $authUserStatus;
    
        // Return the Karm record with its associated BrahminsForKarm records and the authenticated user's status
        return response()->json(['karm' => $karm]);
    }

    public function getKarmAndUsersForTomorrow()
    {
        // Get tomorrow's date
        $tomorrow = Carbon::tomorrow()->toDateString();
    
        // Query to get Karm data along with associated Brahmins (users) for tomorrow's Karm
        $karmData = Karm::where('prayog_date', $tomorrow)
        ->with(['brahminsForKarm' => function($query) {
            $query->where('status', 'accepted'); // Filter for accepted status
        }, 'brahminsForKarm.user']) // Eager load BrahminsForKarm and related User
        ->get();
    
        // Structure the data for better readability (Karm with Users)
        $result = $karmData->map(function($karm) {
            return [
                'karm_id' => $karm->id,
                'prayog_name' => $karm->prayog_name,
                'prayog_date' => $karm->prayog_date,
                'place' => $karm->place,
                'remarks' => $karm->remarks,
                'users' => $karm->brahminsForKarm->map(function($brahminForKarm) {
                    return [
                        'user_id' => $brahminForKarm->user->id,
                        'user_name' => $brahminForKarm->user->full_name,
                        'fcm_token' => $brahminForKarm->user->fcm_token, // Assuming User model has 'name' field
                        'status' => $brahminForKarm->status
                    ];
                })
            ];
        });

        foreach ($result as $karm) {
            foreach ($karm['users'] as $user) {
                // Prepare notification title and body
                $title = 'Hey '.$user['user_name'].', Karm Reminder';
                $body = 'You have "' . $karm['prayog_name'] . '" at "' . $karm['place'] . '" tomorrow.';
    
                // Get the user's FCM token
                $target = $user['fcm_token'];
    
                // Send notification via FCMService
                if (!empty($target)) {
                    $response = $this->fcmService->sendNotification($title, $body, $target);
    
                    // Optionally log the response for debugging purposes
                    \Log::info('Notification sent to ' . $user['user_name'] . ': ' . $response);
                } else {
                    \Log::warning('No FCM token found for user: ' . $user['user_name']);
                }
            }
        }
    
        return $result;
    }
}
