@extends('layouts.app')

@section('title', 'Expenses Report')

@section('content')
@php
    $routePrefix = $routePrefix ?? (request()->route()->getName() && strpos(request()->route()->getName(), 'cashier.') === 0 ? 'cashier' : 'admin');
@endphp
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Expenses Report</h1>
        <div class="flex gap-3">
            <a href="{{ route($routePrefix . '.reports.export', 'expenses') }}?format=pdf&start_date={{ $startDate }}&end_date={{ $endDate }}&category_id={{ $categoryId }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                🖨️ Print PDF
            </a>
            <a href="{{ route($routePrefix . '.reports.export', 'expenses') }}?format=csv&start_date={{ $startDate }}&end_date={{ $endDate }}&category_id={{ $categoryId }}" 
               class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                📥 Export CSV
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route($routePrefix . '.reports.expenses') }}" class="grid grid-cols-4 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category_id" id="category_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Filter
                </button>
                <a href="{{ route($routePrefix . '.reports.export', ['type' => 'expenses', 'start_date' => $startDate, 'end_date' => $endDate, 'category_id' => $categoryId, 'format' => 'excel']) }}" 
                   class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-center">
                    Export CSV
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Total Expenses</h3>
            <p class="text-xl font-bold text-red-600">Rs. {{ number_format($summary['total'], 2) }}</p>
        </div>
        <div class="mt-4">
            <h4 class="text-md font-medium mb-2">By Category</h4>
            <div class="space-y-2">
                @foreach($summary['by_category'] as $data)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-700">{{ $data['category'] }}</span>
                    <span class="text-sm font-semibold">Rs. {{ number_format($data['amount'], 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($expenses as $expense)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $expense->expense_date->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $expense->category->name }}</td>
                    <td class="px-6 py-4 text-sm">{{ $expense->description }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">Rs. {{ number_format($expense->amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ucfirst($expense->payment_method) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No expenses found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

