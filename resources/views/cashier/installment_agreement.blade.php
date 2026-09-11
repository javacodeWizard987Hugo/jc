@extends('layouts.app')

@section('title', 'Installment Agreement')

@section('content')
@php
    $installment = $sale->installmentAgreement;
    $isAdmin = auth()->user()->isAdmin();
@endphp

<div class="max-w-5xl mx-auto px-4 py-6">

    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md border-2 border-gray-200 p-6 mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🏦 Installment Agreement</h1>
            <p class="text-sm text-gray-600">Installment Payment Agreement</p>
        </div>
        <div class="text-right">
            <p class="text-sm font-semibold text-gray-700">Agreement No</p>
            <p class="text-lg font-bold text-red-600">#{{ $sale->invoice_number }}</p>
        </div>
    </div>

    <!-- Applicant Details -->
    <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-blue-900 mb-4">👤 Applicant Details</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <p><strong>Full Name:</strong> {{ $sale->customer->name }}</p>
            <p><strong>NIC:</strong> {{ $sale->customer->nic ?? 'N/A' }}</p>
            <p><strong>Mobile:</strong> {{ $sale->customer->phone }}</p>
            <p><strong>Address:</strong> {{ $sale->customer->address ?? 'N/A' }}</p>
        </div>
    </div>

    <!-- Loan & Device Details -->
    <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-green-900 mb-4">💰 Loan & Device Details</h3>

        <table class="w-full text-sm">
            <tr>
                <td class="font-semibold py-1">Devices</td>
                <td>
                    @foreach($sale->items as $item)
                        {{ $item->item->name }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <td class="font-semibold py-1">Total Price</td>
                <td>Rs. {{ number_format($sale->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="font-semibold py-1">Down Payment</td>
                <td>Rs. {{ number_format($installment->down_payment_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="font-semibold py-1">Loan Amount</td>
                <td>Rs. {{ number_format($installment->balance_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="font-semibold py-1">Monthly Installment</td>
                <td>Rs. {{ number_format($installment->monthly_installment_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="font-semibold py-1">Installment Date</td>
                <td>{{ $installment->due_day_of_month }} of every month</td>
            </tr>
            <tr>
                <td class="font-semibold py-1">Tenure</td>
                <td>{{ $installment->number_of_installments }} Months</td>
            </tr>
        </table>
    </div>

<!-- Guarantor Details -->
<div class="bg-purple-50 border-2 border-purple-200 rounded-lg p-6 mb-6">
    <h3 class="text-lg font-bold text-purple-900 mb-4">🧑‍⚖️ Guarantor Details</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <p><strong>Name:</strong> {{ $installment->guarantor_name ?? 'N/A' }}</p>
        <p><strong>NIC:</strong> {{ $installment->guarantor_nic ?? 'N/A' }}</p>
        <p><strong>Mobile:</strong> {{ $installment->guarantor_mobile_number ?? 'N/A' }}</p>
        <p><strong>Address:</strong> {{ $installment->guarantor_address ?? 'N/A' }}</p>
    </div>
</div>


<div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-6 mt-8 no-print">
    <h3 class="text-lg font-bold text-gray-800 mb-4">📎 View NIC Documents</h3>
    <div class="grid grid-cols-2 gap-4">
        @if($installment->customer_nic_front)
            <a href="{{ asset('storage/'.$installment->customer_nic_front) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded text-center">Customer NIC Front</a>
        @endif
        @if($installment->customer_nic_back)
            <a href="{{ asset('storage/'.$installment->customer_nic_back) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded text-center">Customer NIC Back</a>
        @endif
        @if($installment->guarantor_nic_front)
            <a href="{{ asset('storage/'.$installment->guarantor_nic_front) }}" target="_blank" class="px-4 py-2 bg-green-600 text-white rounded text-center">Guarantor NIC Front</a>
        @endif
        @if($installment->guarantor_nic_back)
            <a href="{{ asset('storage/'.$installment->guarantor_nic_back) }}" target="_blank" class="px-4 py-2 bg-green-600 text-white rounded text-center">Guarantor NIC Back</a>
        @endif
    </div>
</div>



    <!-- Date -->
    <div class="text-right text-sm font-semibold text-gray-700 mb-6">
        Date: {{ now()->format('Y-m-d') }}
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-3 justify-end no-print">

       

        <!--<a href="{{ route($isAdmin ? 'admin.installment-agreement.download' : 'cashier.installment-agreement.download', $sale->id) }}"
           class="px-5 py-2 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700">
            ⬇️ Download PDF
        </a>-->

        <!--<a href="{{ route($isAdmin ? 'admin.installment-agreement.edit' : 'cashier.installment-agreement.edit', $sale->id) }}"
           class="px-5 py-2 bg-yellow-500 text-white rounded-lg font-bold hover:bg-yellow-600">
            ✏️ Edit Agreement
        </a>-->

        {{-- ✅ SAFE Generate Invoice Button --}}
        @php
            $invoiceRoute = $isAdmin ? 'admin.sales.print' : 'cashier.sales.print';
        @endphp

        @if(\Illuminate\Support\Facades\Route::has($invoiceRoute))
            <a href="{{ route($invoiceRoute, $sale->id) }}"
               target="_blank"
               class="px-5 py-2 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700">
                🧾 Generate Invoice
            </a>
        @endif

        
        {{-- ✅ Print Invoice (ONLY for Installment Payments) --}}
        @if($sale->payment_method === 'installment')
            <a href="{{ route(
                $isAdmin ? 'admin.invoice.print' : 'cashier.invoice.print',
                $sale->id
            ) }}"
            target="_blank"
            class="px-5 py-2 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700">
                🧾 Print Invoice
            </a>
        @endif

            <a href="{{ route($isAdmin ? 'admin.installment-agreement.print' : 'cashier.installment-agreement.print', $sale->id) }}"
            target="_blank"
            style="background:#4f46e5;color:#fff;padding:10px 20px;
                    border-radius:8px;font-weight:700;text-decoration:none;">
                📄 Print Agreement
            </a>


        <a href="{{ route($isAdmin ? 'admin.pos' : 'cashier.pos') }}"
           class="px-5 py-2 bg-gray-500 text-white rounded-lg font-bold hover:bg-gray-600">
            ← Back to POS
        </a>
    </div>

</div>
@endsection

@push('styles')
<style>
@media print {
    .no-print { display: none; }
    body { -webkit-print-color-adjust: exact; }
}
</style>
@endpush
