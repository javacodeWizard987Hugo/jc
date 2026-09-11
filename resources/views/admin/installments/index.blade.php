@extends('layouts.app')

@section('title', 'Installment Agreements')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <!-- Header -->
    <div class="bg-white rounded-lg shadow border-2 border-gray-200 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">🏦 Installment History</h1>
                <p class="text-sm text-gray-600">All installment agreements and details</p>
            </div>
            <form action="{{ route('admin.installments.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Name, Phone, NIC or Invoice..." 
                       class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm w-full md:w-64">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold text-sm">
                    Search
                </button>

                @if(request('filter') === 'repeated')
                    <a href="{{ route('admin.installments.index', array_merge(request()->query(), ['filter' => null, 'page' => null])) }}" 
                       class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 font-semibold text-sm whitespace-nowrap"
                       title="Click to remove filter">
                        🔄 Repeated Only
                    </a>
                @else
                    <a href="{{ route('admin.installments.index', array_merge(request()->query(), ['filter' => 'repeated', 'page' => null])) }}" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-200 font-semibold text-sm whitespace-nowrap">
                        Filter Repeated
                    </a>
                @endif

                @if(request('search') || request('filter'))
                    <a href="{{ route('admin.installments.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-semibold text-sm">
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="bg-white rounded-lg shadow border-2 border-gray-200 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900">Summary & Today's Income</h2>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-4">
            <div class="bg-gray-100 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Paid Off Agreements</p>
                <p class="text-2xl font-bold text-gray-900">{{ $paidOffCount }}</p>
            </div>
            <div class="bg-blue-100 p-4 rounded-lg">
                <p class="text-sm text-gray-600">Today's Total Income</p>
                <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($dailyIncome->sum(), 2) }}</p>
            </div>
            @foreach($dailyIncome as $method => $total)
                <div class="bg-green-100 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">by {{ ucfirst($method) }}</p>
                    <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($total, 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Table -->
<div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto max-h-[600px] overflow-y-auto">

        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">#</th>
                    <th class="px-4 py-3 text-left font-semibold">Invoice</th>
                    <th class="px-4 py-3 text-left font-semibold">Customer</th>
                    <th class="px-4 py-3 text-left font-semibold">Items</th>
                  
                   
                         <th class="px-4 py-3 text-left font-semibold">EMI Lock Mode</th>
                             <th class="px-4 py-3 text-left font-semibold">EMI Number</th>
                               <th class="px-4 py-3 text-left font-semibold">Guarantor</th>
                    <th class="px-4 py-3 text-left font-semibold">Down Payment</th>
                    <th class="px-4 py-3 text-left font-semibold">Total</th>
                    <th class="px-4 py-3 text-left font-semibold">Balance</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-right font-semibold">Action</th>
                </tr>
            </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse($agreements as $index => $agreement)
                                <tr>
                                    <td class="px-4 py-3 text-gray-900">
                                        {{ ($agreements->currentPage() - 1) * $agreements->perPage() + $index + 1 }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        #{{ $agreement->sale->invoice_number }}
                                        @if($agreement->agreement_number)
                                            <div class="text-xs text-blue-600 font-bold">{{ $agreement->agreement_number }}</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $agreement->customer->name }} <br>
                                        <span class="text-xs text-gray-500">
                                            {{ $agreement->customer->phone }}
                                        </span>
                                    </td>

                                
                                <td class="px-4 py-3">
                            @foreach($agreement->sale->items as $saleItem)
                                {{ $saleItem->item?->name ?? 'N/A' }}
                                @if(!$loop->last), @endif
                            @endforeach
                        </td>

                                                <td class="px-4 py-3">
                            @foreach($agreement->sale->items as $saleItem)
                                {{ $saleItem->item?->emi_lock_mode ?? 'N/A' }}
                                @if(!$loop->last), @endif
                            @endforeach
                        </td>

                                    <td class="px-4 py-3">
                                        @foreach($agreement->sale->items as $saleItem)
                                            {{ $saleItem->item->emi_number ?? 'N/A' }}
                                            @if(!$loop->last), @endif
                                        @endforeach
                                    </td>

                        <td class="px-4 py-3">
                            {{ $agreement->guarantor_name ?? 'N/A' }}
                        </td>

                        <td class="px-4 py-3">
                            Rs. {{ number_format($agreement->down_payment_amount, 2) }}
                        </td>

                        <td class="px-4 py-3">
                            Rs. {{ number_format($agreement->total_invoice_value, 2) }}
                        </td>

                        <td class="px-4 py-3 font-semibold text-red-600">
                            Rs. {{ number_format($agreement->balance_amount, 2) }}
                        </td>

                        <td class="px-4 py-3">
                            @php
                                $color = $agreement->status === 'paid'
                                    ? 'green'
                                    : ($agreement->status === 'active' ? 'blue' : 'red');
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-{{ $color }}-100 text-{{ $color }}-700">
                                {{ ucfirst($agreement->status) }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right flex justify-end flex-wrap gap-2">
                            <a href="{{ route('admin.installments.show', $agreement->id) }}"
                               class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-semibold text-xs">
                                View
                            </a>

                            @php
                                $rolePrefix = auth()->user()->isAdmin() ? 'admin' : 'cashier';
                            @endphp
                            <a href="{{ route($rolePrefix . '.installment-agreement.edit', $agreement->sale->id) }}"
                               class="px-3 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 font-semibold text-xs">
                                Edit
                            </a>

                            <a href="{{ route($rolePrefix . '.installment-agreement.print', $agreement->sale->id) }}"
                               target="_blank"
                               class="px-3 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 font-semibold text-xs whitespace-nowrap">
                                Agreement Form
                            </a>

                            <a href="{{ route($rolePrefix . '.installment-agreement.application-form', $agreement->sale->id) }}"
                               target="_blank"
                               class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-semibold text-xs whitespace-nowrap">
                                Application Form
                            </a>

                            @php
                                $lastDelayPayment = $agreement->payments->where('fine_amount', '>', 0)->sortByDesc('created_at')->first();
                            @endphp

                            @if($lastDelayPayment)
                                <a href="{{ route('admin.installments.payments.disconnect-receipt', $lastDelayPayment->id) }}"
                                   target="_blank"
                                   class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold text-xs whitespace-nowrap" title="Download last delay payment receipt">
                                    Delay Receipt
                                </a>
                            @endif
                            <form action="{{ route('admin.installments.destroy', $agreement->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this agreement? This will also delete the associated sale and customer if no other records exist.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 font-semibold text-xs whitespace-nowrap" title="Delete only this agreement">
                                    Delete Agreement
                                </button>
                            </form>

                            <form action="{{ route('admin.installments.customer.destroy', $agreement->customer->id) }}" method="POST" onsubmit="return deleteCustomerWithPassword(this);">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="password" class="customer-delete-password">
                                <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-semibold text-xs whitespace-nowrap" title="Delete customer and all their records">
                                    Delete Customer
                                </button>
                            </form>
                           <!--- @if ($agreement->status === 'paid_off')
                                <form action="{{ route('admin.installments.send-payment-completed-notification', $agreement) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 font-semibold">
                                        Pass Payment Completed
                                    </button>
                                </form>
                            @endif-->

                            @if ($agreement->customer_nic_front || $agreement->customer_nic_back)
                                <div class="mt-2">
                                    <span class="font-semibold">Customer NIC:</span>
                                    @if ($agreement->customer_nic_front)
                                        <a href="{{ asset('storage/' . $agreement->customer_nic_front) }}" target="_blank" class="text-blue-600 hover:underline">Front</a>
                                    @endif
                                    @if ($agreement->customer_nic_back)
                                        <a href="{{ asset('storage/' . $agreement->customer_nic_back) }}" target="_blank" class="ml-2 text-blue-600 hover:underline">Back</a>
                                    @endif
                                </div>
                            @endif

                            @if ($agreement->guarantor_nic_front || $agreement->guarantor_nic_back)
                                <div class="mt-2">
                                    <span class="font-semibold">Guarantor NIC:</span>
                                    @if ($agreement->guarantor_nic_front)
                                        <a href="{{ asset('storage/' . $agreement->guarantor_nic_front) }}" target="_blank" class="text-blue-600 hover:underline">Front</a>
                                    @endif
                                    @if ($agreement->guarantor_nic_back)
                                        <a href="{{ asset('storage/' . $agreement->guarantor_nic_back) }}" target="_blank" class="ml-2 text-blue-600 hover:underline">Back</a>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-4 py-6 text-center text-gray-500">
                            No installment agreements found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
     

    </div>

</div>

    {{-- Pagination Links --}}
                    <div class="mt-3">
                     {{ $agreements->links() }}

                    </div>

<script>
    function scrollTable(amount) {
        const container = document.getElementById('table-scroll');
        container.scrollLeft += amount;
    }

    function deleteCustomerWithPassword(form) {
        if (!confirm('WARNING: Are you sure you want to delete this CUSTOMER and ALL their data (sales, payments, agreements)? This cannot be undone.')) {
            return false;
        }
        
        const password = prompt("Please enter the security password (876) to delete the customer:");
        if (password === null) return false;
        
        if (password !== '876') {
            alert('Invalid password!');
            return false;
        }
        
        form.querySelector('.customer-delete-password').value = password;
        return true;
    }
</script>

@endsection

