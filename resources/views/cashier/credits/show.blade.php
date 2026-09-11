@extends('layouts.app')

@section('title', 'Credit Details')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 max-w-4xl">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Credit / Loan Details</h1>
        <a href="{{ route('cashier.credits.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
            Back
        </a>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Credit Information</h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Customer</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $credit->customer->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $credit->sale->invoice_number ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">Rs. {{ number_format($credit->amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Paid Amount</dt>
                <dd class="mt-1 text-sm text-green-600 font-semibold">Rs. {{ number_format($credit->paid_amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Outstanding Amount</dt>
                <dd class="mt-1 text-sm text-red-600 font-bold text-lg">Rs. {{ number_format($credit->outstanding_amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                <dd class="mt-1 text-sm font-semibold {{ $credit->due_date < now() && $credit->outstanding_amount > 0 ? 'text-red-600' : 'text-gray-900' }}">
                    {{ $credit->due_date->format('Y-m-d') }}
                    @if($credit->due_date < now() && $credit->outstanding_amount > 0)
                        <span class="ml-2 text-xs bg-red-100 text-red-800 px-2 py-1 rounded">OVERDUE</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
                    @if($credit->status === 'paid')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                    @elseif($credit->status === 'partial')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Partial</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Pending</span>
                    @endif
                </dd>
            </div>
            @if($credit->notes)
            <div class="col-span-2">
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $credit->notes }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <!-- Sale Details -->
    @if($credit->sale)
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Sale Details</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($credit->sale->items as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $item->item->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $item->quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">Rs. {{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">Rs. {{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

