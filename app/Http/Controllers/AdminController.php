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

class AdminController extends Controller
{
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
        return view('admin.dashboard', compact('totalUsers', 'todaysNewUsers','totalKarm', 'todaysKarm', 'upcomingKarm', 'previousKarm'));
    }
}
