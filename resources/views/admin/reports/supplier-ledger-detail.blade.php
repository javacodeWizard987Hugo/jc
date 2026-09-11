@extends('layouts.app')

@section('title', 'Supplier Ledger - ' . $supplier->name)

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
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <a href="{{ route($routePrefix . '.reports.supplier-ledger') }}" class="text-red-600 hover:text-red-900 mb-2 inline-block">← Back to Supplier List</a>
            <h1 class="text-3xl font-bold text-gray-900">📘 Supplier Ledger</h1>
        </div>
        <div class="flex gap-3">
            <button onclick="openRecordPaymentModal()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                💰 Record Payment
            </button>
            <a href="{{ route($routePrefix . '.reports.export', 'supplier-ledger') }}?format=pdf&supplier_id={{ $supplier->id }}&start_date={{ $startDate ?? '' }}&end_date={{ $endDate ?? '' }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                🖨️ Print PDF
            </a>
            <a href="{{ route($routePrefix . '.reports.export', 'supplier-ledger') }}?format=csv&supplier_id={{ $supplier->id }}&start_date={{ $startDate ?? '' }}&end_date={{ $endDate ?? '' }}" 
               class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                📥 Export CSV
            </a>
        </div>
    </div>

    <!-- Supplier Info Card -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $supplier->name }}</h2>
                <dl class="space-y-2">
                    @if($supplier->contact_person)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                            <dd class="text-sm text-gray-900">{{ $supplier->contact_person }}</dd>
                        </div>
                    @endif
                    @if($supplier->phone)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="text-sm text-gray-900">{{ $supplier->phone }}</dd>
                        </div>
                    @endif
                    @if($supplier->email)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="text-sm text-gray-900">{{ $supplier->email }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-600">
                    <p class="text-sm font-medium text-gray-500">🟦 Total Purchases</p>
                    <p class="text-2xl font-semibold text-gray-900">Rs. {{ number_format($totalPurchases, 2) }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-600">
                    <p class="text-sm font-medium text-gray-500">🟩 Total Payments</p>
                    <p class="text-2xl font-semibold text-green-600">Rs. {{ number_format($totalPaid, 2) }}</p>
                </div>
                <div class="bg-red-50 rounded-lg p-4 border-l-4 border-red-600">
                    <p class="text-sm font-medium text-gray-500">🔴 Outstanding Balance</p>
                    <p class="text-2xl font-semibold text-red-600">Rs. {{ number_format($outstandingBalance, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route($routePrefix . '.reports.supplier-ledger') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
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
            <div>
                <label for="transaction_type" class="block text-sm font-medium text-gray-700 mb-1">📄 Transaction Type</label>
                <select name="transaction_type" id="transaction_type"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="all" {{ $transactionType === 'all' ? 'selected' : '' }}>All</option>
                    <option value="purchases" {{ $transactionType === 'purchases' ? 'selected' : '' }}>Purchases (GRN)</option>
                    <option value="payments" {{ $transactionType === 'payments' ? 'selected' : '' }}>Payments</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Filter
                </button>
            </div>
            <div class="flex items-end gap-2">
                <a href="{{ route('admin.reports.export', ['type' => 'supplier-ledger', 'supplier_id' => $supplier->id, 'format' => 'excel']) }}" 
                   class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-center text-sm">
                    📥 Export Excel
                </a>
            </div>
        </form>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit (Purchases)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit (Payments)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="ledgerTableBody">
                @forelse($ledgerEntries as $entry)
                <tr class="hover:bg-gray-50 {{ $entry['type'] === 'payment' && isset($entry['payment']) && $entry['payment']->payment_method === 'cheque' ? 'cursor-pointer' : '' }}"
                    @if($entry['type'] === 'payment' && isset($entry['payment']) && $entry['payment']->payment_method === 'cheque')
                    onclick="showChequeDetails({{ $entry['payment']->id }})"
                    @endif>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($entry['date'])->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ $entry['reference'] }}</td>
                    <td class="px-6 py-4 text-sm">
                        {{ $entry['description'] }}
                        @if($entry['type'] === 'payment' && isset($entry['payment']))
                            @if($entry['payment']->payment_method === 'cheque' && $entry['payment']->cheque_number)
                                <span class="text-xs text-gray-500 ml-2">(Cheque #{{ $entry['payment']->cheque_number }})</span>
                            @endif
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $entry['debit'] > 0 ? 'text-blue-600 font-semibold' : 'text-gray-400' }}">
                        {{ $entry['debit'] > 0 ? 'Rs. ' . number_format($entry['debit'], 2) : '–' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $entry['credit'] > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                        {{ $entry['credit'] > 0 ? 'Rs. ' . number_format($entry['credit'], 2) : '–' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $entry['balance'] > 0 ? 'text-red-600' : ($entry['balance'] < 0 ? 'text-green-600' : 'text-gray-600') }}">
                        Rs. {{ number_format($entry['balance'], 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <div class="text-4xl mb-2">📘</div>
                        <p>No transactions found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Cheque Details Modal -->
<div id="chequeDetailsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">📝 Cheque Payment Details</h3>
                <button onclick="closeChequeDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="chequeDetailsContent" class="space-y-3">
                <!-- Content will be loaded here -->
            </div>
            <div class="mt-6">
                <button onclick="closeChequeDetailsModal()" 
                        class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div id="recordPaymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">💰 Record Payment</h3>
                <button onclick="closeRecordPaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route($routePrefix . '.suppliers.payments', $supplier) }}" method="POST" id="paymentRecordForm" onsubmit="return validatePaymentForm(event)">
                @csrf
                <div class="space-y-4">
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
                                   value="{{ $accountDetails['account_holder_name'] ?? '' }}"
                                   data-default-value="{{ $accountDetails['account_holder_name'] ?? '' }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="modal_account_number" class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                            <input type="text" name="account_number" id="modal_account_number"
                                   value="{{ $accountDetails['account_number'] ?? '' }}"
                                   data-default-value="{{ $accountDetails['account_number'] ?? '' }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="modal_account_bank" class="block text-sm font-medium text-gray-700 mb-1">Bank</label>
                            <select name="account_bank" id="modal_account_bank"
                                    data-default-value="{{ $accountDetails['account_bank'] ?? '' }}"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank }}" {{ ($accountDetails['account_bank'] ?? '') == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="modal_account_branch" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                            <input type="text" name="account_branch" id="modal_account_branch"
                                   value="{{ $accountDetails['account_branch'] ?? '' }}"
                                   data-default-value="{{ $accountDetails['account_branch'] ?? '' }}"
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
function toggleModalPaymentFields() {
    const method = document.getElementById('modal_payment_method').value;
    const creditFields = document.getElementById('modal_credit_fields');
    const chequeFields = document.getElementById('modal_cheque_fields');
    
    // Get account detail fields
    const accountHolder = document.getElementById('modal_account_holder_name');
    const accountNumber = document.getElementById('modal_account_number');
    const accountBank = document.getElementById('modal_account_bank');
    const accountBranch = document.getElementById('modal_account_branch');
    
    if (method === 'credit') {
        creditFields.style.display = 'block';
        chequeFields.style.display = 'none';
        document.getElementById('modal_credit_repay_date').required = true;
        // Make account fields required for credit
        accountHolder.required = true;
        accountNumber.required = true;
        accountBank.required = true;
        accountBranch.required = true;
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
        // Make account fields required for cheque
        accountHolder.required = true;
        accountNumber.required = true;
        accountBank.required = true;
        accountBranch.required = true;
    } else if (method === 'bank_transfer') {
        creditFields.style.display = 'none';
        chequeFields.style.display = 'none';
        document.getElementById('modal_credit_repay_date').required = false;
        document.getElementById('modal_cheque_number').required = false;
        document.getElementById('modal_bank_name').required = false;
        document.getElementById('modal_cheque_date').required = false;
        document.getElementById('modal_cheque_repay_date').required = false;
        // Make account fields required for bank transfer
        accountHolder.required = true;
        accountNumber.required = true;
        accountBank.required = true;
        accountBranch.required = true;
    } else {
        creditFields.style.display = 'none';
        chequeFields.style.display = 'none';
        document.getElementById('modal_credit_repay_date').required = false;
        document.getElementById('modal_cheque_number').required = false;
        document.getElementById('modal_bank_name').required = false;
        document.getElementById('modal_cheque_date').required = false;
        document.getElementById('modal_cheque_repay_date').required = false;
        // Account fields optional for cash/other
        accountHolder.required = false;
        accountNumber.required = false;
        accountBank.required = false;
        accountBranch.required = false;
    }
}

function validatePaymentForm(event) {
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

function openRecordPaymentModal() {
    document.getElementById('recordPaymentModal').classList.remove('hidden');
    // Reset form and initialize payment fields
    const form = document.querySelector('#recordPaymentModal form');
    if (form) {
        // Ensure all required attributes are properly set
        toggleModalPaymentFields();
    }
}

function closeRecordPaymentModal() {
    document.getElementById('recordPaymentModal').classList.add('hidden');
    const form = document.querySelector('#recordPaymentModal form');
    if (form) {
        // Get default values from data attributes (pre-populated from previous payments)
        const accountHolderInput = document.getElementById('modal_account_holder_name');
        const accountNumberInput = document.getElementById('modal_account_number');
        const accountBankInput = document.getElementById('modal_account_bank');
        const accountBranchInput = document.getElementById('modal_account_branch');
        
        const defaultAccountHolder = accountHolderInput.getAttribute('data-default-value') || '';
        const defaultAccountNumber = accountNumberInput.getAttribute('data-default-value') || '';
        const defaultAccountBank = accountBankInput.getAttribute('data-default-value') || '';
        const defaultAccountBranch = accountBranchInput.getAttribute('data-default-value') || '';
        
        form.reset();
        
        // Restore account details from previous payments (default values)
        if (defaultAccountHolder || defaultAccountNumber || defaultAccountBank || defaultAccountBranch) {
            accountHolderInput.value = defaultAccountHolder;
            accountNumberInput.value = defaultAccountNumber;
            accountBankInput.value = defaultAccountBank;
            accountBranchInput.value = defaultAccountBranch;
        }
    }
}
function showChequeDetails(paymentId) {
    fetch(`{{ url('/' . $routePrefix . '/suppliers/payments') }}/${paymentId}/cheque-details`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('chequeDetailsContent');
            content.innerHTML = `
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Bank Name:</span>
                        <span class="text-gray-900">${data.bank_name || 'N/A'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Cheque Number:</span>
                        <span class="text-gray-900">${data.cheque_number || 'N/A'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Amount:</span>
                        <span class="text-red-600 font-semibold">Rs. ${data.amount || '0.00'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Cheque Date:</span>
                        <span class="text-gray-900">${data.cheque_date || 'N/A'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Repay Date:</span>
                        <span class="text-gray-900">${data.cheque_repay_date || 'N/A'}</span>
                    </div>
                    <hr class="my-3">
                    <div class="text-sm font-semibold text-gray-700 mb-2">Receiver Account Details:</div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Account Holder:</span>
                        <span class="text-gray-900">${data.account_holder_name || 'N/A'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Account Number:</span>
                        <span class="text-gray-900">${data.account_number || 'N/A'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Bank:</span>
                        <span class="text-gray-900">${data.account_bank || 'N/A'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Branch:</span>
                        <span class="text-gray-900">${data.account_branch || 'N/A'}</span>
                    </div>
                </div>
            `;
            document.getElementById('chequeDetailsModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error fetching cheque details:', error);
            alert('Error loading cheque details');
        });
}

function closeChequeDetailsModal() {
    document.getElementById('chequeDetailsModal').classList.add('hidden');
}

</script>
@endsection

