@extends('layouts.app')

@section('title', 'Send SMS Reminders')

@section('content')
<style>
:root {
    --primary: #4e73df;
    --secondary: #1cc88a;
    --warning: #f6c23e;
    --danger: #e74a3b;
    --light-bg: #f8f9fc;
}

.card {
    border-radius: 14px;
    border: none;
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
}
.card-header {
    background: linear-gradient(135deg, var(--primary), #224abe);
    color: #fff;
    border-radius: 14px 14px 0 0;
}
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
}
.badge-success { background: var(--secondary); color: #fff; }
.badge-warning { background: var(--warning); color: #000; }
.badge-info { background: #36b9cc; color: #fff; }
.badge-danger { background: var(--danger); color: #fff; }
.badge-blue { background: #4e73df; color: #fff; }
.customer-header {
    background-color: #f1f4f9;
    padding: 10px 15px;
    border-radius: 8px;
    margin-top: 20px;
    margin-bottom: 10px;
    border-left: 5px solid var(--primary);
}

.table-summary td {
    background: #f8f9fc;
    font-weight: bold;
}
</style>

<div class="container mx-auto">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Send SMS Reminders</h1>
        <a href="{{ route('admin.reports.installment-income') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded text-sm">
            View Full Income Report
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded p-4 mb-6 card">
        <div class="flex flex-wrap justify-between items-end gap-4">
            <form action="{{ route('admin.installments.sms-reminder') }}" method="GET" class="flex items-end gap-4">
                <div>
                    <label for="reminder_date" class="block text-gray-700 text-sm font-bold mb-2">Reminder Date:</label>
                    <input type="date" name="reminder_date" id="reminder_date" value="{{ $selectedDate }}" 
                        class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Filter by Day ({{ $dayOfMonth }})
                </button>
            </form>

            <a href="{{ route('admin.installments.sms-reminder-print', ['date' => $selectedDate]) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print SMS Reminder List
            </a>
        </div>
    </div>

    <form action="{{ route('admin.installments.send-sms-reminders') }}" method="POST">
        @csrf
        <div class="mb-4 flex gap-2">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Send Selected Reminders
            </button>
            <button type="button" id="select-all-btn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                Select All
            </button>
        </div>

        @forelse ($data as $customerData)
            <div class="customer-header">
                <h3 class="text-lg font-bold">👤 Customer: {{ $customerData->customer->name }} ({{ $customerData->customer->phone }})</h3>
            </div>

            @foreach ($customerData->agreements as $row)
                <div class="bg-white shadow-md rounded mb-6 overflow-hidden card">
                    <div class="p-4 border-b bg-gray-50">
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <input type="checkbox" name="agreement_ids[]" value="{{ $row->agreement->id }}" class="agreement-checkbox mr-2">
                                <span class="font-bold text-blue-600">#{{ $row->agreement->sale->invoice_number ?? 'N/A' }}</span>
                                <span class="ml-4 text-gray-600">{{ $row->agreement->created_at->format('Y-m-d') }}</span>
                            </div>
                            <div>
                                <span class="badge badge-{{ $row->agreement->status == 'paid_off' ? 'success' : 'warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $row->agreement->status)) }}
                                </span>
                                @php
                                    $overdueBadgeClass = 'secondary';
                                    if ($row->overdue_status === 'Overdue') $overdueBadgeClass = 'danger';
                                    elseif ($row->overdue_status === 'Due Soon') $overdueBadgeClass = 'warning';
                                    elseif ($row->overdue_status === 'On Track') $overdueBadgeClass = 'success';
                                @endphp
                                <span class="badge badge-{{ $overdueBadgeClass }} ml-2">{{ $row->overdue_status }}</span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs border bg-white">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-2 py-1 border text-left">Date</th>
                                        <th class="px-2 py-1 border text-left">Total</th>
                                        <th class="px-2 py-1 border text-left">DownPmt</th>
                                        <th class="px-2 py-1 border text-left">Principal</th>
                                        <th class="px-2 py-1 border text-left">Interest</th>
                                        <th class="px-2 py-1 border text-left">Paid</th>
                                        <th class="px-2 py-1 border text-left">Interest Paid</th>
                                        <th class="px-2 py-1 border text-left">Outstanding</th>
                                        <th class="px-2 py-1 border text-left">O/S Interest</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="px-2 py-1 border">{{ $row->agreement->created_at->format('Y-m-d') }}</td>
                                        <td class="px-2 py-1 border font-bold">{{ number_format($row->agreement->total_invoice_value, 2) }}</td>
                                        <td class="px-2 py-1 border">{{ number_format($row->agreement->down_payment_amount, 2) }}</td>
                                        <td class="px-2 py-1 border">{{ number_format($row->principal, 2) }}</td>
                                        <td class="px-2 py-1 border">{{ number_format($row->agreement->interest_service_charge, 2) }}</td>
                                        <td class="px-2 py-1 border font-bold">{{ number_format($row->total_paid, 2) }}</td>
                                        <td class="px-2 py-1 border text-success font-bold">{{ number_format($row->interest_paid, 2) }}</td>
                                        <td class="px-2 py-1 border text-danger font-bold">{{ number_format($row->outstanding_principal, 2) }}</td>
                                        <td class="px-2 py-1 border text-danger">{{ number_format($row->outstanding_interest, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-bold mb-2 text-sm text-gray-700">📅 Monthly Installment Schedule (FIFO Allocated)</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs border">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-2 py-1 border text-left">Due Date</th>
                                            <th class="px-2 py-1 border text-left">Due Amt</th>
                                            <th class="px-2 py-1 border text-left">Paid Amt</th>
                                            <th class="px-2 py-1 border text-left">Paid Date</th>
                                            <th class="px-2 py-1 border text-left">Interest</th>
                                            <th class="px-2 py-1 border text-left">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($row->monthly_breakdown as $details)
                                            <tr class="{{ Carbon\Carbon::parse($details['due_date'])->day == $dayOfMonth && Carbon\Carbon::parse($details['due_date'])->format('Y-m') == Carbon\Carbon::parse($selectedDate)->format('Y-m') ? 'bg-yellow-50 font-bold border-l-4 border-yellow-400' : '' }}">
                                                <td class="px-2 py-1 border">{{ $details['due_date'] }}</td>
                                                <td class="px-2 py-1 border">{{ number_format($details['due_amount'], 2) }}</td>
                                                <td class="px-2 py-1 border">{{ number_format($details['paid_amount'], 2) }}</td>
                                                <td class="px-2 py-1 border">{{ $details['paid_date'] ?? '-' }}</td>
                                                <td class="px-2 py-1 border">{{ number_format($details['monthly_interest'], 2) }}</td>
                                                <td class="px-2 py-1 border">
                                                
                                                   <span class="badge badge-{{ $details['status'] == 'paid_off' ? 'success' : ($details['status'] == 'partially_paid' ? 'blue' : 'info') }}">
              
                                                        {{ ucfirst(str_replace('_', ' ', $details['status'])) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-bold mb-2 text-sm text-gray-700">💳 Actual Payment History</h4>
                            <div class="overflow-x-auto mb-4">
                                <table class="min-w-full text-xs border">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-2 py-1 border text-left">Date</th>
                                            <th class="px-2 py-1 border text-left">Amount</th>
                                            <th class="px-2 py-1 border text-left">Method</th>
                                            <th class="px-2 py-1 border text-left">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($row->agreement->payments as $payment)
                                            <tr>
                                                <td class="px-2 py-1 border">{{ $payment->payment_date }}</td>
                                                <td class="px-2 py-1 border font-bold">{{ number_format($payment->amount, 2) }}</td>
                                                <td class="px-2 py-1 border">{{ ucfirst($payment->payment_method) }}</td>
                                                <td class="px-2 py-1 border">
                                                    {{ $payment->notes ?: '-' }}
                                                    <button type="button" class="text-red-600 hover:text-red-900 ml-2 font-bold" onclick="deletePayment({{ $payment->id }})">Del</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="px-2 py-1 border text-center text-gray-500">No payments recorded yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr class="font-bold">
                                            <td class="px-2 py-1 border text-right">Total Installments</td>
                                            <td class="px-2 py-1 border">{{ number_format($row->total_paid, 2) }}</td>
                                            <td colspan="2" class="px-2 py-1 border"></td>
                                        </tr>
                                        <tr class="font-bold">
                                            <td class="px-2 py-1 border text-right">Down Payment</td>
                                            <td class="px-2 py-1 border">{{ number_format($row->agreement->down_payment_amount, 2) }}</td>
                                            <td colspan="2" class="px-2 py-1 border"></td>
                                        </tr>
                                        <tr class="font-bold bg-blue-50">
                                            <td class="px-2 py-1 border text-right">Total Collected</td>
                                            <td class="px-2 py-1 border text-blue-700">{{ number_format($row->total_collected, 2) }}</td>
                                            <td colspan="2" class="px-2 py-1 border"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @empty
            <div class="bg-white shadow-md rounded p-10 text-center card">
                <p class="text-gray-500 font-bold">No installments due on day {{ $dayOfMonth }}.</p>
                <p class="text-gray-400 text-sm mt-2">Try selecting a different reminder date or check the full report.</p>
            </div>
        @endforelse
    </form>
</div>

<script>
    document.getElementById('select-all-btn').addEventListener('click', function() {
        let checkboxes = document.querySelectorAll('.agreement-checkbox');
        let allChecked = true;
        for (let checkbox of checkboxes) {
            if (!checkbox.checked) {
                allChecked = false;
                break;
            }
        }
        for (let checkbox of checkboxes) {
            checkbox.checked = !allChecked;
        }
        this.textContent = allChecked ? 'Select All' : 'Deselect All';
    });
</script>
@push('scripts')
<script>
function deletePayment(paymentId) {
    const password = prompt('Please enter password to delete payment:');
    if (password === '876') {
        if (confirm('Are you sure you want to delete this payment record?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/installments/payments/${paymentId}`;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            
            const passwordInput = document.createElement('input');
            passwordInput.type = 'hidden';
            passwordInput.name = 'password';
            passwordInput.value = password;
            
            form.appendChild(methodInput);
            form.appendChild(csrfInput);
            form.appendChild(passwordInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    } else if (password !== null) {
        alert('Incorrect password!');
    }
}
</script>
@endpush

@endsection
