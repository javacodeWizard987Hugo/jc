@extends('layouts.app')

@section('title', 'Create Supplier')

@section('content')
@php
    $routePrefix = $routePrefix ?? (request()->route()->getName() && strpos(request()->route()->getName(), 'cashier.') === 0 ? 'cashier' : 'admin');
@endphp
<div class="px-4 sm:px-6 lg:px-8 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create Supplier</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route($routePrefix . '.suppliers.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Supplier Name *</label>
                <input type="text" name="name" id="name" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                       value="{{ old('name') }}">
            </div>

            <div class="mb-4">
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" id="address" rows="2"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">{{ old('address') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="contact_person" class="block text-sm font-medium text-gray-700">Contact Person</label>
                    <input type="text" name="contact_person" id="contact_person"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           value="{{ old('contact_person') }}">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" id="phone"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           value="{{ old('phone') }}">
                </div>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                       value="{{ old('email') }}">
            </div>

            <div class="mb-4">
                <label for="payment_terms" class="block text-sm font-medium text-gray-700">Payment Terms</label>
                <input type="text" name="payment_terms" id="payment_terms" placeholder="e.g., 30 days credit"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                       value="{{ old('payment_terms') }}">
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route($routePrefix . '.suppliers.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Create Supplier
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

