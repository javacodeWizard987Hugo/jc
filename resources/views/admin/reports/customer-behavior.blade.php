@extends('layouts.app')

@section('title', 'Customer Behavior Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center print:hidden">
        <div>
            <h1 class="text-3xl font-bold text-black">Customer Behavior Report</h1>
            <p class="text-sm text-gray-500">Analysis of customer purchase history and payment reliability.</p>
        </div>
        <div class="flex gap-2">
           <!-- <a href="{{ route('admin.reports.customer-behavior', ['format' => 'pdf']) }}" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-bold">
                📥 Download PDF
            </a>-->
            <a href="{{ route('admin.reports.customer-behavior-print', ['print' => 1, 'status' => $statusFilter, 'search' => $search]) }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-bold">
                🖨️ Print Report
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6 print:hidden">
        <form method="GET" action="{{ route('admin.reports.customer-behavior') }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Customer</label>
                <input type="text" name="search" id="search" value="{{ $search }}" 
                       placeholder="Name, Phone or NIC..."
                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="w-48">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                <select name="status" id="status" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">All Behaviors</option>
                    <option value="on_time" {{ $statusFilter == 'on_time' ? 'selected' : '' }}>On-time Payments</option>
                    <option value="delayed" {{ $statusFilter == 'delayed' ? 'selected' : '' }}>Delayed Payments</option>
                </select>
            </div>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-bold">
                Search
            </button>
            <a href="{{ route('admin.reports.customer-behavior') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 font-bold">
                Reset
            </a>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Installments</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Payment Ratio</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">On-time/Delayed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Lock Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Behavior</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($customers as $customer)
                @if($customer->sales_count > 0)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-gray-900">{{ $customer->name }}</div>
                        <div class="text-xs text-gray-500">{{ $customer->phone }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-black font-semibold">Rs. {{ number_format($customer->sales_sum_total_amount, 2) }}</div>
                        <div class="text-xs text-gray-500">{{ $customer->sales_count }} Total Bills</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $customer->installment_agreements_count }} Active Agreements</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1 max-w-[100px] mx-auto">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $customer->payment_ratio }}%"></div>
                        </div>
                        <span class="text-xs font-semibold text-gray-700">{{ number_format($customer->payment_ratio, 1) }}% Paid</span>
                    </td>
                     <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-sm font-semibold">
                            <span class="text-green-600" title="Payments made on or before due date">{{ $customer->on_time_premiums }} On-time</span>
                            <span class="text-gray-400 mx-1">|</span>
                            <span class="text-red-600" title="Payments made after due date or currently overdue">{{ $customer->delayed_premiums }} Delayed</span>
                        </div>
                        <div class="text-[10px] text-gray-400 mt-1 uppercase tracking-wider font-bold">Payment History</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-sm">
                            @if($customer->total_lock_duration > 0)
                                <span class="text-orange-600 font-semibold">{{ $customer->total_lock_duration }} days locked</span>
                                @if($customer->is_locked_now)
                                    <br><span class="text-xs bg-red-100 text-red-800 px-1 rounded">Currently Locked</span>
                                @endif
                            @else
                                <span class="text-green-600">Never Locked</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase border-2 {{ $customer->behavior_class }} border-current">
                            {{ $customer->behavior }}
                        </span>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
