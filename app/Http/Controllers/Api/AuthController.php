<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\SendResetOtpNotification;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Handle student login and issue Sanctum token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // Attempt authentication
        // Support both username and email, assuming the app passes 'username' field in 'email' var as it said "Username / Email ID"
        // Let's check both email and name, but Laravel auth typically checks email
        // Or if we need to support both, we can try to find the user by email or name.
        $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $credentials = [
            $loginField => $request->email,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account is deactivated.',
            ], 403);
        }

        // Only allow students
        if (!$user->isStudent()) {
            return response()->json([
                'message' => 'Access denied. You are not a registered student.',
            ], 403);
        }

        // Load student details
        $user->load('institution');
        
        $student = $user->student;
        if ($student) {
            $student->load(['program', 'cohort']);
        }

        // Create token
        $token = $user->createToken('student-app-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'student_id' => $student ? $student->student_id : null,
                'program' => $student && $student->program ? $student->program->name : null,
                'cohort' => $student && $student->cohort ? $student->cohort->name : null,
                'code' => $student && $student->cohort ? $student->cohort->code : null,
                'institution' => $user->institution ? $user->institution->name : null,
            ]
        ], 200);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.'
        ], 200);
    }

    /**
     * Get authenticated student's profile.
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'message' => 'Access denied. You are not a registered student.',
            ], 403);
        }

        $student = $user->student()->with(['program', 'cohort', 'institution'])->first();

        if (!$student) {
            return response()->json([
                'message' => 'Student profile not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Profile fetched successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
            'student' => $student
        ], 200);
    }

    /**
     * Handle Forgot Password - Send OTP
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));

        // Store OTP in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $otp,
                'created_at' => Carbon::now()
            ]
        );

        // Send OTP via email
        $user->notify(new SendResetOtpNotification($otp));

        return response()->json([
            'message' => 'OTP sent to your email.'
        ]);
    }

    /**
     * Handle Reset Password using OTP
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || $resetRecord->token !== $request->otp) {
            return response()->json([
                'message' => 'Invalid or expired OTP.'
            ], 400);
        }

        // Check if OTP is older than 15 minutes
        if (Carbon::parse($resetRecord->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'message' => 'OTP has expired.'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete OTP after successful reset
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Password reset successfully. You can now log in.'
        ]);
    }

    /**
     * Handle Change Password for logged-in user
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        // Optionally delete other active tokens if you want to force logout everywhere else
        // $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'message' => 'Password changed successfully.'
        ]);
    }
}
