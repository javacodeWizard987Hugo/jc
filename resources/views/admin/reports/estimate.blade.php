@extends('layouts.app')

@section('title', 'Estimate Report')

@section('content')
<div class="page-header">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Monthly Estimate Report</h1>
            <p class="text-gray-600">Expected installment collections for the selected month.</p>
        </div>
        <div class="flex space-x-2 print:hidden">
          <a href="{{ route('admin.estimate-report-print', ['month' => $selectedMonth, 'start_date' => $startDate, 'end_date' => $endDate, 'print' => 1]) }}" target="_blank" class="btn btn-secondary">
    <span class="mr-2">🖨️</span> Print Report
</a>
        </div>
    </div>
</div>

<div class="content-card p-6 mb-6 print:hidden">
 <form action="{{ route('admin.estimate-report') }}" method="GET" class="flex flex-wrap items-end gap-4">
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

<div class="content-card overflow-hidden">
    <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
        <h2 class="font-bold text-gray-700">
            @if($startDate && $endDate)
                Estimate for: {{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($endDate)->format('Y-m-d') }}
            @else
                Estimate for: {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
            @endif
        </h2>
        <div class="flex gap-4">
            <span class="badge badge-info">{{ $reportData->sum('premium_quantity') }} Expected Premiums</span>
            <span class="badge badge-success">Total: Rs. {{ number_format($reportData->sum('premium'), 2) }}</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Guarantor Details</th>
                    <th>EMI Numbers</th>
                    <th>QTY</th>
                    <th>Due Date</th>
                 
                    <th>TOTAL (RS.)</th>
                    <th>Remaining Balance (RS.)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="font-medium">{{ $row['customer_name'] }}</td>
                        <td>{{ $row['phone'] }}</td>
                        <td>
                            {{ $row['guarantor_name'] }}<br>
                            <span class="text-xs text-gray-500">{{ $row['guarantor_phone'] }}</span>
                        </td>
                         <td>{{ $row['combined_emi_numbers'] }}</td>
                        <td>{{ $row['premium_quantity'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($row['due_date'])->format('Y-m-d') }}</td>
                     
                        <td class="font-bold">Rs. {{ number_format($row['premium'], 2) }}</td>
                        <td class="text-red-600">Rs. {{ number_format($row['balance'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-8 text-gray-500">
                            No estimated collections for this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($reportData->count() > 0)
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="5" class="text-right uppercase">Total Premium Quantity</td>
                        <td>{{ $reportData->sum('premium_quantity') }}</td>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td colspan="7" class="text-right uppercase">Total Value of Premium Quantity</td>
                        <td class="text-right">Rs. {{ number_format($reportData->sum('premium'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

<style>
    @media print {
        .sidebar, .top-bar, .print\:hidden {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
        }
        .content-card {
            border: none !important;
            box-shadow: none !important;
        }
        .table {
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }
        th, td {
            padding: 4px !important;
        }
    }
</style>
@endsection
