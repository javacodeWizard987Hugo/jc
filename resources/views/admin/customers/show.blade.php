@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Customer Details</h1>
        <div class="flex gap-3">
            <a href="{{ route(($routePrefix ?? 'admin') . '.customers.edit', $customer) }}" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                Edit
            </a>
            <a href="{{ route(($routePrefix ?? 'admin') . '.customers.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Customer Information -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Customer Information</h2>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->phone ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->email ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->address ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Credit Limit</dt>
                    <dd class="mt-1 text-sm text-gray-900">Rs. {{ number_format($customer->credit_limit, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Outstanding Balance</dt>
                    <dd class="mt-1 text-sm font-semibold {{ $customer->outstanding_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                        Rs. {{ number_format($customer->outstanding_balance, 2) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        @if($customer->is_active)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Lock Mode</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->emi_lock_mode ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Emi Number</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->emi_number ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Sales Summary -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Sales Summary</h2>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total Sales</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->sales->count() }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total Sales Amount</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">
                        Rs. {{ number_format($customer->sales->sum('total_amount'), 2) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Active Credits</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $customer->credits->where('outstanding_amount', '>', 0)->count() }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Recent Sales -->
    @if($customer->sales->count() > 0)
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Recent Sales</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($customer->sales->take(10) as $sale)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $sale->invoice_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ucfirst($sale->payment_method) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($sale->status === 'completed')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ ucfirst($sale->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

