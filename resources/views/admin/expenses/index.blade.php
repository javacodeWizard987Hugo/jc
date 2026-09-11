@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
@php
    $routePrefix = $routePrefix ?? (request()->route()->getName() && strpos(request()->route()->getName(), 'cashier.') === 0 ? 'cashier' : 'admin');
@endphp
<div class="space-y-6">
    <!-- Page Header -->
    <div class="page-header">
        <div class="flex justify-between items-center">
            <div>
                <h1>Expenses</h1>
                <p>Track and manage all business expenses</p>
            </div>
            <a href="{{ route($routePrefix . '.expenses.create') }}" class="btn btn-primary bg-white text-red-600 hover:bg-gray-100">
                <span class="mr-2">➕</span> Add Expense
            </a>
        </div>
    </div>

    <!-- Expenses Table Card -->
    <div class="content-card overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Expenses</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr>
                        <td class="text-gray-600">{{ $expense->expense_date->format('M d, Y') }}</td>
                        <td>
                            <span class="badge badge-info">{{ $expense->category->name }}</span>
                        </td>
                        <td class="text-gray-900">{{ $expense->description }}</td>
                        <td class="font-semibold text-red-600">Rs. {{ number_format($expense->amount, 2) }}</td>
                        <td class="text-gray-600 capitalize">{{ $expense->payment_method }}</td>
                        <td class="text-gray-600">{{ $expense->creator->name }}</td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route($routePrefix . '.expenses.edit', $expense) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</a>
                                <span class="text-gray-300">|</span>
                                <form action="{{ route($routePrefix . '.expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this expense?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">
                            <div class="text-4xl mb-2">💰</div>
                            <p>No expenses found. Add your first expense!</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
        <div class="p-4 border-t border-gray-200">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
