@extends('layouts.app')

@section('title', 'On Date Installments Report')

@section('content')
<div class="page-header">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">On Date Installments Report</h1>
            <p class="text-gray-600">Customers who paid their installment premium on the due date.</p>
        </div>
        <div class="flex space-x-2 print:hidden">
            <button onclick="window.print()" class="btn btn-secondary">
                <span class="mr-2">🖨️</span> Print Report
            </button>
        </div>
    </div>
</div>

<div class="content-card p-6 mb-6 print:hidden">
    <form action="{{ route('admin.reports.on-date-installments') }}" method="GET" class="flex items-end gap-4">
        <div class="flex-1">
            <label for="date" class="form-label">Select Date</label>
            <input type="date" name="date" id="date" value="{{ $date }}" class="form-input">
        </div>
        <button type="submit" class="btn btn-primary">Filter Report</button>
    </form>
</div>

<div class="content-card overflow-hidden">
    <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
        <h2 class="font-bold text-gray-700">Report for: {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</h2>
        <span class="badge badge-info">{{ $reportData->count() }} Customers Found</span>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                      <th>Guarantor Name</th>
                    <th>Guarantor Phone</th>
                    <th>EMI Numbers</th>
                    <th>Premium (Rs.)</th>
                    <th>Balance (Rs.)</th>
                    <th>Total Paid Premium Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $row)
                    <tr>
                        <td class="font-medium">{{ $row['customer_name'] }}</td>
                        <td>{{ $row['phone'] }}</td>
                            <td>{{ $row['guarantor_name'] }}</td>
                        <td>{{ $row['guarantor_phone'] }}</td>
                        <td>{{ $row['emi_number'] }}</td>
                        <td>{{ number_format($row['premium'], 2) }}</td>
                        <td>{{ number_format($row['balance'], 2) }}</td>
                        <td>{{ $row['paid_quantity'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">
                            No customers found who paid on this date.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($reportData->count() > 0)
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="2" class="text-right">TOTAL</td>
                        <td>{{ number_format($reportData->sum('premium'), 2) }}</td>
                        <td>{{ number_format($reportData->sum('balance'), 2) }}</td>
                        <td>-</td>
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
        }
    }
</style>
@endsection
