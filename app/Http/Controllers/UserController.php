<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Password_reset_otps;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordMail;

class UserController extends Controller
{
    public function signup(Request $request)
    {
        $validatedData = $request->validate(User::$rules);

        $user = User::create([
            'full_name' => $validatedData['full_name'],
            'email' => $validatedData['email'],
            'phone_number' => $validatedData['phone_number'],
            'password' => bcrypt($validatedData['password']),
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('AuthToken')->plainTextToken;
            return response()->json(['message' => 'Login successful', 'token' => $token]);
        }
        else {
            return response()->json(['message' => 'Invalid Credentials']);
        }

        
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout successful']);
    }

    public function sendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = DB::table('users')->where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT); // Generate 4-digit OTP

        $title = 'do not share this otp with anyone';
        $body = $otp;
        $Password_reset_otps = Password_reset_otps::create([
            'email' => $user->email,
            'otp' => $otp
        ]);

        Mail::to($user->email)->send(new ForgotPasswordMail($title, $body));

        return response()->json(['message' => 'OTP sent successfully']);
    }
}

