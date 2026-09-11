@extends('layouts.app')

@section('title', 'Shift Summary')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Shift Summary</h1>
        <p class="text-sm text-gray-500 mt-1">Sales summary for {{ $today->format('F d, Y') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white shadow rounded-lg p-6 border-l-4 border-red-600">
            <p class="text-sm text-gray-500">Total Sales</p>
            <p class="text-3xl font-bold text-gray-900">Rs. {{ number_format($summary['total_sales'], 2) }}</p>
        </div>

        <div class="bg-white shadow rounded-lg p-6 border-l-4 border-blue-600">
            <p class="text-sm text-gray-500">Total Bills</p>
            <p class="text-3xl font-bold text-gray-900">{{ $summary['total_bills'] }}</p>
        </div>

        <div class="bg-white shadow rounded-lg p-6 border-l-4 border-green-600">
            <p class="text-sm text-gray-500">Items Sold</p>
            <p class="text-3xl font-bold text-gray-900">{{ $summary['total_items'] }}</p>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Sales by Payment Method</h3>
        <div class="space-y-3">
            @foreach($summary['by_payment_method'] as $method => $data)
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                <span class="text-sm font-medium text-gray-700">{{ ucfirst($method) }}</span>
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900">Rs. {{ number_format($data['amount'], 2) }}</p>
                    <p class="text-xs text-gray-500">{{ $data['count'] }} bills</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

