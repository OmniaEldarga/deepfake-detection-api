<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Carbon\Carbon;
use App\Models\User;
use App\Models\EmailVerification;
use App\Models\Profile;
use App\Notifications\SendOtpNotification;
use Illuminate\Notifications\Notifiable;
use App\Notifications\LoginNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Http\Requests\VerifyOtpRequest;
use App\Notifications\ResetOtpNotification;


class UserController extends Controller
{
    use Notifiable;

/** Register and Send OTP */
    public function register(RegisterRequest $request)
{
        $validated = $request->validated();
    //create user(without verifying email yet)
    $user = User::create([
        'full_name' => $request->full_name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);
    // Generate OTP and token
        $otp = rand(100000, 999999);
        $token = Str::uuid();
    // delete old code (ifthere)
        EmailVerification::where('email', $user->email)->delete();
    // Upload profile image if exists
        $imagePath = null;
            if ($request->hasFile('profile_image')) {
                $imagePath = $request->file('profile_image')->store('profile_images', 'public');
                }
    // Create profile
        Profile::create([
            'user_id' => $user->id,
            'phone' => $request->phone,
            'profile_image' => $imagePath?? 'default_profile_picture.jpg',
        ]);
    // Save email verification data
        EmailVerification::create([
        'email' => $request->email,
        'otp_code' => $otp,
        'expires_at' => now()->addMinutes(10),
        'token' => $token,
                    ]);
    // Send OTP notification
    $user->notify(new SendOtpNotification($otp));
    // Return token to frontend
        return response()->json([
            'status' => true,
            'message' => 'A verification code has been sent to your email.',
            'token' => $token,
        ]);
}
/**2-Verify otp and Activate Email */
    public function verifyOtpAndRegister(VerifyOtpRequest  $request)
{
        $verification = EmailVerification::where('token', $request->token)
        ->where('otp_code', $request->otp_code)
        ->where('expires_at', '>', now())
        ->first();

        if (!$verification) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP code.',
            ], 403);
        }
    // Activate the user's email
    $user = User::where('email', $verification->email)->first();
        if ($user) {
            $user->email_verified_at = now();
            $user->save();
        }
    //delete  OTP
        $verification->delete();
        return response()->json([
            'status' => true,
            'message' => 'Email verified successfully.',
        ], 200);
}
//Login
    public function login(LoginRequest $request)
{
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }
        if (!$user->email_verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Please verify your email before logging in.',
            ], 403);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'status' => true,
            'message' => 'Login successful!',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
}
//logout
public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully.'
        ]);
    }
//reset and sendOtp
    public function sendotp(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email'
    ]);

    $user = User::where('email', $request->email)->first();
    $otp = rand(100000, 999999);
    $token = Str::uuid();

    EmailVerification::where('email', $request->email)->delete();
    EmailVerification::create([
        'email' => $request->email,
        'token' => $token,
        'otp_code' => $otp,
        'expires_at' => now()->addMinutes(10),
        'reset_pass' => true,
        'user_id' => $user->id
    ]);
    $user->notify(new ResetOtpNotification($otp));
    return response()->json([
        'message' => 'OTP sent to your email.',
        'token' => $token
    ]);

}
///verifyOtp
public function verifyOtp(Request $request)
{
    $request->validate([
        'otp_code' => 'required|string',
        'token' => 'required|uuid',
    ]);

    $verification = EmailVerification::where('token', $request->token)->first();

    if (!$verification) {
        return response()->json(['message' => 'Invalid token'], 404);
    }

    if (now()->greaterThan($verification->expires_at)) {
        return response()->json(['message' => 'OTP expired'], 403);
    }

    if ($verification->otp_code !== $request->otp_code) {
        $verification->increment('attempts');
        return response()->json(['message' => 'Incorrect OTP'], 401);
    }

    return response()->json([
        'message' => 'OTP verified successfully',
        'token' => $verification->token
    ]);
}
//reset password
public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required|uuid',
        'new_password' => 'required|string|min:8',
    ]);
    $verification = EmailVerification::where('token', $request->token)->first();

    if (!$verification || !$verification->reset_pass) {
        return response()->json(['message' => 'Invalid request'], 400);
    }

    $user = $verification->user;

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $user->password = Hash::make($request->new_password);
    $user->save();
    $verification->delete();
    return response()->json(['message' => 'Password has been updated successfully']);
}
}
