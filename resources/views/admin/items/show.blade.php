@extends('layouts.app')

@section('title', 'Item Details - ' . $item->name)

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route(($routePrefix ?? 'admin') . '.items.index') }}"
           class="text-red-600 hover:text-red-900 mb-4 inline-block">
            ← Back to Items
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Item Details</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Basic Information -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Basic Information</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Item Code:</dt>
                    <dd class="text-sm text-gray-900 font-mono">
                        {{ $item->item_code }}
                    </dd>
                </div>

                @if($item->barcode)
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Barcode:</dt>
                    <dd class="text-sm text-gray-900 font-mono">
                        {{ $item->barcode }}
                    </dd>
                </div>
                @endif

                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Name:</dt>
                    <dd class="text-sm text-gray-900 font-semibold">
                        {{ $item->name }}
                    </dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Category:</dt>
                    <dd class="text-sm text-gray-900">
                        <span class="badge badge-info">
                            {{ $item->category->name }}
                        </span>
                    </dd>
                </div>

                @if($item->supplier)
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Supplier:</dt>
                    <dd class="text-sm text-gray-900">
                        {{ $item->supplier->name }}
                    </dd>
                </div>
                @endif

                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Unit of Measure:</dt>
                    <dd class="text-sm text-gray-900 font-semibold">
                        PCS
                    </dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Status:</dt>
                    <dd class="text-sm">
                        @if($item->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Pricing & Stock Information -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Pricing & Stock</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Cost Price:</dt>
                    <dd class="text-sm text-gray-900">
                        Rs. {{ number_format($item->cost_price, 2) }}
                    </dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Selling Price:</dt>
                    <dd class="text-sm text-red-600 font-semibold">
                        Rs. {{ number_format($item->selling_price, 2) }}
                    </dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Current Stock:</dt>
                    <dd class="text-sm text-gray-900 font-semibold">
                        {{ (int)$currentStock }} PCS
                    </dd>
                </div>

                @if($item->reorder_level > 0)
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Reorder Level:</dt>
                    <dd class="text-sm text-gray-900">
                        {{ (int)$item->reorder_level }} PCS
                    </dd>
                </div>
                @endif

                @if($isLowStock)
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-red-600">Stock Status:</dt>
                    <dd class="text-sm">
                        <span class="badge badge-danger">Low Stock</span>
                    </dd>
                </div>
                @endif

                @if($item->expiry_date)
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Expiry Date:</dt>
                    <dd class="text-sm text-gray-900">
                        {{ $item->expiry_date->format('M d, Y') }}

                        @if($item->expiry_date < now())
                            <span class="badge badge-danger ml-2">Expired</span>
                        @elseif($item->expiry_date <= now()->addDays(7))
                            <span class="badge badge-warning ml-2">Expiring Soon</span>
                        @endif
                    </dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Stock History -->
    @if($item->stockMovements->count() > 0)
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Recent Stock Movements</h2>
            <a href="{{ route(($routePrefix ?? 'admin') . '.items.stock-history', $item) }}"
               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View Full History →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock After</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($item->stockMovements->take(10) as $movement)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $movement->created_at->format('M d, Y H:i') }}
                        </td>

                        <td class="px-6 py-4 text-sm">
                            <span class="badge {{ $movement->type === 'in' ? 'badge-success' : 'badge-danger' }}">
                                {{ strtoupper($movement->type) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-sm font-semibold {{ $movement->type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $movement->type === 'in' ? '+' : '-' }}{{ (int)abs($movement->quantity) }} PCS
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ (int)$movement->stock_after }} PCS
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $movement->creator->name ?? 'System' }}
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $movement->notes ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex justify-end space-x-3">
        <a href="{{ route(($routePrefix ?? 'admin') . '.items.index') }}"
           class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            Back to List
        </a>

        <a href="{{ route(($routePrefix ?? 'admin') . '.items.stock-history', $item) }}"
           class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            View Stock History
        </a>

        <a href="{{ route(($routePrefix ?? 'admin') . '.items.edit', $item) }}"
           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
            Edit Item
        </a>
    </div>
</div>
@endsection

