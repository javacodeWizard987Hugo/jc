@extends('layouts.app')

@section('title', 'Customer Credits ')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Customer Credits</h1>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('cashier.credits.index') }}" class="grid grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Customer name or phone..."
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer</label>
                <select name="customer_id" id="customer_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div>
                <label for="overdue" class="block text-sm font-medium text-gray-700">Overdue</label>
                <select name="overdue" id="overdue"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">All</option>
                    <option value="yes" {{ request('overdue') === 'yes' ? 'selected' : '' }}>Overdue Only</option>
                </select>
            </div>
            <div class="col-span-4 flex justify-end">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Filter
                </button>
                <a href="{{ route('cashier.credits.index') }}" class="ml-2 bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="creditsTableBody">
                @forelse($credits as $credit)
                @php
                    $isOverdue = $credit->due_date < now() && $credit->outstanding_amount > 0;
                @endphp
                <tr data-credit-id="{{ $credit->id }}" 
                    data-credit-updated="{{ $credit->updated_at->timestamp }}"
                    class="{{ $isOverdue ? 'bg-red-50' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 credit-customer">
                        {{ $credit->customer->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 credit-invoice">
                        {{ $credit->sale->invoice_number ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 credit-amount" data-amount="{{ $credit->amount }}">
                        Rs. {{ number_format($credit->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 credit-paid" data-paid="{{ $credit->paid_amount }}">
                        Rs. {{ number_format($credit->paid_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 credit-outstanding" 
                        data-outstanding="{{ $credit->outstanding_amount }}">
                        Rs. {{ number_format($credit->outstanding_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm credit-due-date" 
                        data-due-date="{{ $credit->due_date->timestamp }}"
                        data-outstanding="{{ $credit->outstanding_amount }}">
                        <span class="{{ $isOverdue ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                            {{ $credit->due_date->format('Y-m-d') }}
                            @if($isOverdue)
                                <span class="ml-2 text-xs">(OVERDUE)</span>
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap credit-status" data-status="{{ $credit->status }}">
                        @if($credit->status === 'paid')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                        @elseif($credit->status === 'partial')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Partial</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('cashier.credits.show', $credit) }}" class="text-red-600 hover:text-red-900">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">No credits found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $credits->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchParam = '{{ request('search') ?? '' }}';
    const customerIdParam = '{{ request('customer_id') ?? '' }}';
    const statusParam = '{{ request('status') ?? '' }}';
    const overdueParam = '{{ request('overdue') ?? '' }}';
    
    let apiUrl = '/cashier/credits/api';
    const params = new URLSearchParams();
    if (searchParam) params.append('search', searchParam);
    if (customerIdParam) params.append('customer_id', customerIdParam);
    if (statusParam) params.append('status', statusParam);
    if (overdueParam) params.append('overdue', overdueParam);
    if (params.toString()) apiUrl += '?' + params.toString();
    
    let lastUpdateTimestamp = Math.max(...Array.from(document.querySelectorAll('[data-credit-updated]')).map(el => parseInt(el.getAttribute('data-credit-updated')) || 0), 0);
    let updateInterval = null;
    
    // Get status badge HTML
    function getStatusBadge(status) {
        if (status === 'paid') {
            return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>';
        } else if (status === 'partial') {
            return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Partial</span>';
        } else {
            return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Pending</span>';
        }
    }
    
    // Update existing credit row
    function updateCreditRow(row, credit) {
        // Update customer name
        const customerCell = row.querySelector('.credit-customer');
        if (customerCell && customerCell.textContent.trim() !== credit.customer_name) {
            customerCell.textContent = credit.customer_name;
        }
        
        // Update invoice
        const invoiceCell = row.querySelector('.credit-invoice');
        if (invoiceCell && invoiceCell.textContent.trim() !== credit.invoice_number) {
            invoiceCell.textContent = credit.invoice_number;
        }
        
        // Update amount
        const amountCell = row.querySelector('.credit-amount');
        if (amountCell) {
            const currentAmount = parseFloat(amountCell.getAttribute('data-amount'));
            if (currentAmount !== credit.amount) {
                amountCell.setAttribute('data-amount', credit.amount);
                amountCell.textContent = 'Rs. ' + parseFloat(credit.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        }
        
        // Update paid amount
        const paidCell = row.querySelector('.credit-paid');
        if (paidCell) {
            const currentPaid = parseFloat(paidCell.getAttribute('data-paid'));
            if (currentPaid !== credit.paid_amount) {
                paidCell.setAttribute('data-paid', credit.paid_amount);
                paidCell.textContent = 'Rs. ' + parseFloat(credit.paid_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        }
        
        // Update outstanding amount (most important)
        const outstandingCell = row.querySelector('.credit-outstanding');
        if (outstandingCell) {
            const currentOutstanding = parseFloat(outstandingCell.getAttribute('data-outstanding'));
            if (currentOutstanding !== credit.outstanding_amount) {
                outstandingCell.setAttribute('data-outstanding', credit.outstanding_amount);
                outstandingCell.textContent = 'Rs. ' + parseFloat(credit.outstanding_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                
                // Highlight change
                outstandingCell.style.backgroundColor = '#fef3c7';
                setTimeout(() => {
                    outstandingCell.style.transition = 'background-color 2s';
                    outstandingCell.style.backgroundColor = '';
                }, 100);
            }
        }
        
        // Update due date and overdue status
        const dueDateCell = row.querySelector('.credit-due-date');
        if (dueDateCell) {
            const currentDueDate = parseInt(dueDateCell.getAttribute('data-due-date'));
            const currentOutstanding = parseFloat(dueDateCell.getAttribute('data-outstanding'));
            
            if (currentDueDate !== credit.due_date_timestamp || currentOutstanding !== credit.outstanding_amount) {
                dueDateCell.setAttribute('data-due-date', credit.due_date_timestamp);
                dueDateCell.setAttribute('data-outstanding', credit.outstanding_amount);
                
                const isOverdue = credit.is_overdue;
                const span = dueDateCell.querySelector('span');
                if (span) {
                    span.className = isOverdue ? 'text-red-600 font-bold' : 'text-gray-500';
                    span.innerHTML = credit.due_date + (isOverdue ? ' <span class="ml-2 text-xs">(OVERDUE)</span>' : '');
                }
                
                // Update row background
                if (isOverdue) {
                    row.classList.add('bg-red-50');
                } else {
                    row.classList.remove('bg-red-50');
                }
            }
        }
        
        // Update status
        const statusCell = row.querySelector('.credit-status');
        if (statusCell) {
            const currentStatus = statusCell.getAttribute('data-status');
            if (currentStatus !== credit.status) {
                statusCell.setAttribute('data-status', credit.status);
                statusCell.innerHTML = getStatusBadge(credit.status);
            }
        }
        
        row.setAttribute('data-credit-updated', credit.updated_at);
    }
    
    // Add new credit row
    function addCreditRow(credit) {
        const tbody = document.getElementById('creditsTableBody');
        const existingEmptyRow = tbody.querySelector('td[colspan="8"]');
        if (existingEmptyRow) {
            existingEmptyRow.closest('tr').remove();
        }
        
        const row = document.createElement('tr');
        row.setAttribute('data-credit-id', credit.id);
        row.setAttribute('data-credit-updated', credit.updated_at);
        if (credit.is_overdue) {
            row.classList.add('bg-red-50');
        }
        
        const dueDateClass = credit.is_overdue ? 'text-red-600 font-bold' : 'text-gray-500';
        const dueDateHtml = credit.due_date + (credit.is_overdue ? ' <span class="ml-2 text-xs">(OVERDUE)</span>' : '');
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 credit-customer">${credit.customer_name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 credit-invoice">${credit.invoice_number}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 credit-amount" data-amount="${credit.amount}">
                Rs. ${parseFloat(credit.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 credit-paid" data-paid="${credit.paid_amount}">
                Rs. ${parseFloat(credit.paid_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 credit-outstanding" 
                data-outstanding="${credit.outstanding_amount}">
                Rs. ${parseFloat(credit.outstanding_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm credit-due-date" 
                data-due-date="${credit.due_date_timestamp}"
                data-outstanding="${credit.outstanding_amount}">
                <span class="${dueDateClass}">${dueDateHtml}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap credit-status" data-status="${credit.status}">
                ${getStatusBadge(credit.status)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <a href="/cashier/credits/${credit.id}" class="text-red-600 hover:text-red-900">View</a>
            </td>
        `;
        
        tbody.insertBefore(row, tbody.firstChild);
        
        // Highlight animation
        row.style.backgroundColor = '#fef3c7';
        setTimeout(() => {
            row.style.transition = 'background-color 2s';
            row.style.backgroundColor = '';
        }, 100);
    }
    
    // Fetch latest credits
    async function fetchCredits() {
        try {
            const response = await fetch(apiUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch credits');
            }
            
            const data = await response.json();
            
            // Create map of existing rows
            const existingRows = new Map();
            document.querySelectorAll('[data-credit-id]').forEach(row => {
                const id = row.getAttribute('data-credit-id');
                existingRows.set(parseInt(id), row);
            });
            
            // Process credits
            const tbody = document.getElementById('creditsTableBody');
            const existingEmptyRow = tbody.querySelector('td[colspan="8"]');
            if (existingEmptyRow && data.credits.length > 0) {
                existingEmptyRow.closest('tr').remove();
            }
            
            const sortedCredits = [...data.credits].sort((a, b) => b.updated_at - a.updated_at);
            
            sortedCredits.forEach(credit => {
                const existingRow = existingRows.get(credit.id);
                
                if (existingRow) {
                    updateCreditRow(existingRow, credit);
                } else {
                    addCreditRow(credit);
                }
            });
            
            if (sortedCredits.length > 0) {
                lastUpdateTimestamp = Math.max(...sortedCredits.map(c => c.updated_at));
            }
        } catch (error) {
            console.error('Error fetching credits:', error);
        }
    }
    
    // Start polling every 3 seconds
    updateInterval = setInterval(fetchCredits, 3000);
    fetchCredits();
    
    // Clean up when page is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (updateInterval) {
                clearInterval(updateInterval);
                updateInterval = null;
            }
        } else {
            if (!updateInterval) {
                updateInterval = setInterval(fetchCredits, 3000);
                fetchCredits();
            }
        }
    });
});
</script>
@endsection

