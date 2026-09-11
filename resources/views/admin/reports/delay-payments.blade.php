@extends('layouts.app')

@section('title', 'Delay Payments Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Delay Payments Report</h1>
            <p class="mt-2 text-sm text-gray-700">A list of all delay charge payments received.</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mt-8 bg-white shadow sm:rounded-lg p-6">
        <form action="{{ route('admin.reports.delay-payments') }}" method="GET" class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
            </div>
            <div class="sm:col-span-1 flex items-end">
                <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Filter Results
                </button>
            </div>
        </form>
    </div>

    {{-- Summary --}}
    <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Delay Charges Collected</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">Rs. {{ number_format($totalFine, 2) }}</dd>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Customer</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Invoice #</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">EMI Number</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Payment Method</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Fine Paid</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($payments as $payment)
                                <tr>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">{{ $payment->agreement->customer->name }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $payment->agreement->sale->invoice_number }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $payment->agreement->combined_emi_numbers }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $payment->payment_method }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right font-bold text-red-600">Rs. {{ number_format($payment->fine_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-4 text-sm text-center text-gray-500">No delay payments found for the selected period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($payments->count() > 0)
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <th colspan="5" class="px-3 py-3.5 text-right text-sm font-bold text-gray-900">TOTAL</th>
                                    <th class="px-3 py-3.5 text-right text-sm font-bold text-red-600">Rs. {{ number_format($totalFine, 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
