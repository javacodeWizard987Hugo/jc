@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Change Password</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('password.change') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password *</label>
                <input type="password" name="current_password" id="current_password" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('current_password') border-red-500 @enderror">
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password *</label>
                <input type="password" name="new_password" id="new_password" required minlength="6"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('new_password') border-red-500 @enderror">
                @error('new_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password *</label>
                <input type="password" name="new_password_confirmation" id="new_password_confirmation" required minlength="6"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ url()->previous() }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Change Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

