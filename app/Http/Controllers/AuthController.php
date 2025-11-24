<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $key = 'login.' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again later.',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Welcome back!',
                'redirect' => route('dashboard'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials. Please try again.',
        ], 401);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Create wallet for user
        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        Auth::login($user);

        // Send verification email
        try {
            Mail::to($user->email)->send(new VerifyEmail($user));
        } catch (\Exception $e) {
            // Log error but don't fail registration
            \Log::error('Failed to send verification email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully! Please check your email to verify your account.',
            'redirect' => route('dashboard'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function verifyEmail($userId, $hash)
    {
        $user = User::findOrFail($userId);
        
        if (sha1($user->email) === $hash) {
            $user->update(['email_verified_at' => now()]);
            
            return redirect()->route('login')->with('success', 'Email verified successfully! You can now log in.');
        }
        
        return redirect()->route('login')->with('error', 'Invalid verification link.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        
        // Generate reset code
        $resetCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store in session (expires in 60 minutes)
        session()->put('password_reset_code', [
            'email' => $user->email,
            'code' => $resetCode,
            'expires' => now()->addMinutes(60),
        ]);

        // Send reset code email
        try {
            Mail::to($user->email)->send(new \App\Mail\PasswordResetCode($user, $resetCode));
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset code. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset code sent to your email!',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'code' => 'required|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $stored = session()->get('password_reset_code');
        
        if (!$stored || now()->gt($stored['expires'])) {
            session()->forget('password_reset_code');
            return response()->json([
                'success' => false,
                'message' => 'Reset code expired. Please request a new one.',
            ], 400);
        }

        if ($stored['code'] !== $request->code) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reset code.',
            ], 400);
        }

        $user = User::where('email', $stored['email'])->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);
        
        session()->forget('password_reset_code');

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully! You can now log in.',
            'redirect' => route('login'),
        ]);
    }

    public function setupPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits_between:4,6',
            'pin_confirmation' => 'required|same:pin',
        ]);

        auth()->user()->update([
            'pin_hash' => Hash::make($request->pin),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN setup successfully!',
        ]);
    }
}






