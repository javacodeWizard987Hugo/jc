@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
@php
    $routePrefix = $routePrefix ?? (request()->route()->getName() && strpos(request()->route()->getName(), 'cashier.') === 0 ? 'cashier' : 'admin');
@endphp
<div class="space-y-6">
    <!-- Page Header -->
    <div class="page-header">
        <div class="flex justify-between items-center">
            <div>
                <h1>Suppliers</h1>
                <p>Manage your suppliers and vendor relationships</p>
            </div>
            <a href="{{ route($routePrefix . '.suppliers.create') }}" class="btn btn-primary bg-white text-red-600 hover:bg-gray-100">
                <span class="mr-2">➕</span> Add Supplier
            </a>
        </div>
    </div>

    <!-- Suppliers Table Card -->
    <div class="content-card overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Suppliers</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Outstanding Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr>
                        <td>
                            <div class="font-medium text-gray-900">{{ $supplier->name }}</div>
                            <div class="text-sm text-gray-500">{{ $supplier->contact_person }}</div>
                        </td>
                        <td>
                            <div class="text-sm text-gray-900">{{ $supplier->phone }}</div>
                            <div class="text-sm text-gray-500">{{ $supplier->email }}</div>
                        </td>
                        <td>
                            <span class="font-semibold {{ $supplier->outstanding_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                Rs. {{ number_format($supplier->outstanding_balance, 2) }}
                            </span>
                        </td>
                        <td>
                            @if($supplier->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route($routePrefix . '.suppliers.show', $supplier) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                                <span class="text-gray-300">|</span>
                                <a href="{{ route($routePrefix . '.suppliers.edit', $supplier) }}" class="text-green-600 hover:text-green-800 text-sm font-medium">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">
                            <div class="text-4xl mb-2">🏢</div>
                            <p>No suppliers found. Add your first supplier!</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
