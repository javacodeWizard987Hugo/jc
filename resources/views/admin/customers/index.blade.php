@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Customers / Clients</h1>
        <a href="{{ route(($routePrefix ?? 'admin') . '.customers.create') }}" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
            + Add Customer
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route(($routePrefix ?? 'admin') . '.customers.index') }}" class="grid grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Name, phone, or address..."
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    Filter
                </button>
            </div>
        </form>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credit Limit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="customersTableBody">
                    @forelse($customers as $customer)
                    <tr data-customer-id="{{ $customer->id }}" data-customer-updated="{{ $customer->updated_at->timestamp }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 customer-name">{{ $customer->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 customer-phone">{{ $customer->phone ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs customer-address">
                            <div class="truncate" title="{{ $customer->address ?? 'N/A' }}">
                                {{ $customer->address ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 customer-credit-limit" data-credit-limit="{{ $customer->credit_limit }}">
                            Rs. {{ number_format($customer->credit_limit, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold customer-outstanding" 
                            data-outstanding="{{ $customer->outstanding_balance }}"
                            data-customer-id="{{ $customer->id }}">
                            <span class="{{ $customer->outstanding_balance > 0 ? 'text-red-600' : 'text-gray-500' }}">
                            Rs. {{ number_format($customer->outstanding_balance, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap customer-status" data-is-active="{{ $customer->is_active ? '1' : '0' }}">
                            @if($customer->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route(($routePrefix ?? 'admin') . '.customers.show', $customer) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                            <a href="{{ route(($routePrefix ?? 'admin') . '.customers.edit', $customer) }}" class="text-red-600 hover:text-red-900 mr-3">Edit</a>
                            <form action="{{ route(($routePrefix ?? 'admin') . '.customers.destroy', $customer) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? This will delete the customer permanently.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No customers found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const routePrefix = '{{ $routePrefix ?? 'admin' }}';
    const searchParam = '{{ request('search') ?? '' }}';
    const statusParam = '{{ request('status') ?? '' }}';
    let apiUrl = `/${routePrefix}/customers/api`;
    const params = new URLSearchParams();
    if (searchParam) params.append('search', searchParam);
    if (statusParam) params.append('status', statusParam);
    if (params.toString()) apiUrl += '?' + params.toString();
    
    let lastUpdateTimestamp = Math.max(...Array.from(document.querySelectorAll('[data-customer-updated]')).map(el => parseInt(el.getAttribute('data-customer-updated')) || 0), 0);
    let updateInterval = null;
    
    // Update existing customer row
    function updateCustomerRow(row, customer) {
        // Update name
        const nameCell = row.querySelector('.customer-name');
        if (nameCell && nameCell.textContent !== customer.name) {
            nameCell.textContent = customer.name;
        }
        
        // Update phone
        const phoneCell = row.querySelector('.customer-phone');
        if (phoneCell && phoneCell.textContent !== customer.phone) {
            phoneCell.textContent = customer.phone;
        }
        
        // Update address
        const addressCell = row.querySelector('.customer-address');
        if (addressCell) {
            const addressDiv = addressCell.querySelector('div');
            if (addressDiv && addressDiv.textContent.trim() !== customer.address) {
                addressDiv.textContent = customer.address;
                addressDiv.setAttribute('title', customer.address);
            }
        }
        
        // Update credit limit
        const creditLimitCell = row.querySelector('.customer-credit-limit');
        if (creditLimitCell) {
            const currentLimit = parseFloat(creditLimitCell.getAttribute('data-credit-limit'));
            if (currentLimit !== customer.credit_limit) {
                creditLimitCell.setAttribute('data-credit-limit', customer.credit_limit);
                creditLimitCell.textContent = 'Rs. ' + parseFloat(customer.credit_limit).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        }
        
        // Update outstanding balance (most important for real-time)
        const outstandingCell = row.querySelector('.customer-outstanding');
        if (outstandingCell) {
            const currentOutstanding = parseFloat(outstandingCell.getAttribute('data-outstanding'));
            if (currentOutstanding !== customer.outstanding_balance) {
                outstandingCell.setAttribute('data-outstanding', customer.outstanding_balance);
                const span = outstandingCell.querySelector('span');
                if (span) {
                    span.className = customer.outstanding_balance > 0 ? 'text-red-600' : 'text-gray-500';
                    span.textContent = 'Rs. ' + parseFloat(customer.outstanding_balance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
                
                // Highlight change
                outstandingCell.style.backgroundColor = '#fef3c7';
                setTimeout(() => {
                    outstandingCell.style.transition = 'background-color 2s';
                    outstandingCell.style.backgroundColor = '';
                }, 100);
            }
        }
        
        // Update status
        const statusCell = row.querySelector('.customer-status');
        if (statusCell) {
            const currentStatus = statusCell.getAttribute('data-is-active') === '1';
            if (currentStatus !== customer.is_active) {
                statusCell.setAttribute('data-is-active', customer.is_active ? '1' : '0');
                if (customer.is_active) {
                    statusCell.innerHTML = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>';
                } else {
                    statusCell.innerHTML = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>';
                }
            }
        }
        
        row.setAttribute('data-customer-updated', customer.updated_at);
    }
    
    // Add new customer row
    function addCustomerRow(customer) {
        const tbody = document.getElementById('customersTableBody');
        const existingEmptyRow = tbody.querySelector('td[colspan="7"]');
        if (existingEmptyRow) {
            existingEmptyRow.closest('tr').remove();
        }
        
        const row = document.createElement('tr');
        row.setAttribute('data-customer-id', customer.id);
        row.setAttribute('data-customer-updated', customer.updated_at);
        
        const outstandingColor = customer.outstanding_balance > 0 ? 'text-red-600' : 'text-gray-500';
        const statusBadge = customer.is_active 
            ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>'
            : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>';
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 customer-name">${customer.name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 customer-phone">${customer.phone}</td>
            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs customer-address">
                <div class="truncate" title="${customer.address}">${customer.address}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 customer-credit-limit" data-credit-limit="${customer.credit_limit}">
                Rs. ${parseFloat(customer.credit_limit).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold customer-outstanding" 
                data-outstanding="${customer.outstanding_balance}"
                data-customer-id="${customer.id}">
                <span class="${outstandingColor}">
                    Rs. ${parseFloat(customer.outstanding_balance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap customer-status" data-is-active="${customer.is_active ? '1' : '0'}">
                ${statusBadge}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <a href="/${routePrefix}/customers/${customer.id}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                <a href="/${routePrefix}/customers/${customer.id}/edit" class="text-red-600 hover:text-red-900 mr-3">Edit</a>
                <form action="/${routePrefix}/customers/${customer.id}" method="POST" class="inline" onsubmit="return confirm('Are you sure? This will delete the customer permanently.')">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                </form>
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
    
    // Fetch latest customers
    async function fetchCustomers() {
        try {
            const response = await fetch(apiUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (!response.ok) {
                throw new Error('Failed to fetch customers');
            }
            
            const data = await response.json();
            
            // Create map of existing rows
            const existingRows = new Map();
            document.querySelectorAll('[data-customer-id]').forEach(row => {
                const id = row.getAttribute('data-customer-id');
                existingRows.set(parseInt(id), row);
            });
            
            // Process customers
            const tbody = document.getElementById('customersTableBody');
            const existingEmptyRow = tbody.querySelector('td[colspan="7"]');
            if (existingEmptyRow && data.customers.length > 0) {
                existingEmptyRow.closest('tr').remove();
            }
            
            const sortedCustomers = [...data.customers].sort((a, b) => b.updated_at - a.updated_at);
            
            sortedCustomers.forEach(customer => {
                const existingRow = existingRows.get(customer.id);
                
                if (existingRow) {
                    updateCustomerRow(existingRow, customer);
                } else {
                    addCustomerRow(customer);
                }
            });
            
            if (sortedCustomers.length > 0) {
                lastUpdateTimestamp = Math.max(...sortedCustomers.map(c => c.updated_at));
            }
        } catch (error) {
            console.error('Error fetching customers:', error);
        }
    }
    
    // Start polling every 3 seconds
    updateInterval = setInterval(fetchCustomers, 3000);
    fetchCustomers();
    
    // Clean up when page is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (updateInterval) {
                clearInterval(updateInterval);
                updateInterval = null;
            }
        } else {
            if (!updateInterval) {
                updateInterval = setInterval(fetchCustomers, 3000);
                fetchCustomers();
            }
        }
    });
});
</script>
@endsection

