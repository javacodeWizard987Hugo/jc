@extends('layouts.app')

@section('title', 'Create Customer')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create Customer / Client</h1>
        <p class="mt-2 text-sm text-gray-600">Add a new customer to the system</p>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('cashier.customers.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                <input type="text" name="name" id="name" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('name') border-red-500 @enderror"
                       value="{{ old('name') }}" autofocus>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="emi_number" class="block text-sm font-medium text-gray-700">Emi Number</label>
                <input type="text" name="emi_number" id="emi_number"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('emi_number') border-red-500 @enderror"
                       value="{{ old('emi_number') }}">
                @error('emi_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="nic" class="block text-sm font-medium text-gray-700">NIC (National ID)</label>
                <input type="text" name="nic" id="nic"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('nic') border-red-500 @enderror"
                       value="{{ old('nic') }}">
                @error('nic')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700">Primary Phone</label>
                <input type="text" name="phone" id="phone"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('phone') border-red-500 @enderror"
                       value="{{ old('phone') }}">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="mobile_numbers" class="block text-sm font-medium text-gray-700">Additional Mobile Numbers</label>
                <input type="text" name="mobile_numbers" id="mobile_numbers"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                       placeholder="Enter multiple numbers, separated by commas">
                <p class="mt-1 text-sm text-gray-500">You can add more numbers later.</p>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('email') border-red-500 @enderror"
                       value="{{ old('email') }}">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" id="address" rows="3"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="primary_branch_id" class="block text-sm font-medium text-gray-700">Primary Branch</label>
                <select name="primary_branch_id" id="primary_branch_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                    <option value="">None</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('primary_branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500">Assign a primary branch for this customer (optional).</p>
            </div>

            <div class="mb-4">
                <label for="credit_limit" class="block text-sm font-medium text-gray-700">Credit Limit (Rs.)</label>
                <input type="number" name="credit_limit" id="credit_limit" step="0.01" min="0"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('credit_limit') border-red-500 @enderror"
                       value="{{ old('credit_limit', 0) }}">
                @error('credit_limit')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Maximum credit amount allowed for this customer</p>
            </div>

            <div class="mb-4">
                <label for="category" class="block text-sm font-medium text-gray-700">Customer Category</label>
                <select name="category" id="category" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md">
                    <option value="normal" {{ old('category') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="vip" {{ old('category') === 'vip' ? 'selected' : '' }}>VIP</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="emi_lock_mode" class="block text-sm font-medium text-gray-700">Lock Mode</label>
                <input type="text" name="emi_lock_mode" id="emi_lock_mode"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('emi_lock_mode') border-red-500 @enderror"
                       value="{{ old('emi_lock_mode') }}">
                @error('emi_lock_mode')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="promotional_sms_opt_in" value="1" {{ old('promotional_sms_opt_in') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700">Receive Promotional SMS</span>
                </label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('cashier.pos') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Create Customer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

