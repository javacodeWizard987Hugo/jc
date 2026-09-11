@extends('layouts.app')

@section('title', 'Overdue Installments')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">

    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md border-2 border-red-200 p-6 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">⏰ Overdue Installments</h1>
            <p class="text-sm text-gray-600">
                Customers who have missed installment payments
            </p>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <p class="text-sm font-semibold text-gray-700">Total Overdue</p>
                <p class="text-lg font-bold text-red-600">
                    {{ $overdueAgreements->count() }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.installments.overdue-print', ['start_date' => $startDate, 'end_date' => $endDate, 'search' => $search, 'format' => 'pdf']) }}" class="px-4 py-2 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download PDF
                </a>
                <a href="{{ route('admin.installments.overdue-export', ['start_date' => $startDate, 'end_date' => $endDate, 'search' => $search]) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </a>
                <a href="{{ route('admin.installments.overdue-print', ['start_date' => $startDate, 'end_date' => $endDate, 'search' => $search]) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print List
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
        <form action="{{ route('admin.installments.overdue') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search Name/Phone</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Customer name or phone..." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date (Up to)</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
            </div>
            <div>
                <button type="submit" class="px-6 py-2 bg-gray-800 text-red rounded-lg font-bold hover:bg-gray-900">
                    Filter
                </button>
            </div>
            <div>
                <a href="{{ route('admin.installments.overdue') }}" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg font-bold hover:bg-gray-300">
                    Clear
                </a>
            </div>
        </form>
    </div>

    @if($overdueAgreements->isNotEmpty())
        <div class="bg-white rounded-lg shadow-md overflow-x-auto mb-6 border">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Guarantor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Invoice/EMI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Call History</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Monthly Premium</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Late Days</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Outstanding</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($overdueAgreements as $agreement)
                        @php
                            $sale = $agreement->sale;
                            $customer = $agreement->customer;
                            $nextDueDate = $agreement->next_due_date;
                            
                            $daysOverdue = 0;
                            if ($nextDueDate instanceof \Carbon\Carbon) {
                                $daysOverdue = now()->startOfDay()->diffInDays($nextDueDate->startOfDay(), false);
                                $daysOverdue = abs($daysOverdue);
                            }
                            
                            $totalPaid = $agreement->payments->sum('amount');
                            $emiNumber = floor($totalPaid / $agreement->monthly_installment_amount) + 1;
                            $totalQty = $sale ? $sale->items->sum('quantity') : 0;
                        @endphp
                        <tr class="hover:bg-red-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $customer->name }}</div>
                                <div class="text-xs text-gray-500">{{ $customer->phone ?? 'N/A' }}</div>
                            </td>
                            <!-- Requirement 6: Guarantee Detail add on outstanding report -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $agreement->guarantor_name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $agreement->guarantor_mobile_number ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-semibold">#{{ $sale->invoice_number }}</div>
                            <div class="text-xs text-gray-500">EMI: {{ $agreement->combined_emi_numbers }}</div>
                                <div class="text-xs text-gray-500">Lock: {{ $agreement->emi_lock_mode ?? 'None' }}</div>
                            </td>
                            <!-- Requirement 11: Times calling and record client feedback -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @for($i=1; $i<=5; $i++)
                                        @php
                                            $statusField = "call_{$i}_status";
                                            $feedbackField = "call_{$i}_feedback";
                                            $status = $agreement->$statusField;
                                            $feedback = $agreement->$feedbackField;
                                            $badgeColor = $status == 'Answered' ? 'bg-green-100 text-green-800' : ($status == 'Not Answered' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800');
                                        @endphp
                                        <button type="button" 
                                                onclick="openCallModal({{ $agreement->id }}, {{ $i }}, @js($status), @js($feedback))"
                                                class="px-2 py-0.5 text-[10px] rounded {{ $badgeColor }} border border-gray-300 hover:opacity-80 transition-opacity"
                                                title="Call {{ $i }}: {{ $status ?? 'N/A' }} - {{ $feedback ?? 'No feedback' }}">
                                            Call {{ $i }}
                                        </button>
                                    @endfor
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                Rs. {{ number_format($agreement->monthly_installment_amount, 2) }}
                                <div class="text-[10px] text-gray-400">Qty: {{ $totalQty }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $nextDueDate instanceof \Carbon\Carbon ? $nextDueDate->format('Y-m-d') : $nextDueDate }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                    {{ $daysOverdue }} Delay Days
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-red-600">
                                Rs. {{ number_format($agreement->balance_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.installments.show', $agreement) }}" class="text-blue-600 hover:text-blue-900" title="View Agreement">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.installments.send-overdue-reminder', $agreement) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-yellow-600 hover:text-yellow-900" title="Send Reminder">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                        </button>
                                    </form>
                                    @if(!$agreement->disconnected_at)
                                        <form action="{{ route('admin.installments.disconnect', $agreement) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to disconnect this device?')">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Disconnect Device">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6 text-center">
            <h3 class="text-lg font-bold text-green-900">
                ✅ No Overdue Installments
            </h3>
            <p class="text-sm text-gray-700">
                All installment payments are up to date within the selected criteria.
            </p>
        </div>
    @endif

</div>

<!-- Call Log Modal -->
<div id="callLogModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">Record Call Feedback</h3>
            <form id="callLogForm" method="POST" action="" class="mt-4 text-left">
                @csrf
                <input type="hidden" name="call_index" id="callIndexInput">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="callStatusInput" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" required>
                        <option value="">Select Status</option>
                        <option value="Answered">Answered</option>
                        <option value="Not Answered">Not Answered</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Feedback</label>
                    <textarea name="feedback" id="callFeedbackInput" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" placeholder="Enter customer feedback..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeCallModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Save Log</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openCallModal(agreementId, callIndex, currentStatus, currentFeedback) {
        const modal = document.getElementById('callLogModal');
        const form = document.getElementById('callLogForm');
        const title = document.getElementById('modalTitle');
        const indexInput = document.getElementById('callIndexInput');
        const statusInput = document.getElementById('callStatusInput');
        const feedbackInput = document.getElementById('callFeedbackInput');

        title.innerText = `Record Call ${callIndex} Feedback`;
        indexInput.value = callIndex;
        statusInput.value = currentStatus || "";
        feedbackInput.value = currentFeedback || "";
        
        form.action = `/admin/installments/${agreementId}/update-call-log`;
        
        modal.classList.remove('hidden');
    }

    function closeCallModal() {
        document.getElementById('callLogModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('callLogModal');
        if (event.target == modal) {
            closeCallModal();
        }
    }
</script>
@endsection
