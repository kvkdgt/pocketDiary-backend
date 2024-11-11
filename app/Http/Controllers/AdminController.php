<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminUser;
use App\Models\User;
use App\Models\Karm;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\FCMService;


class AdminController extends Controller
{
    protected $fcmService;
    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = AdminUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }
    public function login_view()
    {
        return view('admin_login');
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $user = Auth::guard('admin')->user();
            $token = $user->createToken('AuthToken')->plainTextToken;

            return to_route('admin/dashboard');
        } else {
            $request->session()->put('error', 'Invalid Credentials');
            return to_route('login');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('admin/login');
    }

    public function index()
    {
        // Fetch total users
        $totalUsers = User::count();

        // Fetch today's new users
        $todaysNewUsers = User::whereDate('created_at', now())->count();

        $totalKarm = Karm::count();

        // Fetch today's Karm
        $todaysKarm = Karm::whereDate('prayog_date', now())->count();

        // Fetch upcoming Karm (assumes upcoming Karm are those with prayog_date in the future)
        $upcomingKarm = Karm::whereDate('prayog_date', '>', now())->count();

        // Fetch previous Karm (assumes previous Karm are those with prayog_date in the past)
        $previousKarm = Karm::whereDate('prayog_date', '<', now())->count();


        // Pass the data to the view
        return view('admin.dashboard', compact('totalUsers', 'todaysNewUsers', 'totalKarm', 'todaysKarm', 'upcomingKarm', 'previousKarm'));
    }

    public function users(Request $request)
    {
        $query = User::withCount('createdKarms');

        // Apply search by name or mobile number
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('full_name', 'like', "%{$searchTerm}%")
                    ->orWhere('phone_number', 'like', "%{$searchTerm}%");
            });
        }

        // Apply selected filter
        switch ($request->input('filter')) {
            case 'highest':
                $query->orderBy('created_karms_count', 'desc');
                break;
            case 'lowest':
                $query->orderBy('created_karms_count', 'asc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                // No filtering
                break;
        }

        $users = $query->paginate(5);

        return view('admin/users', compact('users'));
    }

    public function marketing(Request $request)
    {
        return view('admin/marketing');
    }

    public function notifications(Request $request)
    {
        return view('admin/notifications');
    }

    public function sendCustomNotification(Request $request)
    {
        // Print the form data
        $title = $request->input('notification_title');
        $body = $request->input('notification_description');

        // If an image is uploaded, print the file details and upload it
        $imageUrl = null;
        if ($request->hasFile('notification_image')) {
            $image = $request->file('notification_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            // Store the image in a public directory or cloud storage
            // Example: store in public folder (this could be cloud storage like S3 or Firebase Storage)
            $image->move(public_path('notifications'), $imageName);
            $imageUrl =  'http://karmtrack.krishivtech.in/' . 'notifications/' . $imageName;
        }
        $users = User::whereNotNull('fcm_token')->get();
        foreach ($users as $user) {
            $this->fcmService->sendNotificationWithImage($title, $body, $user->fcm_token, $imageUrl);
        }
        // Pass the image URL to the service
        // Return success message
        return back()->with('success', 'Notifications Sent!');
    }
}
