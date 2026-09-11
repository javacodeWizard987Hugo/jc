@extends('layouts.app')

@section('title', 'Disconnect Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center print:hidden">
        <h1 class="text-3xl font-bold text-black">Disconnect Report</h1>
        <div class="flex gap-2">
           <!-- <a href="{{ route('admin.reports.disconnect', ['start_date' => $startDate, 'end_date' => $endDate, 'format' => 'pdf']) }}" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-bold">
                📥 Download PDF
            </a>-->
            <a href="{{ route('admin.reports.disconnect-print', ['start_date' => $startDate, 'end_date' => $endDate, 'report_type' => $reportType, 'print' => 1]) }}" target="_red" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-black-700 font-bold">
                🖨️ Print Report
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6 print:hidden">
         <form method="GET" action="{{ route('admin.reports.disconnect') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search (Customer/EMI)</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Phone or EMI..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            <div>
                <label for="report_type" class="block text-sm font-medium text-gray-700">Report Range</label>
                <select name="report_type" id="report_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    <option value="filtered" {{ $reportType == 'filtered' ? 'selected' : '' }}>By Date Range</option>
                    <option value="all_time" {{ $reportType == 'all_time' ? 'selected' : '' }}>All Disconnected (Historical)</option>
                </select>
            </div>
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
           <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-bold">Filter</button>
                <a href="{{ route('admin.reports.disconnect') }}" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 font-bold text-center">Clear</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white shadow rounded-lg p-4 border-l-4 border-red-600">
            <p class="text-xs text-gray-500 font-bold uppercase">Disconnected Devices</p>
            <p class="text-2xl font-bold text-black">{{ $withinMonthCount }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 border-l-4 border-orange-600">
            <p class="text-xs text-gray-500 font-bold uppercase">Current Due of Lock</p>
            <p class="text-2xl font-bold text-black">Rs. {{ number_format($lockBalance, 2) }}</p>
        </div>
        <!--<div class="bg-white shadow rounded-lg p-4 border-l-4 border-green-600">
            <p class="text-xs text-gray-500 font-bold uppercase">Current Due of Unlock</p>
            <p class="text-2xl font-bold text-black">Rs. {{ number_format($unlockBalance, 2) }}</p>
        </div>-->
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4 text-black">Disconnected Device List</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EMI Number</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disconnected Days</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guarantor</th>
            <!--
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delay Days</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delay Charge</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining Charge</th>
            -->

            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Premiums</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Balance</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200">
        @foreach($disconnected as $agreement)
        <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                {{ $agreement->combined_emi_numbers }}<br>
                <span class="text-xs text-gray-500">Invoice: {{ $agreement->sale->invoice_number }}</span>
            </td>

            <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                {{ $agreement->customer->name }}<br>
                <span class="text-xs text-gray-500">{{ $agreement->customer->phone }}</span>
            </td>


                        
            <td class="px-6 py-4 whitespace-nowrap text-sm text-black text-center">
                <span class="px-2 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                    {{ (int) $agreement->disconnected_at->startOfDay()->diffInDays(now()->startOfDay()) }} Days
                </span>
            </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                            {{ $agreement->guarantor_name ?? 'N/A' }}<br>
                            <span class="text-xs text-gray-500">{{ $agreement->guarantor_mobile_number }}</span>
                        </td>

            <!--
            <td class="px-6 py-4 whitespace-nowrap text-sm text-black text-center">
                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $agreement->delay_days > 0 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $agreement->delay_days }} Days
                </span>
            </td>

            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-bold">
                Rs. {{ number_format($agreement->calculated_fine, 2) }}
            </td>

            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-700 font-extrabold">
                Rs. {{ number_format($agreement->remaining_fine, 2) }}
            </td>
            -->

            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-bold">
                Rs. {{ number_format($agreement->overdue_installment_amount, 2) }}
            </td>

            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                    DISCONNECTED
                </span>
            </td>

            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black font-bold">
                Rs. {{ number_format($agreement->balance_amount, 2) }}
            </td>

            <td class="px-6 py-4 whitespace-nowrap text-sm text-center flex flex-col gap-1">
                @if($agreement->disconnected_at && !$agreement->unlocked_at)
                    <form action="{{ route('admin.installments.unlock', $agreement) }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="w-full bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700"
                            onclick="return confirm('Ensure all charges and balance are cleared before unlocking.')">
                            Unlock
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.installments.disconnect', $agreement) }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="w-full bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">
                            Disconnect
                        </button>
                    </form>
                @endif

                @if($agreement->payments->count() > 0)
                    <a href="{{ route('admin.installments.payments.disconnect-receipt', $agreement->payments->sortByDesc('created_at')->first()) }}"
                       target="_blank"
                       class="bg-orange-600 text-white px-3 py-1 rounded text-xs hover:bg-orange-700">
                        Print Last Receipt
                    </a>
                @endif

                <a href="{{ route('admin.installments.show', $agreement) }}"
                   class="bg-yellow-600 text-white px-3 py-1 rounded text-xs hover:bg-yellow-700">
                    Record Payment
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4 text-black">Unlocked Device List</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EMI Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guarantor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unlocked At</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Lock Duration</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Due Premiums</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Balance</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($unlocked as $agreement)
                    <tr>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $agreement->combined_emi_numbers }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">{{ $agreement->customer->name }}
                        <br>
                            <span class="text-xs text-gray-500">
                                 <td>{{ $agreement->customer->phone }}</td>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                            {{ $agreement->guarantor_name ?? 'N/A' }}<br>
                            <span class="text-xs text-gray-500">{{ $agreement->guarantor_mobile_number }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                            {{ $agreement->unlocked_at && !$agreement->disconnected_at ? $agreement->unlocked_at->format('Y-m-d H:i') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-black">
                            @if($agreement->disconnected_at && $agreement->unlocked_at)
                                <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                    {{ (int) $agreement->disconnected_at->diffInDays($agreement->unlocked_at) }} Days
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600 font-bold">Rs. {{ number_format($agreement->overdue_installment_amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. {{ number_format($agreement->balance_amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center flex flex-col gap-1">
                            <form action="{{ route('admin.installments.disconnect', $agreement) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Block Again</button>
                            </form>
                            <a href="{{ route('admin.customers.edit', $agreement->customer_id) }}" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">Edit Customer</a>
                            <a href="{{ route('admin.installment-agreement.edit', $agreement->sale_id) }}" class="bg-purple-600 text-white px-3 py-1 rounded text-xs hover:bg-purple-700">Edit Agreement</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
