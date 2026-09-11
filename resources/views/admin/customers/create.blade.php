@extends('layouts.app')

@section('title', 'Create Customer')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create Customer / Client</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('admin.customers.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                <input type="text" name="name" id="name" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('name') border-red-500 @enderror"
                       value="{{ old('name') }}">
                @error('name')
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
                    @if(session('existingCustomer'))
                        <div class="mt-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
                            <h4 class="font-bold">Existing Customer Found:</h4>
                            <p>Name: {{ session('existingCustomer')->name }}</p>
                            <p>Phone: {{ session('existingCustomer')->phone }}</p>
                            <a href="{{ route('admin.customers.edit', session('existingCustomer')) }}" class="text-blue-500 hover:underline">Edit this customer</a>
                        </div>
                    @endif
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
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="mb-4">
                <label for="category" class="block text-sm font-medium text-gray-700">Customer Category</label>
                <select name="category" id="category" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md">
                    <option value="normal" {{ old('category') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="vip" {{ old('category') === 'vip' ? 'selected' : '' }}>VIP</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="emi_lock_mode" class="block text-sm font-medium text-gray-700">EMI Lock Mode</label>
                <input type="text" name="emi_lock_mode" id="emi_lock_mode"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('emi_lock_mode') border-red-500 @enderror"
                       value="{{ old('emi_lock_mode') }}">
                @error('emi_lock_mode')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="emi_number" class="block text-sm font-medium text-gray-700">EMI Number</label>
                <input type="text" name="emi_number" id="emi_number"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('emi_number') border-red-500 @enderror"
                       value="{{ old('emi_number') }}">
                @error('emi_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <hr class="my-6 border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Guarantor Information</h3>

            <div class="mb-4">
                <label for="guarantor_name" class="block text-sm font-medium text-gray-700">Guarantor Name</label>
                <input type="text" name="guarantor_name" id="guarantor_name"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                       value="{{ old('guarantor_name') }}">
            </div>

            <div class="mb-4">
                <label for="guarantor_nic" class="block text-sm font-medium text-gray-700">Guarantor NIC</label>
                <input type="text" name="guarantor_nic" id="guarantor_nic"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                       value="{{ old('guarantor_nic') }}">
            </div>

            <div class="mb-4">
                <label for="guarantor_mobile_number" class="block text-sm font-medium text-gray-700">Guarantor Mobile</label>
                <input type="text" name="guarantor_mobile_number" id="guarantor_mobile_number"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                       value="{{ old('guarantor_mobile_number') }}">
            </div>

            <div class="mb-4">
                <label for="guarantor_address" class="block text-sm font-medium text-gray-700">Guarantor Address</label>
                <textarea name="guarantor_address" id="guarantor_address" rows="2"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">{{ old('guarantor_address') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="promotional_sms_opt_in" value="1" {{ old('promotional_sms_opt_in') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700">Receive Promotional SMS</span>
                </label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
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

