@extends('layouts.app')

@section('title', 'Stock Adjustment Details')

@section('content')
@php
    $routePrefix = $routePrefix ?? (request()->route()->getName() && strpos(request()->route()->getName(), 'cashier.') === 0 ? 'cashier' : 'admin');
@endphp
<div class="px-4 sm:px-6 lg:px-8 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Stock Adjustment Details</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Item</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $stockAdjustment->item->name }}</dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500">Type</dt>
                <dd class="mt-1 text-sm">
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                        @if($stockAdjustment->type === 'expire') bg-red-100 text-red-800
                        @elseif($stockAdjustment->type === 'loss') bg-orange-100 text-orange-800
                        @elseif($stockAdjustment->type === 'stock_take') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $stockAdjustment->type)) }}
                    </span>
                </dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500">Quantity Change</dt>
                <dd class="mt-1 text-sm {{ $stockAdjustment->quantity < 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $stockAdjustment->quantity > 0 ? '+' : '' }}{{ \App\Models\Item::formatStock(abs($stockAdjustment->quantity), $stockAdjustment->item->unit_of_measure) }}
                </dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500">Balance After</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ \App\Models\Item::formatStock($stockAdjustment->balance_after, $stockAdjustment->item->unit_of_measure) }}
                </dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500">Created By</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $stockAdjustment->creator->name ?? 'N/A' }}</dd>
            </div>
            
            <div>
                <dt class="text-sm font-medium text-gray-500">Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $stockAdjustment->created_at->format('Y-m-d H:i:s') }}</dd>
            </div>
            
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $stockAdjustment->notes }}</dd>
            </div>
        </dl>

        <div class="mt-6">
            <a href="{{ route($routePrefix . '.stock-adjustments.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                Back to List
            </a>
        </div>
    </div>
</div>
@endsection

