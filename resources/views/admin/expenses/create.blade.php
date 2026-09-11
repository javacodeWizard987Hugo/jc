@extends('layouts.app')

@section('title', 'Create Expense')

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
<div class="px-4 sm:px-6 lg:px-8 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create Expense</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route($routePrefix . '.expenses.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="expense_category_id" class="block text-sm font-medium text-gray-700">Category *</label>
                <select name="expense_category_id" id="expense_category_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                <input type="text" name="description" id="description" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                       value="{{ old('description') }}">
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount *</label>
                    <input type="number" step="0.01" name="amount" id="amount" required min="0"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           value="{{ old('amount') }}">
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method *</label>
                    <select name="payment_method" id="payment_method" required onchange="togglePaymentFields()"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                        <option value="cheque">Cheque</option>
                        <option value="credit">Credit</option>
                        <option value="other">Other</option>
                    </select>
                </div>
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
                               value="{{ old('credit_repay_date') }}" min="{{ date('Y-m-d') }}">
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
                                           value="{{ old('cheque_number') }}" placeholder="Enter cheque number">
                                </div>
                                <div>
                                    <label for="cheque_bank" class="block text-sm font-medium text-gray-700 mb-1">Bank Name *</label>
                                    <select name="cheque_bank" id="cheque_bank"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                                        <option value="">Select Bank</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank }}" {{ old('cheque_bank') == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="cheque_date" class="block text-sm font-medium text-gray-700 mb-1">Cheque Date *</label>
                                    <input type="date" name="cheque_date" id="cheque_date"
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                           value="{{ old('cheque_date') }}">
                                </div>
                                <div>
                                    <label for="cheque_repay_date" class="block text-sm font-medium text-gray-700 mb-1">Repay Date *</label>
                                    <input type="date" name="cheque_repay_date" id="cheque_repay_date"
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                           value="{{ old('cheque_repay_date') }}" min="{{ date('Y-m-d') }}">
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
                                           value="{{ old('account_holder_name') }}" placeholder="Enter account holder name">
                                </div>
                                <div>
                                    <label for="account_number" class="block text-sm font-medium text-gray-700 mb-1">Account Number *</label>
                                    <input type="text" name="account_number" id="account_number"
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                           value="{{ old('account_number') }}" placeholder="Enter account number">
                                </div>
                                <div>
                                    <label for="account_bank" class="block text-sm font-medium text-gray-700 mb-1">Bank *</label>
                                    <select name="account_bank" id="account_bank"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                                        <option value="">Select Bank</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank }}" {{ old('account_bank') == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="account_branch" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                                    <input type="text" name="account_branch" id="account_branch"
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                           value="{{ old('account_branch') }}" placeholder="Enter branch name">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="expense_date" class="block text-sm font-medium text-gray-700">Expense Date *</label>
                <input type="date" name="expense_date" id="expense_date" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                       value="{{ old('expense_date', now()->format('Y-m-d')) }}">
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-red-600 focus:ring-red-500" id="is_recurring">
                    <span class="ml-2 text-sm text-gray-700">Recurring Expense</span>
                </label>
            </div>

            <div class="mb-4" id="recurring_days_div" style="display: none;">
                <label for="recurring_days" class="block text-sm font-medium text-gray-700">Recurring Days</label>
                <input type="number" name="recurring_days" id="recurring_days" min="1"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                       value="{{ old('recurring_days') }}" placeholder="e.g., 30 for monthly">
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route($routePrefix . '.expenses.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Create Expense
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('is_recurring').addEventListener('change', function() {
    document.getElementById('recurring_days_div').style.display = this.checked ? 'block' : 'none';
});

function togglePaymentFields() {
    const paymentMethod = document.getElementById('payment_method').value;
    const creditFields = document.getElementById('credit_fields');
    const chequeFields = document.getElementById('cheque_fields');
    
    if (paymentMethod === 'credit') {
        creditFields.style.display = 'block';
        chequeFields.style.display = 'none';
        document.getElementById('credit_repay_date').required = true;
        // Clear cheque required attributes
        document.getElementById('cheque_number').required = false;
        document.getElementById('cheque_bank').required = false;
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
        document.getElementById('cheque_bank').required = true;
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
        document.getElementById('cheque_bank').required = false;
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

