<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'cashier.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Determine login mode
        $loginMode = $request->input('login_mode', 'email');
        
        if ($loginMode === 'pin') {
            $request->validate([
                'pin' => 'required|string|size:6',
            ]);
        } else {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
        }

        $user = null;
        $authenticated = false;

        // Try PIN login first if PIN is provided
        if ($loginMode === 'pin' && $request->filled('pin')) {
            $user = \App\Models\User::where('pin', $request->pin)
                ->where('is_active', true)
                ->first();
            
            if ($user) {
                $authenticated = true;
            } else {
                return back()->withErrors(['pin' => 'Invalid PIN. Please try again.'])->withInput(['login_mode' => 'pin']);
            }
        } 
        // Otherwise try email/password login
        else if ($loginMode === 'email' && $request->filled('email') && $request->filled('password')) {
            $user = \App\Models\User::where('email', $request->email)->first();

            if (!$user) {
                return back()->withErrors(['email' => 'No account found with this email address.'])->withInput();
            }

            if (!$user->is_active) {
                return back()->withErrors(['email' => 'Your account is inactive. Please contact administrator.'])->withInput();
            }

            $authenticated = Auth::attempt([
                'email' => $request->email,
                'password' => $request->password
            ], $request->filled('remember'));
            
            if (!$authenticated) {
                return back()->withErrors(['password' => 'Invalid password. Please try again.'])->withInput();
            }
        } else {
            return back()->withErrors(['email' => 'Please provide email and password, or PIN.'])->withInput();
        }

        if ($authenticated && $user) {
            // Regenerate session for security
            $request->session()->regenerate();
            
            // Login the user
            Auth::login($user, $request->filled('remember'));
            
            AuditLog::log('login', 'User logged in', $user);
            
            return redirect()->intended(
                $user->isAdmin() ? route('admin.dashboard') : route('cashier.dashboard')
            )->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors(['email' => 'Invalid credentials. Please try again.'])->withInput();
    }

    public function logout(Request $request)
    {
        // Log logout action before invalidating session
        if (Auth::check()) {
            try {
                AuditLog::log('logout', 'User logged out', auth()->user());
            } catch (\Exception $e) {
                // If logging fails, continue with logout anyway
            }
        }
        
        // Logout user
        Auth::logout();
        
        // Invalidate and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
