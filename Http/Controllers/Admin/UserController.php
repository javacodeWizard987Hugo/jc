<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,cashier',
            'pin' => 'nullable|string|size:6',
            'permissions' => 'nullable|array',
        ]);

        // Store plain text password for viewing
        $validated['password_viewable'] = $validated['password'];
        // Hash password for authentication
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;
        
        // Ensure permissions are empty for admins, or set for cashiers
        if ($validated['role'] === 'admin') {
            $validated['permissions'] = null;
        } else {
            $validated['permissions'] = $request->input('permissions', []);
        }

        $user = User::create($validated);
        
        AuditLog::log('user_created', "Created user: {$user->name} ({$user->role})", $user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,cashier',
            'is_active' => 'boolean',
            'pin' => 'nullable|string|size:6',
            'permissions' => 'nullable|array',
        ]);

        if ($request->filled('password')) {
            // Store plain text password for viewing
            $validated['password_viewable'] = $validated['password'];
            // Hash password for authentication
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
            unset($validated['password_viewable']);
        }

        $validated['is_active'] = $request->has('is_active');
        
        // Ensure permissions are empty for admins, or updated for cashiers
        if ($validated['role'] === 'admin') {
            $validated['permissions'] = null;
        } else {
            $validated['permissions'] = $request->input('permissions', []);
        }

        $oldValues = $user->getOriginal();
        $user->update($validated);
        
        AuditLog::log('user_updated', "Updated user: {$user->name}", $user, $oldValues, $user->getChanges());

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $userName = $user->name;
        $user->delete();
        
        AuditLog::log('user_deleted', "Deleted user: {$userName}", null);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
