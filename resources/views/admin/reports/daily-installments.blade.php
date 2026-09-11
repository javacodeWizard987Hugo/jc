@extends('layouts.app')

@section('title', 'Daily Installment Details')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center print:hidden">
        <h1 class="text-3xl font-bold text-black">Daily Installment Report Details</h1>
        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                ЁЯЦия╕П Print Current View
            </button>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('admin.reports.daily-installments') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-black mb-1">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-black mb-1">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black">
                </div>
                <div>
                    <label for="customer_id" class="block text-sm font-medium text-black mb-1">Customer</label>
                    <select name="customer_id" id="customer_id" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black">
                        <option value="">All Customers</option>
                        @foreach($allCustomers as $customer)
                            <option value="{{ $customer->id }}" {{ $customerId == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="report_type" class="block text-sm font-medium text-black mb-1">Report Type</label>
                    <select name="report_type" id="report_type" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black">
                        <option value="all" {{ $reportType == 'all' ? 'selected' : '' }}>All Collection</option>
                        <option value="down_payment" {{ $reportType == 'down_payment' ? 'selected' : '' }}>Down Payments Only</option>
                        <option value="installment" {{ $reportType == 'installment' ? 'selected' : '' }}>Installments Only</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                        Filter
                    </button>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.reports.export', 'daily-installments') }}?format=pdf&print=1&start_date={{ $startDate }}&end_date={{ $endDate }}&report_type=all{{ $customerId ? '&customer_id=' . $customerId : '' }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                    ЁЯЦия╕П Print: Full Report
                </a>
                <a href="{{ route('admin.reports.export', 'daily-installments') }}?format=pdf&print=1&start_date={{ $startDate }}&end_date={{ $endDate }}&report_type=down_payment{{ $customerId ? '&customer_id=' . $customerId : '' }}" 
                   class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">
                    ЁЯЦия╕П Print: Down Payments
                </a>
                <a href="{{ route('admin.reports.export', 'daily-installments') }}?format=pdf&print=1&start_date={{ $startDate }}&end_date={{ $endDate }}&report_type=installment{{ $customerId ? '&customer_id=' . $customerId : '' }}" 
                   class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 text-sm">
                    ЁЯЦия╕П Print: Installment Collection
                </a>
                <a href="{{ route('admin.reports.export', 'daily-installments') }}?format=csv&start_date={{ $startDate }}&end_date={{ $endDate }}&report_type={{ $reportType }}{{ $customerId ? '&customer_id=' . $customerId : '' }}" 
                   class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                    ЁЯУе Export CSV
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Interest</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reportData as $row)
                    <tr class="hover:bg-gray-50 {{ $row['type'] == 'Down Payment' ? 'bg-blue-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $row['date'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black {{ $row['type'] == 'Down Payment' ? 'font-semibold' : '' }}">{{ $row['type'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $row['customer'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">#{{ $row['invoice'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $row['due_date'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $row['method'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($row['interest'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-black">Rs. {{ number_format($row['amount'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $row['notes'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-black">
                            <p>No transactions found in the selected date range.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-sm text-right text-black">Total</td>
                        <td class="px-6 py-4 text-sm text-right text-black">Rs. {{ number_format($reportData->sum('amount'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

