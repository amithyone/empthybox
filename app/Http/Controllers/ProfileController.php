<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pin' => 'nullable|digits_between:4,6',
            'pin_confirmation' => 'nullable|same:pin',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];

        if (!empty($validated['pin'])) {
            $user->pin_hash = Hash::make($validated['pin']);
            $user->pin_set_at = now();
        }

        // Handle password change if provided
        if (!empty($validated['new_password'])) {
            // Require current password to match
            if (empty($validated['current_password']) || !Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect'])->withInput();
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return redirect()->route('profile.index')->with('status', 'Profile updated successfully');
    }
}


