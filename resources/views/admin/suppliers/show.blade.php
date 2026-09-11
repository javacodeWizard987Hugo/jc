@extends('layouts.app')

@section('title', 'Supplier Details - ' . $supplier->name)

@section('content')
@php
    $banks = [
        'Amana Bank PLC',
        'Bank of Ceylon',
        'Bank of China Ltd.',
        'Cargills Bank PLC',
        'Citibank, N.A.',
        'Commercial Bank of Ceylon PLC',
        'Deutsche Bank AG (Colombo Branch)',
        'DFCC Bank PLC',
        'Habib Bank Ltd.',
        'Hatton National Bank PLC',
        'Indian Bank',
        'Indian Overseas Bank',
        'MCB Bank Ltd',
        'National Development Bank PLC',
        'Nations Trust Bank PLC',
        'Pan Asia Banking Corporation PLC',
        'People\'s Bank',
        'Public Bank Berhad (Colombo Branch)',
        'Sampath Bank PLC',
        'Seylan Bank PLC',
        'Standard Chartered Bank',
        'State Bank of India (Colombo Branch)',
        'Union Bank of Colombo PLC'
    ];
    $routePrefix = $routePrefix ?? (request()->route()->getName() && strpos(request()->route()->getName(), 'cashier.') === 0 ? 'cashier' : 'admin');
@endphp
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route($routePrefix . '.suppliers.index') }}" class="text-red-600 hover:text-red-900 mb-4 inline-block">← Back to Suppliers</a>
        <h1 class="text-3xl font-bold text-gray-900">{{ $supplier->name }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Supplier Information</h2>
            <dl class="space-y-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                    <dd class="text-sm text-gray-900">{{ $supplier->contact_person ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="text-sm text-gray-900">{{ $supplier->phone ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="text-sm text-gray-900">{{ $supplier->email ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                    <dd class="text-sm text-gray-900">{{ $supplier->address ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Payment Terms</dt>
                    <dd class="text-sm text-gray-900">{{ $supplier->payment_terms ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Outstanding Balance</dt>
                    <dd class="text-sm font-semibold text-red-600">Rs. {{ number_format($supplier->outstanding_balance, 2) }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Record Payment</h2>
            <form action="{{ route($routePrefix . '.suppliers.payments', $supplier) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="invoice_amount" class="block text-sm font-medium text-gray-700">Invoice Amount *</label>
                        <input type="number" step="0.01" name="invoice_amount" id="invoice_amount" required min="0"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="paid_amount" class="block text-sm font-medium text-gray-700">Paid Amount *</label>
                        <input type="number" step="0.01" name="paid_amount" id="paid_amount" required min="0"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method *</label>
                        <select name="payment_method" id="payment_method" required onchange="togglePaymentFields()"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit">Credit</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Credit Payment Details (for Credit payment method) -->
                    <div class="mb-6 transition-all duration-300 ease-in-out" id="credit_fields" style="display: none;">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="ml-2 text-lg font-semibold text-gray-800">Credit Payment Details</h3>
                            </div>
                            <div>
                                <label for="credit_repay_date" class="block text-sm font-medium text-gray-700 mb-1">Repay Date *</label>
                                <input type="date" name="credit_repay_date" id="credit_repay_date"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white"
                                       min="{{ date('Y-m-d') }}">
                                <p class="mt-1 text-xs text-gray-500">Enter the date when this credit payment should be repaid</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cheque Payment Details (for Cheque payment method) -->
                    <div class="mb-6 transition-all duration-300 ease-in-out" id="cheque_fields" style="display: none;">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-5">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="ml-2 text-lg font-semibold text-gray-800">Cheque Payment Details</h3>
                            </div>
                            
                            <div class="space-y-4">
                                <!-- Cheque Information -->
                                <div class="bg-white rounded-md p-4 border border-gray-200">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                        <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Cheque Information
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="cheque_number" class="block text-sm font-medium text-gray-700 mb-1">Cheque Number *</label>
                                            <input type="text" name="cheque_number" id="cheque_number"
                                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                                   placeholder="Enter cheque number">
                                        </div>
                                        <div>
                                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">Bank Name *</label>
                                            <select name="bank_name" id="bank_name"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                                                <option value="">Select Bank</option>
                                                @foreach($banks as $bank)
                                                    <option value="{{ $bank }}">{{ $bank }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label for="cheque_date" class="block text-sm font-medium text-gray-700 mb-1">Cheque Date *</label>
                                            <input type="date" name="cheque_date" id="cheque_date"
                                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                                        </div>
                                        <div>
                                            <label for="cheque_repay_date" class="block text-sm font-medium text-gray-700 mb-1">Repay Date *</label>
                                            <input type="date" name="cheque_repay_date" id="cheque_repay_date"
                                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                                   min="{{ date('Y-m-d') }}">
                                            <p class="mt-1 text-xs text-gray-500">Date when cheque should be repaid</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Receiver Account Details -->
                                <div class="bg-white rounded-md p-4 border border-gray-200">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                        <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Receiver Account Details
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="account_holder_name" class="block text-sm font-medium text-gray-700 mb-1">Account Holder Name *</label>
                                            <input type="text" name="account_holder_name" id="account_holder_name"
                                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                                   placeholder="Enter account holder name">
                                        </div>
                                        <div>
                                            <label for="account_number" class="block text-sm font-medium text-gray-700 mb-1">Account Number *</label>
                                            <input type="text" name="account_number" id="account_number"
                                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                                   placeholder="Enter account number">
                                        </div>
                                        <div>
                                            <label for="account_bank" class="block text-sm font-medium text-gray-700 mb-1">Bank *</label>
                                            <select name="account_bank" id="account_bank"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                                                <option value="">Select Bank</option>
                                                @foreach($banks as $bank)
                                                    <option value="{{ $bank }}">{{ $bank }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label for="account_branch" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                                            <input type="text" name="account_branch" id="account_branch"
                                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                                   placeholder="Enter branch name">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Payment History</h2>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($supplier->supplierPayments as $payment)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $payment->payment_date?->format('Y-m-d') ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">Rs. {{ number_format($payment->invoice_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">Rs. {{ number_format($payment->paid_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">Rs. {{ number_format($payment->outstanding_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No payments recorded</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function togglePaymentFields() {
    const paymentMethod = document.getElementById('payment_method').value;
    const creditFields = document.getElementById('credit_fields');
    const chequeFields = document.getElementById('cheque_fields');
    
    // Add smooth transition
    if (paymentMethod === 'credit') {
        creditFields.style.display = 'block';
        setTimeout(() => creditFields.style.opacity = '1', 10);
        chequeFields.style.display = 'none';
        chequeFields.style.opacity = '0';
        document.getElementById('credit_repay_date').required = true;
        // Clear cheque required attributes
        document.getElementById('cheque_number').required = false;
        document.getElementById('bank_name').required = false;
        document.getElementById('cheque_date').required = false;
        document.getElementById('cheque_repay_date').required = false;
        document.getElementById('account_holder_name').required = false;
        document.getElementById('account_number').required = false;
        document.getElementById('account_bank').required = false;
        document.getElementById('account_branch').required = false;
    } else if (paymentMethod === 'cheque') {
        creditFields.style.display = 'none';
        creditFields.style.opacity = '0';
        chequeFields.style.display = 'block';
        setTimeout(() => chequeFields.style.opacity = '1', 10);
        document.getElementById('credit_repay_date').required = false;
        // Set cheque required attributes
        document.getElementById('cheque_number').required = true;
        document.getElementById('bank_name').required = true;
        document.getElementById('cheque_date').required = true;
        document.getElementById('cheque_repay_date').required = true;
        document.getElementById('account_holder_name').required = true;
        document.getElementById('account_number').required = true;
        document.getElementById('account_bank').required = true;
        document.getElementById('account_branch').required = true;
    } else {
        creditFields.style.display = 'none';
        creditFields.style.opacity = '0';
        chequeFields.style.display = 'none';
        chequeFields.style.opacity = '0';
        document.getElementById('credit_repay_date').required = false;
        // Clear cheque required attributes
        document.getElementById('cheque_number').required = false;
        document.getElementById('bank_name').required = false;
        document.getElementById('cheque_date').required = false;
        document.getElementById('cheque_repay_date').required = false;
        document.getElementById('account_holder_name').required = false;
        document.getElementById('account_number').required = false;
        document.getElementById('account_bank').required = false;
        document.getElementById('account_branch').required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set initial opacity
    const creditFields = document.getElementById('credit_fields');
    const chequeFields = document.getElementById('cheque_fields');
    if (creditFields) creditFields.style.opacity = '0';
    if (chequeFields) chequeFields.style.opacity = '0';
    
    togglePaymentFields();
});
</script>
@endsection

