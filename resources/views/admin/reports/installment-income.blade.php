@extends('layouts.app')

@section('title', 'Installment Income Report')

@section('content')

<style>
:root {
    --primary: #4e73df;
    --secondary: #1cc88a;
    --warning: #f6c23e;
    --danger: #e74a3b;
    --light-bg: #f8f9fc;
}

/* Layout */
.scroll-wrapper {
    max-height: 75vh;
    overflow: auto;
    border-radius: 10px;
    border: 1px solid #e3e6f0;
    background: #fff;
}

/* Cards */
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

/* Tables */
.table thead th {
    background: var(--light-bg);
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    white-space: nowrap;
}
.table td {
    vertical-align: middle;
}

/* Badges */
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
}
.badge-success { background: var(--secondary); color: #fff; }
.badge-warning { background: var(--warning); color: #000; }
.badge-info { background: #36b9cc; color: #fff; }
.badge-danger { background: var(--danger); color: #fff; }
.badge-secondary { background: #858796; color: #fff; }

/* Filters */
.filter-bar .form-control,
.filter-bar .btn {
    height: 38px;
}

.customer-row {
    background-color: #f1f3f9 !important;
    font-weight: bold;
}

.agreement-row {
    background-color: #ffffff;
}

.details-container {
    padding: 15px;
    background-color: #fdfdfd;
}

.section-title {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 10px;
    color: var(--primary);
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('toggle-summary-btn');
    const card = document.getElementById('daily-summary-card');
    if(btn && card) {
        btn.addEventListener('click', () => {
            card.style.display = card.style.display === 'none' ? 'block' : 'none';
            btn.textContent = card.style.display === 'none' ? 'View Daily Summary' : 'Hide Daily Summary';
        });
    }
});

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

<div class="container-fluid">

{{-- ================= DAILY SUMMARY ================= --}}
<div class="row mb-4">
    <div class="col-md-12 text-right">
        <button id="toggle-summary-btn" class="btn btn-outline-primary btn-sm mb-2">View Daily Summary</button>
    </div>
    <div class="col-md-12" id="daily-summary-card" style="display:none;">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">📊 Daily Income Summary</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="form-inline mb-3">
                    <label class="mr-2 font-weight-bold">Date</label>
                    <input type="date" name="summary_date" class="form-control mr-2"
                           value="{{ $summaryDate->format('Y-m-d') }}">
                    <button class="btn btn-primary btn-sm">View</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th class="text-right">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailyIncomeSummary as $method => $total)
                            <tr>
                                <td>{{ ucfirst(str_replace('_',' ',$method)) }}</td>
                                <td class="text-right">{{ number_format($total,2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted">No income records for this date</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold bg-light">
                                <td>Grand Total</td>
                                <td class="text-right">{{ number_format($dailyIncomeSummary->sum(),2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= INSTALLMENT REPORT ================= --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">💰 Installment Income Report</h4>
    </div>

    <div class="card-body">
        {{-- FILTER BAR --}}
        <form method="GET" class="form-inline mb-4 filter-bar">
            <input type="text" name="customer_search" class="form-control mr-2 mb-2"
                     placeholder="name / phone / NIC"
                   value="{{ request('customer_search') }}">

            <select name="customer_id" class="form-control mr-2 mb-2">
                <option value="">All Customers</option>
                @foreach($allCustomers as $customer)
                    <option value="{{ $customer->id }}"
                        {{ request('customer_id')==$customer->id?'selected':'' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>

            <input type="month" name="month" class="form-control mr-2 mb-2" value="{{ request('month') }}">
            <input type="date" name="start_date" class="form-control mr-2 mb-2" value="{{ request('start_date') }}">
            <input type="date" name="end_date" class="form-control mr-2 mb-2" value="{{ request('end_date') }}">

            <button class="btn btn-primary btn-sm mb-2">Filter Results</button>
            <a href="{{ route('admin.reports.installment-income') }}" class="btn btn-secondary btn-sm mb-2 ml-1">Reset</a>
        </form>

        {{-- MAIN TABLE --}}
        <div class="scroll-wrapper">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Agreement / Date</th>
                        <th>Total Value</th>
                        <th>Profit</th>
                        <th>Down Payment</th>
                        <th>Principal</th>
                        <th>Interest</th>
                        <th>Paid</th>
                        <th>Int. Paid</th>
                        <th>Outstanding</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($data as $customerData)
                        <tr class="customer-row">
                             <td colspan="11">
                                👤 Customer: {{ $customerData->customer->name }} ({{ $customerData->customer->phone }})
                                @php
                                    $customerItems = $customerData->agreements->flatMap(function($r) {
                                        return $r->agreement->sale->items->map(fn($si) => $si->item->name ?? 'Unknown');
                                    })->unique()->implode(', ');
                                @endphp
                                @if($customerItems)
                                    <span class="ml-2 text-dark font-weight-normal" style="font-size: 0.85em;">- [{{ $customerItems }}]</span>
                                @endif
                            </td>
                        </tr>
                        @foreach($customerData->agreements as $row)
                            <tr class="agreement-row font-weight-bold">
                                  <td>
                                     <span class="text-primary">#{{ $row->agreement->sale->invoice_number ?? $row->agreement->id }}</span>
                                     @php
                                         $itemNames = $row->agreement->sale->items->map(function($si) {
                                             return ($si->item->name ?? 'Unknown') . ($si->item->category ? ' (' . $si->item->category->name . ')' : '');
                                         })->implode(', ');
                                     @endphp
                                     @if($itemNames)
                                         <br><span class="text-dark" style="font-size: 10px; font-weight: normal;">{{ $itemNames }}</span>
                                     @endif
                                     @if($row->agreement->agreement_number)
                                        <br><span class="text-info" style="font-size: 10px;">{{ $row->agreement->agreement_number }}</span>
                                     @endif
                                     <br>
                                    <small class="text-muted">{{ $row->agreement->created_at->format('Y-m-d') }}</small>
                                </td>
                                <td>{{ number_format($row->agreement->total_invoice_value, 2) }}</td>
                                <td class="{{ $row->profit_class }}">{{ number_format($row->profit, 2) }}</td>
                                <td>{{ number_format($row->agreement->down_payment_amount, 2) }}</td>
                                <td>{{ number_format($row->principal, 2) }}</td>
                                <td>{{ number_format($row->agreement->interest_service_charge, 2) }}</td>
                                <td>{{ number_format($row->total_paid, 2) }}</td>
                                <td class="text-success">{{ number_format($row->interest_paid, 2) }}</td>
                                <td class="text-danger">{{ number_format($row->outstanding_principal + $row->outstanding_interest, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $row->status == 'paid_off' ? 'success' : 'warning' }}">
                                        {{ ucfirst(str_replace('_', ' ', $row->status)) }}
                                    </span>
                                    <br>
                                    @php
                                        $badgeClass = 'secondary';
                                        if ($row->overdue_status === 'Overdue') $badgeClass = 'danger';
                                        elseif ($row->overdue_status === 'Due Soon') $badgeClass = 'warning';
                                        elseif ($row->overdue_status === 'On Track') $badgeClass = 'success';
                                    @endphp
                                    <small class="badge badge-{{ $badgeClass }} mt-1" style="font-size: 9px; padding: 2px 6px;">{{ $row->overdue_status }}</small>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <a href="{{ route('admin.installments.show', $row->agreement->id) }}" class="btn btn-sm btn-info mr-1" title="View Agreement">View</a>
                                        
                                        @if ($row->agreement->status === 'paid_off')
                                            <form action="{{ route('admin.installments.send-payment-completed-notification', $row->agreement->id) }}" method="POST" class="mr-1">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Send payment completed SMS?')" title="Send Completed SMS">SMS</button>
                                            </form>
                                        @endif

                                        <form action="{{ route('admin.installments.destroy', $row->agreement->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this agreement? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                           <!-- <button type="submit" class="btn btn-sm btn-danger" title="Delete Agreement">Del</button> -->
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- BREAKDOWN SECTION --}}
                            <tr>
                                <td colspan="11" class="details-container">
                                    <div class="row">
                                        {{-- Monthly Installment Breakdown --}}
                                        <div class="col-lg-7 col-md-12">
                                            <div class="section-title">📅 Monthly Installment Schedule (FIFO Allocated)</div>
                                            <table class="table table-sm table-bordered bg-white">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Due Date</th>
                                                        <th class="text-right">Due Amt</th>
                                                        <th class="text-right">Paid Amt</th>
                                                        <th>Paid Date</th>
                                                        <th class="text-right">Interest</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($row->monthly_breakdown as $details)
                                                        <tr>
                                                            <td>{{ $details['due_date'] }}</td>
                                                            <td class="text-right">{{ number_format($details['due_amount'], 2) }}</td>
                                                            <td class="text-right font-weight-bold {{ $details['paid_amount'] > 0 ? 'text-success' : '' }}">
                                                                {{ number_format($details['paid_amount'], 2) }}
                                                            </td>
                                                            <td>{{ $details['paid_date'] ?: '-' }}</td>
                                                            <td class="text-right">{{ number_format($details['monthly_interest'], 2) }}</td>
                                                            <td>
                                                                <span class="badge badge-{{ $details['status'] == 'paid_off' ? 'success' : ($details['status'] == 'partially_paid' ? 'warning' : 'info') }}" style="font-size: 9px; padding: 2px 6px;">
                                                                    {{ ucfirst(str_replace('_', ' ', $details['status'])) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        {{-- Actual Payment Records --}}
                                        <div class="col-lg-5 col-md-12">
                                            <div class="section-title">💳 Actual Payment History</div>
                                            <table class="table table-sm table-bordered bg-white">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th class="text-right">Amount</th>
                                                        <th class="text-right">Fine</th>
                                                        <th class="text-right">Total</th>
                                                        <th>Method</th>
                                                        <th>Notes</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($row->agreement->payments->sortByDesc('payment_date') as $payment)
                                                        <tr>
                                                            <td>{{ $payment->payment_date }}</td>
                                                            <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
                                                            <td class="text-right">{{ number_format($payment->fine_amount, 2) }}</td>
                                                            <td class="text-right font-weight-bold text-primary">{{ number_format($payment->amount + $payment->fine_amount, 2) }}</td>
                                                            <td><small>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</small></td>
                                                            <td><small>{{ $payment->notes ?: '-' }}</small></td>
                                                            <td>
                                                                <div class="d-flex gap-1">
                                                                    <a href="{{ route('admin.installments.payments.receipt', $payment->id) }}" target="_blank" class="btn btn-xs btn-primary p-1" style="font-size: 8px;" title="Print POS Receipt">POS</a>
                                                                    <a href="{{ route('admin.installments.payments.disconnect-receipt', $payment->id) }}" target="_blank" class="btn btn-xs btn-danger p-1" style="font-size: 8px;" title="Print Disconnect Receipt">Disc</a>
                                                                    <button type="button" class="btn btn-xs btn-warning p-1" style="font-size: 8px;" title="Delete Payment" onclick="deletePayment({{ $payment->id }})">Del</button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted">No payments recorded yet.</td>
                                                        </tr>
                                                    @endforelse
                                                    <tr class="bg-light font-weight-bold">
                                                        <td>Total Installments</td>
                                                        <td class="text-right text-primary">{{ number_format($row->total_paid, 2) }}</td>
                                                        <td colspan="5"></td>
                                                    </tr>
                                                    <tr class="bg-light">
                                                        <td>Down Payment</td>
                                                        <td class="text-right">{{ number_format($row->agreement->down_payment_amount, 2) }}</td>
                                                        <td class="text-right">0.00</td>
                                                        <td class="text-right">{{ number_format($row->agreement->down_payment_amount, 2) }}</td>
                                                        <td colspan="3"><small>{{ ucfirst(str_replace('_', ' ', $row->agreement->down_payment_method)) }}</small></td>
                                                    </tr>
                                                    <tr class="table-info font-weight-bold">
                                                        <td>Total Collected</td>
                                                        <td class="text-right" colspan="3">{{ number_format($row->total_paid + $row->agreement->down_payment_amount + $row->agreement->payments->sum('fine_amount'), 2) }}</td>
                                                        <td colspan="3"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="11" class="text-center p-4">
                                <div class="text-muted">No installment agreements found matching your criteria.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4">
            {{ $agreements->links() }}
        </div>
    </div>
</div>

</div>

@endsection
