<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'cashier.pos');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'cashier', // Default to cashier for all new registrations
            'is_active' => true,
        ]);

        AuditLog::log('user_registered', "New user registered: {$user->name} (cashier)", $user);

        Auth::login($user);

        return redirect()->route('cashier.pos')
            ->with('success', 'Account created successfully! Welcome!');
    }
}
