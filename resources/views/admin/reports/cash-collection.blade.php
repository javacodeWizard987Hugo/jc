@extends('layouts.app')

@section('title', 'Cash Collection Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center print:hidden">
        <h1 class="text-3xl font-bold text-black">Cash Collection Report</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.reports.cash-collection', ['months' => $selectedMonthCount, 'as_of_date' => $asOfDate, 'format' => 'pdf']) }}" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-bold">
                📥 Download PDF
            </a>
            <a href="{{ route('admin.reports.cash-collection-print', ['months' => $selectedMonthCount, 'as_of_date' => $asOfDate, 'print' => 1]) }}" target="_blank" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-bold">
                🖨️ Print Report
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6 print:hidden">
        <form method="GET" action="{{ route('admin.reports.cash-collection') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
            <div>
                <label for="months" class="block text-sm font-medium text-gray-700">Forecast Period (Months)</label>
                <select name="months" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    @for($i=1; $i<=12; $i++)
                        <option value="{{ $i }}" {{ $selectedMonthCount == $i ? 'selected' : '' }}>{{ $i }} Month{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-bold">Generate Projection</button>
        </form>
    </div>

    <div class="space-y-6">
        @foreach($upcomingCollections as $monthKey => $data)
        @php
            $actualReceived = $receivedData->get($monthKey)->total ?? 0;
            $balance = $data['expected'] - $actualReceived;
            $isPast = Carbon\Carbon::parse($monthKey)->startOfMonth()->lt(now()->startOfMonth());
        @endphp
        <div class="bg-white shadow rounded-lg overflow-hidden border {{ $isPast ? 'border-gray-200' : 'border-blue-200' }}">
            <div class="{{ $isPast ? 'bg-gray-100' : 'bg-blue-50' }} px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">{{ $data['month'] }} {{ $isPast ? '(Previous Month Data)' : '(Upcoming Projection)' }}</h3>
                <div class="flex gap-8">
                    <div class="text-right">
                        <p class="text-xs text-gray-500 font-bold uppercase">Expected</p>
                        <p class="text-lg font-bold text-black">Rs. {{ number_format($data['expected'], 2) }}</p>
                    </div>
                    @if($isPast || $monthKey == now()->format('Y-m'))
                    <div class="text-right">
                        <p class="text-xs text-gray-500 font-bold uppercase">Actual Received</p>
                        <p class="text-lg font-bold text-green-600">Rs. {{ number_format($actualReceived, 2) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 font-bold uppercase">Balance</p>
                        <p class="text-lg font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-700' }}">Rs. {{ number_format($balance, 2) }}</p>
                    </div>
                    @endif
                </div>
            </div>
            <div class="p-0 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Phone</th>
                            <th class="px-6 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Due Date</th>
                            <th class="px-6 py-2 text-right text-[10px] font-bold text-gray-500 uppercase">Expected Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['customers'] as $cust)
                        <tr>
                            <td class="px-6 py-2 whitespace-nowrap text-xs text-black font-semibold">{{ $cust['customer'] }}</td>
                            <td class="px-6 py-2 whitespace-nowrap text-xs text-gray-600">{{ $cust['phone'] }}</td>
                            <td class="px-6 py-2 whitespace-nowrap text-xs text-gray-600">{{ $cust['due_date'] }}</td>
                            <td class="px-6 py-2 whitespace-nowrap text-xs text-right text-black">Rs. {{ number_format($cust['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
