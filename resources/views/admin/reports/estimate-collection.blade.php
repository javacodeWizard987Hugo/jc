@extends('layouts.app')

@section('title', 'Estimate Collection Report')

@section('content')
<div class="page-header">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Estimate Collection Report</h1>
            <p class="text-gray-600">Comparison between estimated and actual received payments.</p>
        </div>
        <div class="flex space-x-2 print:hidden">
            <a href="{{ route('admin.reports.estimate-collection-print', ['month' => $selectedMonth, 'start_date' => $startDate, 'end_date' => $endDate, 'status' => $statusFilter]) }}" target="_blank" class="btn btn-secondary">
                <span class="mr-2">🖨️</span> Print Report
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('admin.reports.estimate-collection', ['month' => $selectedMonth, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="content-card p-4 hover:shadow-md transition-shadow {{ !$statusFilter ? 'ring-2 ring-primary-green' : '' }}">
        <div class="flex flex-col">
            <span class="text-gray-500 text-sm font-semibold uppercase tracking-wider">Total Estimates</span>
            <span class="text-2xl font-bold text-gray-800">{{ $counts['total'] }}</span>
        </div>
    </a>
    <a href="{{ route('admin.reports.estimate-collection', ['month' => $selectedMonth, 'start_date' => $startDate, 'end_date' => $endDate, 'status' => 'paid_off']) }}" class="content-card p-4 hover:shadow-md transition-shadow {{ $statusFilter === 'paid_off' ? 'ring-2 ring-green-500' : '' }}">
        <div class="flex flex-col">
            <span class="text-green-500 text-sm font-semibold uppercase tracking-wider">Paid Off</span>
            <span class="text-2xl font-bold text-gray-800">{{ $counts['paid_off'] }}</span>
        </div>
    </a>
    <a href="{{ route('admin.reports.estimate-collection', ['month' => $selectedMonth, 'start_date' => $startDate, 'end_date' => $endDate, 'status' => 'partial']) }}" class="content-card p-4 hover:shadow-md transition-shadow {{ $statusFilter === 'partial' ? 'ring-2 ring-blue-500' : '' }}">
        <div class="flex flex-col">
            <span class="text-blue-500 text-sm font-semibold uppercase tracking-wider">Partial Paid</span>
            <span class="text-2xl font-bold text-gray-800">{{ $counts['partial'] }}</span>
        </div>
    </a>
    <a href="{{ route('admin.reports.estimate-collection', ['month' => $selectedMonth, 'start_date' => $startDate, 'end_date' => $endDate, 'status' => 'not_paid']) }}" class="content-card p-4 hover:shadow-md transition-shadow {{ $statusFilter === 'not_paid' ? 'ring-2 ring-red-500' : '' }}">
        <div class="flex flex-col">
            <span class="text-red-500 text-sm font-semibold uppercase tracking-wider">Not Paid</span>
            <span class="text-2xl font-bold text-gray-800">{{ $counts['not_paid'] }}</span>
        </div>
    </a>
</div>

<div class="content-card p-6 mb-6">
    <form action="{{ route('admin.reports.estimate-collection') }}" method="GET" class="flex flex-wrap items-end gap-4">
        <input type="hidden" name="status" value="{{ $statusFilter }}">
        <div>
            <label for="month" class="form-label">Select Month</label>
            <input type="month" name="month" id="month" value="{{ $selectedMonth }}" class="form-input" onchange="if(this.value){ document.getElementById('start_date').value=''; document.getElementById('end_date').value=''; }">
        </div>
        <div class="text-gray-400 self-center mt-6">OR</div>
        <div>
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="form-input" onchange="if(this.value){ document.getElementById('month').value=''; }">
        </div>
        <div>
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="form-input" onchange="if(this.value){ document.getElementById('month').value=''; }">
        </div>
        <button type="submit" class="btn btn-primary">Filter Report</button>
    </form>
</div>


<div class="content-card p-6 mb-6">
    <form action="{{ route('admin.reports.estimate-collection') }}" method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="month" class="form-label">Select Month</label>
            <input type="month" name="month" id="month" value="{{ $selectedMonth }}" class="form-input" onchange="if(this.value){ document.getElementById('start_date').value=''; document.getElementById('end_date').value=''; }">
        </div>
        <div class="text-gray-400 self-center mt-6">OR</div>
        <div>
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="form-input" onchange="if(this.value){ document.getElementById('month').value=''; }">
        </div>
        <div>
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="form-input" onchange="if(this.value){ document.getElementById('month').value=''; }">
        </div>
        <div>
            <label for="status" class="form-label">Collection Status</label>
            <select name="status" id="status" class="form-select">
                <option value="">All Collections</option>
                <option value="paid_off" {{ $statusFilter == 'paid_off' ? 'selected' : '' }}>Paid Off</option>
                <option value="partial" {{ $statusFilter == 'partial' ? 'selected' : '' }}>Partial Paid</option>
                <option value="not_paid" {{ $statusFilter == 'not_paid' ? 'selected' : '' }}>Not Paid</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Filter Report</button>
            <a href="{{ route('admin.reports.estimate-collection-print', ['month' => $selectedMonth, 'start_date' => $startDate, 'end_date' => $endDate, 'status' => $statusFilter]) }}" target="_blank" class="btn btn-secondary">
                🖨️ Print
            </a>
        </div>
    </form>
</div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Details</th>
                    <th>Guarantor</th>
                    <th>EMI Nos.</th>
                    <th>QTY</th>
                    <th>Estimated Value</th>
                    <th>Actual Received</th>
                    <th>Outstanding</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="font-bold text-gray-800">{{ $row['customer_name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $row['phone'] }}</div>
                        </td>
                        <td>
                            <div class="text-sm">{{ $row['guarantor_name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $row['guarantor_phone'] }}</div>
                        </td>
                        <td>{{ $row['combined_emi_numbers'] }}</td>
                        <td class="text-center">{{ $row['premium_quantity'] }}</td>
                        <td class="font-semibold">Rs. {{ number_format($row['estimated_value'], 2) }}</td>
                        <td class="font-semibold text-green-600">Rs. {{ number_format($row['actual_received'], 2) }}</td>
                        <td class="font-bold text-red-600">Rs. {{ number_format($row['estimated_value'] - $row['actual_received'], 2) }}</td>
                        <td>
                            @if($row['status'] === 'paid_off')
                                <span class="badge badge-success">Paid Off</span>
                            @elseif($row['status'] === 'partial')
                                <span class="badge badge-info">Partial</span>
                            @else
                                <span class="badge badge-danger">Not Paid</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.installments.show', $row['agreement_id']) }}" class="btn btn-secondary py-1 px-3 text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-12 text-gray-500">
                            No collection data found for the selected period and filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($reportData->count() > 0)
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="5" class="text-right uppercase">Totals</td>
                        <td>Rs. {{ number_format($reportData->sum('estimated_value'), 2) }}</td>
                        <td class="text-green-600">Rs. {{ number_format($reportData->sum('actual_received'), 2) }}</td>
                        <td class="text-red-600">Rs. {{ number_format($reportData->sum('estimated_value') - $reportData->sum('actual_received'), 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
