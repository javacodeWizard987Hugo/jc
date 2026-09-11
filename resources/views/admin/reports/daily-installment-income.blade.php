@extends('layouts.app')

@section('title', 'Daily Installment Income Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-black">Daily Installment Income Report</h1>
        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                🖨️ Print
            </button>
            <a href="{{ route('admin.reports.export', 'daily-installment-income') }}?format=csv&start_date={{ $startDate }}&end_date={{ $endDate }}" 
               class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                📥 Export CSV
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('admin.reports.daily-installment-income') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Down Payments</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Installments</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Total Income</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dailyIncome as $day)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $day['date'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($day['down_payments'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($day['installments'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-black">Rs. {{ number_format($day['total'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-black">
                            <p>No income data found in the selected date range.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td class="px-6 py-4 text-sm text-black">Total</td>
                        <td class="px-6 py-4 text-sm text-right text-black">Rs. {{ number_format($dailyIncome->sum('down_payments'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black">Rs. {{ number_format($dailyIncome->sum('installments'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black">Rs. {{ number_format($dailyIncome->sum('total'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mb-6 mt-12">
        <h2 class="text-2xl font-bold text-black mb-4">Customer-wise Summary</h2>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Down Payments</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Installments</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customerSummary as $id => $summary)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black font-medium">{{ $summary['name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($summary['down_payments'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($summary['installments'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-black">Rs. {{ number_format($summary['total'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-black">
                            <p>No customer summary data found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td class="px-6 py-4 text-sm text-black">Total</td>
                        <td class="px-6 py-4 text-sm text-right text-black">Rs. {{ number_format($customerSummary->sum('down_payments'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black">Rs. {{ number_format($customerSummary->sum('installments'), 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-black">Rs. {{ number_format($customerSummary->sum('total'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
