@extends('layouts.app')

@section('title', 'Supplier Ledger')

@section('content')
@php
    $routePrefix = auth()->user()->isAdmin() ? 'admin' : 'cashier';
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
@endphp
<div class="px-4 sm:px-6 lg:px-8">
    <!-- Top Bar -->
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">📘 Supplier Ledger</h1>
        <div class="flex gap-3">
             <a href="{{ route('admin.reports.supplier-ledger-pdf', ['print' => 1]) }}" target="_blank" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-bold">
                🖨️ Print Report
            </a>
          
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route($routePrefix . '.reports.supplier-ledger') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">🔍 Search Supplier</label>
                <input type="text" name="search" id="search" value="{{ $search ?? '' }}" 
                       placeholder="Name / Phone"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">📅 Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? '' }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">📅 End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? '' }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="flex items-end">
                <label class="flex items-center">
                    <input type="checkbox" name="show_outstanding" value="1" {{ $showOnlyOutstanding ? 'checked' : '' }}
                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700">⚠️ Show only with outstanding balance</span>
                </label>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Suppliers Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Purchases</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Transaction</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($suppliers as $supplier)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $supplier['name'] }}</div>
                        @if($supplier['contact_person'])
                            <div class="text-sm text-gray-500">{{ $supplier['contact_person'] }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        Rs. {{ number_format($supplier['total_purchases'], 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">
                        Rs. {{ number_format($supplier['total_paid'], 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $supplier['outstanding_balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        Rs. {{ number_format($supplier['outstanding_balance'], 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $supplier['last_transaction'] ? \Carbon\Carbon::parse($supplier['last_transaction'])->format('Y-m-d') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route($routePrefix . '.reports.supplier-ledger', ['supplier_id' => $supplier['id']]) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium">👁 View Ledger</a>
                            <span class="text-gray-300">|</span>
                            <button onclick="openRecordPaymentModal({{ $supplier['id'] }}, '{{ $supplier['name'] }}')" 
                                    class="text-green-600 hover:text-green-800 font-medium">💰 Record Payment</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <div class="text-4xl mb-2">📘</div>
                        <p>No suppliers found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Record Payment Modal -->
<div id="recordPaymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">💰 Record Payment</h3>
                <button onclick="closeRecordPaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="paymentForm" action="{{ route($routePrefix . '.suppliers.payments', ['supplier' => '__SUPPLIER_ID__']) }}" method="POST" onsubmit="return validatePaymentFormList(event)">
                @csrf
                <input type="hidden" id="modal_supplier_id" name="supplier_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                        <input type="text" id="modal_supplier_name" readonly 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                    </div>
                    <div>
                        <label for="modal_invoice_amount" class="block text-sm font-medium text-gray-700 mb-1">Invoice Amount *</label>
                        <input type="number" step="0.01" name="invoice_amount" id="modal_invoice_amount" required min="0"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="modal_paid_amount" class="block text-sm font-medium text-gray-700 mb-1">Paid Amount *</label>
                        <input type="number" step="0.01" name="paid_amount" id="modal_paid_amount" required min="0"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="modal_payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                        <select name="payment_method" id="modal_payment_method" required onchange="toggleModalPaymentFields()"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit">Credit</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="modal_payment_date" class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
                        <input type="date" name="payment_date" id="modal_payment_date" 
                               value="{{ date('Y-m-d') }}" required
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div id="modal_credit_fields" style="display: none;">
                        <label for="modal_credit_repay_date" class="block text-sm font-medium text-gray-700 mb-1">Repay Date *</label>
                        <input type="date" name="credit_repay_date" id="modal_credit_repay_date"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div id="modal_cheque_fields" style="display: none;" class="space-y-2">
                        <div>
                            <label for="modal_cheque_number" class="block text-sm font-medium text-gray-700 mb-1">Cheque Number *</label>
                            <input type="text" name="cheque_number" id="modal_cheque_number"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="modal_bank_name" class="block text-sm font-medium text-gray-700 mb-1">Bank Name *</label>
                            <select name="bank_name" id="modal_bank_name"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank }}">{{ $bank }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="modal_cheque_date" class="block text-sm font-medium text-gray-700 mb-1">Cheque Date *</label>
                            <input type="date" name="cheque_date" id="modal_cheque_date"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="modal_cheque_repay_date" class="block text-sm font-medium text-gray-700 mb-1">Repay Date *</label>
                            <input type="date" name="cheque_repay_date" id="modal_cheque_repay_date"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                                   min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <!-- Account Details - Always visible -->
                    <div class="space-y-2 border-t pt-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Account Details</h4>
                        <div>
                            <label for="modal_account_holder_name" class="block text-sm font-medium text-gray-700 mb-1">Account Holder Name</label>
                            <input type="text" name="account_holder_name" id="modal_account_holder_name"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="modal_account_number" class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                            <input type="text" name="account_number" id="modal_account_number"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="modal_account_bank" class="block text-sm font-medium text-gray-700 mb-1">Bank</label>
                            <select name="account_bank" id="modal_account_bank"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank }}">{{ $bank }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="modal_account_branch" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                            <input type="text" name="account_branch" id="modal_account_branch"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>
                    <div>
                        <label for="modal_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" id="modal_notes" rows="2"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeRecordPaymentModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        ❌ Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        ✅ Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function validatePaymentFormList(event) {
    const method = document.getElementById('modal_payment_method').value;
    
    // Ensure required attributes are set before validation
    toggleModalPaymentFields();
    
    // Custom validation for cheque payments
    if (method === 'cheque') {
        const chequeDate = document.getElementById('modal_cheque_date');
        if (!chequeDate || !chequeDate.value) {
            alert('Cheque Date is required for cheque payments.');
            if (chequeDate) {
                chequeDate.focus();
                chequeDate.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            event.preventDefault();
            return false;
        }
    }
    
    // Let HTML5 validation handle the rest
    const form = event.target;
    if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        form.reportValidity();
        return false;
    }
    
    return true;
}

function openRecordPaymentModal(supplierId, supplierName) {
    document.getElementById('modal_supplier_id').value = supplierId;
    document.getElementById('modal_supplier_name').value = supplierName;
    const form = document.getElementById('paymentForm');
    form.action = form.action.replace('__SUPPLIER_ID__', supplierId);
    document.getElementById('recordPaymentModal').classList.remove('hidden');
    toggleModalPaymentFields();
}

function closeRecordPaymentModal() {
    document.getElementById('recordPaymentModal').classList.add('hidden');
    document.getElementById('paymentForm').reset();
}

function toggleModalPaymentFields() {
    const method = document.getElementById('modal_payment_method').value;
    const creditFields = document.getElementById('modal_credit_fields');
    const chequeFields = document.getElementById('modal_cheque_fields');
    const accountHolder = document.getElementById('modal_account_holder_name');
    const accountNumber = document.getElementById('modal_account_number');
    const accountBank = document.getElementById('modal_account_bank');
    const accountBranch = document.getElementById('modal_account_branch');
    
    if (method === 'credit') {
        creditFields.style.display = 'block';
        chequeFields.style.display = 'none';
        document.getElementById('modal_credit_repay_date').required = true;
        if (accountHolder) accountHolder.required = true;
        if (accountNumber) accountNumber.required = true;
        if (accountBank) accountBank.required = true;
        if (accountBranch) accountBranch.required = true;
        // Clear cheque specific required
        document.getElementById('modal_cheque_number').required = false;
        document.getElementById('modal_bank_name').required = false;
        document.getElementById('modal_cheque_date').required = false;
        document.getElementById('modal_cheque_repay_date').required = false;
    } else if (method === 'cheque') {
        creditFields.style.display = 'none';
        chequeFields.style.display = 'block';
        document.getElementById('modal_cheque_number').required = true;
        document.getElementById('modal_bank_name').required = true;
        document.getElementById('modal_cheque_date').required = true;
        document.getElementById('modal_cheque_repay_date').required = true;
        if (accountHolder) accountHolder.required = true;
        if (accountNumber) accountNumber.required = true;
        if (accountBank) accountBank.required = true;
        if (accountBranch) accountBranch.required = true;
    } else if (method === 'bank_transfer') {
        creditFields.style.display = 'none';
        chequeFields.style.display = 'none';
        document.getElementById('modal_credit_repay_date').required = false;
        document.getElementById('modal_cheque_number').required = false;
        document.getElementById('modal_bank_name').required = false;
        document.getElementById('modal_cheque_date').required = false;
        document.getElementById('modal_cheque_repay_date').required = false;
        if (accountHolder) accountHolder.required = true;
        if (accountNumber) accountNumber.required = true;
        if (accountBank) accountBank.required = true;
        if (accountBranch) accountBranch.required = true;
    } else {
        creditFields.style.display = 'none';
        chequeFields.style.display = 'none';
        document.getElementById('modal_credit_repay_date').required = false;
        document.getElementById('modal_cheque_number').required = false;
        document.getElementById('modal_bank_name').required = false;
        document.getElementById('modal_cheque_date').required = false;
        document.getElementById('modal_cheque_repay_date').required = false;
        if (accountHolder) accountHolder.required = false;
        if (accountNumber) accountNumber.required = false;
        if (accountBank) accountBank.required = false;
        if (accountBranch) accountBranch.required = false;
    }
}



        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('print')) {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        };
  

</script>
@endsection

