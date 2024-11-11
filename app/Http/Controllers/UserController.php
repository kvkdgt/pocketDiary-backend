<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Karm;
use App\Models\Password_reset_otps;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\ForgotPasswordMail;

class UserController extends Controller
{
    public function signup(Request $request)
    {
        $emailExists = User::where('email', $request->email)->exists();
        if ($emailExists) {
            return response()->json(['message' => 'Email already exists'], 200);
        }

        $phoneNumberExists = User::where('phone_number', $request->phone_number)->exists();
        if ($phoneNumberExists) {
            return response()->json(['message' => 'Phone number already exists'], 200);
        }

        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => bcrypt($request->password),
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }

    public function editProfile(Request $request)
    {
        $userId = Auth::user()->id;
        $user = User::find($userId);

        if ($request->has('email')) {
            $existingUserWithEmail = User::where('email', $request->email)->where('id', '!=', $user->id)->first();
            if ($existingUserWithEmail) {
                return response()->json(['message' => 'Email already exists'], 200);
            }
            $user->email = $request->email;
        }

        if ($request->has('phone_number')) {
            $existingUserWithPhoneNumber = User::where('phone_number', $request->phone_number)->where('id', '!=', $user->id)->first();
            if ($existingUserWithPhoneNumber) {
                return response()->json(['message' => 'Phone number already exists'], 200);
            }
            $user->phone_number = $request->phone_number;
        }

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('profile_pictures'), $imageName);
            $user->profile_picture = $imageName;
        }
        $user->full_name = $request->full_name;
        $user->save();

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
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
            return response()->json(['message' => 'Login successful', 'token' => $token, 'user' => $user]);
        } else {
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
        $existingOTP = Password_reset_otps::where('email', $user->email)->first();

        if ($existingOTP) {
            // Update existing OTP
            $existingOTP->otp = $otp;
            $existingOTP->save();
        } else {
            // Create new OTP
            $newOTP = Password_reset_otps::create([
                'email' => $user->email,
                'otp' => $otp
            ]);
        }


        Mail::to($user->email)->send(new ForgotPasswordMail($title, $body));
        $userEmail  = $user->email;
        return response()->json(['message' => 'OTP sent successfully', 'email' => $user->email]);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|digits:4',
            'new_password' => 'required|min:6',
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found']);
        }

        // Check if OTP matches
        $otpEntry = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$otpEntry) {
            return response()->json(['error' => 'Invalid OTP']);
        }

        // Update user's password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Delete the used OTP entry
        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        return response()->json(['message' => 'Password updated successfully']);
    }

    public function changePassword(Request $request)
    {
        $userId = Auth::user()->id;
        $credentials = $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6', // Assuming minimum password length is 8 characters
        ]);
        $userData = User::find($userId);
        if (!Hash::check($credentials['old_password'], $userData->password)) {
            return response()->json(['error' => 'Old password is incorrect'], 200);
        }
        $userData->password = bcrypt($credentials['new_password']);
        $userData->save();

        return response()->json(['message' => 'Password changed successfully']);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = Auth::user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json(['message' => 'FMC Token updated successfully.']);
    }



    
}
