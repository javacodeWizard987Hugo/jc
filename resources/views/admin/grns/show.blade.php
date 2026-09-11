@extends('layouts.app')

@section('title', 'GRN Details - ' . $grn->grn_number)

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route(($routePrefix ?? 'admin') . '.grns.index') }}"
           class="text-red-600 hover:text-red-900 mb-4 inline-block">
            ← Back to GRNs
        </a>
        <h1 class="text-3xl font-bold text-gray-900">
            GRN: {{ $grn->grn_number }}
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">GRN Information</h2>
            <dl class="space-y-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Supplier</dt>
                    <dd class="text-sm text-gray-900">
                        {{ $grn->supplier->name }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">GRN Date</dt>
                    <dd class="text-sm text-gray-900">
                        {{ $grn->grn_date->format('Y-m-d') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Reference Document</dt>
                    <dd class="text-sm text-gray-900">
                        {{ $grn->reference_document ?? 'N/A' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                    <dd class="text-sm font-semibold text-red-600">
                        Rs. {{ number_format($grn->total_amount, 2) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created By</dt>
                    <dd class="text-sm text-gray-900">
                        {{ $grn->creator->name }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Items</h2>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Item
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Quantity (PCS)
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Unit Cost
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Total Cost
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        Expiry Date
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($grn->items as $grnItem)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $grnItem->item->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ number_format($grnItem->quantity) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        Rs. {{ number_format($grnItem->unit_cost, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                        Rs. {{ number_format($grnItem->total_cost, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $grnItem->expiry_date?->format('Y-m-d') ?? 'N/A' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
