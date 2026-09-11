@extends('layouts.app')

@section('title', 'Supplier Ledger')

@section('content')
@php
    $routePrefix = auth()->user()->isAdmin() ? 'admin' : 'cashier';
@endphp
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Supplier Ledger</h1>
        <div class="flex gap-3">
            <a href="{{ route($routePrefix . '.reports.export', 'supplier-ledger') }}?format=pdf&supplier_id={{ $supplierId ?? '' }}&start_date={{ $startDate ?? '' }}&end_date={{ $endDate ?? '' }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                🖨️ Print PDF
            </a>
            <a href="{{ route($routePrefix . '.reports.export', 'supplier-ledger') }}?format=csv&supplier_id={{ $supplierId ?? '' }}&start_date={{ $startDate ?? '' }}&end_date={{ $endDate ?? '' }}" 
               class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                📥 Export CSV
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route($routePrefix . '.reports.supplier-ledger') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                <select name="supplier_id" id="supplier_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ $supplierId == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }} (Rs. {{ number_format($supplier->outstanding_balance, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? '' }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? '' }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Filter
                </button>
                @if($supplierId)
                <a href="{{ route($routePrefix . '.reports.export', ['type' => 'supplier-ledger', 'supplier_id' => $supplierId, 'format' => 'excel']) }}" 
                   class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-center text-sm">
                    Export CSV
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    @if(isset($summary))
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-6 border-l-4 border-blue-600">
            <p class="text-sm font-medium text-gray-500">Total Invoice Amount</p>
            <p class="text-2xl font-semibold text-gray-900">Rs. {{ number_format($summary['total_invoice_amount'], 2) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6 border-l-4 border-green-600">
            <p class="text-sm font-medium text-gray-500">Total Paid</p>
            <p class="text-2xl font-semibold text-green-600">Rs. {{ number_format($summary['total_paid_amount'], 2) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6 border-l-4 border-red-600">
            <p class="text-sm font-medium text-gray-500">Total Outstanding</p>
            <p class="text-2xl font-semibold text-red-600">Rs. {{ number_format($summary['total_outstanding'], 2) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-6 border-l-4 border-orange-600">
            <p class="text-sm font-medium text-gray-500">Current Balance</p>
            <p class="text-2xl font-semibold text-orange-600">Rs. {{ number_format($summary['current_outstanding_balance'], 2) }}</p>
        </div>
    </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Running Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="paymentsTableBody">
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50" data-payment-id="{{ $payment->id }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $payment->payment_date?->format('Y-m-d') ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $payment->supplier->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $payment->invoice_number ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($payment->due_date)
                            {{ $payment->due_date->format('Y-m-d') }}
                            @if($payment->due_date < now() && $payment->outstanding_amount > 0)
                                <span class="ml-1 text-red-600">⚠️</span>
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">Rs. {{ number_format($payment->invoice_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">Rs. {{ number_format($payment->paid_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">Rs. {{ number_format($payment->outstanding_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ ($payment->running_balance ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                        Rs. {{ number_format($payment->running_balance ?? 0, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex flex-col">
                            <span>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                            @if($payment->payment_method === 'credit' && $payment->credit_repay_date)
                                <span class="text-xs text-gray-500">Repay Date: {{ $payment->credit_repay_date->format('Y-m-d') }}</span>
                                @if($payment->credit_repay_date < now())
                                    <span class="text-xs text-red-600 font-semibold">⚠️ Overdue</span>
                                @elseif($payment->credit_repay_date <= now()->addDays(2))
                                    <span class="text-xs text-orange-600 font-semibold">⚠️ Due Soon</span>
                                @endif
                            @elseif($payment->payment_method === 'cheque')
                                @if($payment->cheque_number)
                                    <span class="text-xs text-gray-500">Cheque #: {{ $payment->cheque_number }}</span>
                                @endif
                                @if($payment->bank_name)
                                    <span class="text-xs text-gray-500">Bank: {{ $payment->bank_name }}</span>
                                @endif
                                @if($payment->cheque_date)
                                    <span class="text-xs text-gray-500">Cheque Date: {{ $payment->cheque_date->format('Y-m-d') }}</span>
                                @endif
                                @if($payment->cheque_repay_date)
                                    <span class="text-xs text-gray-500">Repay Date: {{ $payment->cheque_repay_date->format('Y-m-d') }}</span>
                                    @if($payment->cheque_repay_date < now())
                                        <span class="text-xs text-red-600 font-semibold">⚠️ Overdue</span>
                                    @elseif($payment->cheque_repay_date <= now()->addDays(2))
                                        <span class="text-xs text-orange-600 font-semibold">⚠️ Due Soon</span>
                                    @endif
                                @endif
                                @if($payment->account_holder_name)
                                    <span class="text-xs text-gray-500">Account: {{ $payment->account_holder_name }}</span>
                                @endif
                                @if($payment->account_number)
                                    <span class="text-xs text-gray-500">A/C #: {{ $payment->account_number }}</span>
                                @endif
                                @if($payment->account_bank)
                                    <span class="text-xs text-gray-500">Bank: {{ $payment->account_bank }}</span>
                                @endif
                                @if($payment->account_branch)
                                    <span class="text-xs text-gray-500">Branch: {{ $payment->account_branch }}</span>
                                @endif
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($payment->outstanding_amount > 0 && $payment->due_date && $payment->due_date < now())
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                        @elseif($payment->outstanding_amount > 0)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-6 py-4 text-center text-gray-500">No payments found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $payments->links() }}
    </div>
</div>

<script>
let lastUpdateTimestamp = {{ $payments->count() > 0 ? $payments->first()->updated_at->timestamp : 0 }};
let updateInterval;

function fetchSupplierLedger() {
    const supplierId = document.getElementById('supplier_id')?.value || '';
    const startDate = document.getElementById('start_date')?.value || '';
    const endDate = document.getElementById('end_date')?.value || '';
    
    const params = new URLSearchParams({
        supplier_id: supplierId,
        start_date: startDate,
        end_date: endDate,
    });
    
    fetch(`{{ route($routePrefix . '.reports.supplier-ledger-api') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            updatePaymentsTable(data.payments);
            updateSummary(data.summary);
        })
        .catch(error => console.error('Error fetching supplier ledger:', error));
}


function updatePaymentsTable(payments) {
    const tbody = document.getElementById('paymentsTableBody');
    if (!tbody) return;
    
    const existingRows = Array.from(tbody.querySelectorAll('tr[data-payment-id]'));
    const existingIds = new Set(existingRows.map(row => row.getAttribute('data-payment-id')));
    const newIds = new Set(payments.map(p => p.id.toString()));
    
    // Update existing rows
    payments.forEach(payment => {
        const row = tbody.querySelector(`tr[data-payment-id="${payment.id}"]`);
        if (row) {
            updatePaymentRow(row, payment);
        } else {
            // Add new row at the top
            const newRow = createPaymentRow(payment);
            tbody.insertBefore(newRow, tbody.firstChild);
            // Highlight new row
            newRow.classList.add('bg-yellow-50');
            setTimeout(() => newRow.classList.remove('bg-yellow-50'), 3000);
        }
    });
    
    // Remove rows that no longer exist
    existingRows.forEach(row => {
        const id = row.getAttribute('data-payment-id');
        if (!newIds.has(id)) {
            row.remove();
        }
    });
}

function updatePaymentRow(row, payment) {
    const cells = row.querySelectorAll('td');
    if (cells.length < 10) return;
    
    // Update cells
    cells[0].textContent = payment.payment_date;
    cells[1].innerHTML = `<span class="font-medium">${payment.supplier_name}</span>`;
    cells[2].textContent = payment.invoice_number;
    cells[3].innerHTML = payment.due_date 
        ? `${payment.due_date}${payment.due_date < new Date().toISOString().split('T')[0] && parseFloat(payment.outstanding_amount) > 0 ? ' <span class="ml-1 text-red-600">⚠️</span>' : ''}`
        : 'N/A';
    cells[4].textContent = `Rs. ${payment.invoice_amount}`;
    cells[5].innerHTML = `<span class="text-green-600 font-semibold">Rs. ${payment.paid_amount}</span>`;
    cells[6].innerHTML = `<span class="font-semibold text-red-600">Rs. ${payment.outstanding_amount}</span>`;
    cells[7].innerHTML = `<span class="font-bold ${parseFloat(payment.running_balance) > 0 ? 'text-red-600' : 'text-green-600'}">Rs. ${payment.running_balance}</span>`;
    
    // Update payment method cell
    let methodHtml = `<div class="flex flex-col"><span>${payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1).replace('_', ' ')}</span>`;
    
    if (payment.payment_method === 'credit' && payment.credit_repay_date) {
        methodHtml += `<span class="text-xs text-gray-500">Repay Date: ${payment.credit_repay_date}</span>`;
        const repayDate = new Date(payment.credit_repay_date);
        const today = new Date();
        const twoDaysFromNow = new Date();
        twoDaysFromNow.setDate(today.getDate() + 2);
        if (repayDate < today) {
            methodHtml += `<span class="text-xs text-red-600 font-semibold">⚠️ Overdue</span>`;
        } else if (repayDate <= twoDaysFromNow) {
            methodHtml += `<span class="text-xs text-orange-600 font-semibold">⚠️ Due Soon</span>`;
        }
    } else if (payment.payment_method === 'cheque') {
        if (payment.cheque_number) {
            methodHtml += `<span class="text-xs text-gray-500">Cheque #: ${payment.cheque_number}</span>`;
        }
        if (payment.bank_name) {
            methodHtml += `<span class="text-xs text-gray-500">Bank: ${payment.bank_name}</span>`;
        }
        if (payment.cheque_date) {
            methodHtml += `<span class="text-xs text-gray-500">Cheque Date: ${payment.cheque_date}</span>`;
        }
        if (payment.cheque_repay_date) {
            methodHtml += `<span class="text-xs text-gray-500">Repay Date: ${payment.cheque_repay_date}</span>`;
            const repayDate = new Date(payment.cheque_repay_date);
            const today = new Date();
            const twoDaysFromNow = new Date();
            twoDaysFromNow.setDate(today.getDate() + 2);
            if (repayDate < today) {
                methodHtml += `<span class="text-xs text-red-600 font-semibold">⚠️ Overdue</span>`;
            } else if (repayDate <= twoDaysFromNow) {
                methodHtml += `<span class="text-xs text-orange-600 font-semibold">⚠️ Due Soon</span>`;
            }
        }
        if (payment.account_holder_name) {
            methodHtml += `<span class="text-xs text-gray-500">Account: ${payment.account_holder_name}</span>`;
        }
        if (payment.account_number) {
            methodHtml += `<span class="text-xs text-gray-500">A/C #: ${payment.account_number}</span>`;
        }
        if (payment.account_bank) {
            methodHtml += `<span class="text-xs text-gray-500">Bank: ${payment.account_bank}</span>`;
        }
        if (payment.account_branch) {
            methodHtml += `<span class="text-xs text-gray-500">Branch: ${payment.account_branch}</span>`;
        }
    }
    methodHtml += '</div>';
    cells[8].innerHTML = methodHtml;
    
    // Update status cell
    const status = payment.status;
    let statusHtml = '';
    if (status === 'overdue') {
        statusHtml = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>';
    } else if (status === 'pending') {
        statusHtml = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>';
    } else {
        statusHtml = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>';
    }
    cells[9].innerHTML = statusHtml;
}

function createPaymentRow(payment) {
    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50';
    row.setAttribute('data-payment-id', payment.id);
    
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap text-sm">${payment.payment_date}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">${payment.supplier_name}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">${payment.invoice_number}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">${payment.due_date || 'N/A'}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">Rs. ${payment.invoice_amount}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">Rs. ${payment.paid_amount}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">Rs. ${payment.outstanding_amount}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold ${parseFloat(payment.running_balance) > 0 ? 'text-red-600' : 'text-green-600'}">Rs. ${payment.running_balance}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">
            <div class="flex flex-col">
                <span>${payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1).replace('_', ' ')}</span>
                ${payment.payment_method === 'cheque' && payment.cheque_number ? `<span class="text-xs text-gray-500">Cheque #: ${payment.cheque_number}</span>` : ''}
                ${payment.payment_method === 'cheque' && payment.bank_name ? `<span class="text-xs text-gray-500">Bank: ${payment.bank_name}</span>` : ''}
                ${payment.payment_method === 'cheque' && payment.cheque_repay_date ? `<span class="text-xs text-gray-500">Repay Date: ${payment.cheque_repay_date}</span>` : ''}
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            ${payment.status === 'overdue' ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>' : 
              payment.status === 'pending' ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>' : 
              '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>'}
        </td>
    `;
    
    return row;
}

function updateSummary(summary) {
    // Update summary cards if they exist
    const summaryCards = document.querySelectorAll('[data-summary]');
    summaryCards.forEach(card => {
        const key = card.getAttribute('data-summary');
        if (summary[key] !== undefined) {
            const valueElement = card.querySelector('.text-2xl');
            if (valueElement) {
                valueElement.textContent = `Rs. ${parseFloat(summary[key]).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            }
        }
    });
}

// Start real-time updates
document.addEventListener('DOMContentLoaded', function() {
    // Fetch updates every 5 seconds
    updateInterval = setInterval(fetchSupplierLedger, 5000);
    
    // Also fetch when filters change
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            clearInterval(updateInterval);
            setTimeout(() => {
                updateInterval = setInterval(fetchSupplierLedger, 5000);
            }, 1000);
        });
    }
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
</script>
@endsection

