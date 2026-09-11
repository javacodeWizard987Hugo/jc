@extends('layouts.app')

@section('title', 'Outstanding Installments Report')

@section('content')
<div class="page-header">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Outstanding Installments Report</h1>
            <p class="text-gray-600">Customers who were due today but have not paid yet.</p>
        </div>
       <!-- <div class="flex space-x-2 print:hidden">
            <button onclick="window.print()" class="btn btn-secondary">
                <span class="mr-2">ЁЯЦия╕П</span> Print Report
            </button>
        </div>-->
    </div>
</div>

<div class="content-card p-6 mb-6 print:hidden">
    <form action="{{ route('admin.reports.outstanding-installments') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label for="search" class="form-label">Search Customer</label>
            <input type="text" name="search" id="search" value="{{ $search }}" class="form-input" placeholder="Name, NIC or Phone">
        </div>
        <div>
            <label for="report_type" class="form-label">Report Type</label>
            <select name="report_type" id="report_type" class="form-select">
                <option value="total" {{ $reportType == 'total' ? 'selected' : '' }}>Total Company Outstanding</option>
                <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Daily Outstanding</option>
            </select>
        </div>
        <div>
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-select">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex space-x-2">
            <button type="submit" class="btn btn-primary flex-1">Filter</button>
            <a href="{{ route('admin.reports.outstanding-installments-print') }}?search={{ $search }}&report_type={{ $reportType }}&category_id={{ $categoryId }}&print=1" target="_blank" class="btn btn-secondary">
                ЁЯЦия╕П Print
            </a>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="content-card p-6 border-l-4 border-blue-500">
        <p class="text-sm font-medium text-gray-500 uppercase">Total Value Issued</p>
        <p class="text-2xl font-bold text-gray-800">Rs. {{ number_format($summary['total_loan_amount'], 2) }}</p>
    </div>
    <div class="content-card p-6 border-l-4 border-green-500">
        <p class="text-sm font-medium text-gray-500 uppercase">Total Paid (Collection)</p>
        <p class="text-2xl font-bold text-gray-800">Rs. {{ number_format($summary['total_paid_amount'], 2) }}</p>
    </div>
    <div class="content-card p-6 border-l-4 border-red-500">
        <p class="text-sm font-medium text-gray-500 uppercase">Total Company Outstanding</p>
        <p class="text-2xl font-bold text-gray-800">Rs. {{ number_format($summary['total_outstanding_amount'], 2) }}</p>
    </div>
</div>

<div class="content-card overflow-hidden">
    <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
        <h2 class="font-bold text-gray-700">Current Outstanding Report ({{ ucfirst($reportType) }})</h2>
        <div class="flex gap-4 items-center">
            <span class="badge badge-info">Filtered Agreements: {{ $reportData->count() }}</span>
            <span class="badge badge-warning">Unique Customers: {{ $reportData->unique('customer_id')->count() }}</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <th>Guarantor Name</th>
                    <th>Guarantor Phone</th>
                    <th class="text-right">Premium (Rs.)</th>
                    <th class="text-right">Loan Amount</th>
                    <th class="text-right">Total Paid</th>
                    <th class="text-right">Outstanding (Rs.)</th>
                    <th class="text-center">Due Qty</th>
                    <th class="text-center">Paid Qty</th>
                    <th>EMI Number</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $totalPremium = 0;
                    $totalLoanAmount = 0;
                    $totalPaidAmount = 0;
                    $totalOutstandingAmount = 0;
                    $totalPaidQty = 0;
                @endphp
                @forelse($reportData as $index => $row)
                    @php
                        $totalPremium += $row['premium'];
                        $totalLoanAmount += $row['loan_amount'];
                        $totalPaidAmount += $row['total_paid'];
                        $totalOutstandingAmount += $row['outstanding_amount'];
                        $totalPaidQty += $row['paid_quantity'];
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="font-medium">{{ $row['customer_name'] }}</td>
                        <td>{{ $row['phone'] }}</td>
                        <td>{{ $row['guarantor_name'] }}</td>
                        <td>{{ $row['guarantor_phone'] }}</td>
                        <td class="text-right">{{ number_format($row['premium'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['loan_amount'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['total_paid'], 2) }}</td>
                        <td class="font-bold text-right">Rs. {{ number_format($row['outstanding_amount'], 2) }}</td>
                        <td class="text-center font-bold text-red-600">{{ $row['due_quantity'] }}</td>
                        <td class="text-center">{{ $row['paid_quantity'] }}</td>
                        <td>{{ $row['emi_number'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center py-8 text-gray-500">
                            No outstanding customers found for this date.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($reportData->count() > 0)
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                       <!-- <td colspan="3" class="text-right">TOTAL (OUTSTANDING ONLY)</td>-->
                      <!--  <td class="text-right">Rs. {{ number_format($totalPremium, 2) }}</td>
                        <td class="text-right">Rs. {{ number_format($totalLoanAmount, 2) }}</td>
                        <td class="text-right">Rs. {{ number_format($totalPaidAmount, 2) }}</td>-->
                        <!--<td class="text-right">Rs. {{ number_format($totalOutstandingAmount, 2) }}</td>
                        <td class="text-center">{{ $reportData->sum('due_quantity') }}</td>
                        <td class="text-center">{{ $totalPaidQty }}</td>
                        <td></td>-->
                    </tr>
                    @if($reportType === 'total')
                    <tr class="bg-blue-50 border-t-2 border-blue-200">
                        <td colspan="6" class="text-right text-blue-800 uppercase">Total Company Collection (Including Paid Off)</td>
                        <td class="text-right text-blue-800">Rs. {{ number_format($summary['total_loan_amount'], 2) }}</td>
                        <td class="text-right text-blue-800">Rs. {{ number_format($summary['total_paid_amount'], 2) }}</td>
                        <td class="text-right text-blue-800">Rs. {{ number_format($summary['total_outstanding_amount'], 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                    @endif
                </tfoot>
            @endif
        </table>
    </div>
</div>

@if($categorySummary && $categorySummary->count() > 0)
<div class="mt-8 mb-8">
    <h3 class="text-xl font-bold mb-4 text-gray-800">Category-wise Outstanding Summary</h3>
    <div class="content-card overflow-hidden">
        <table class="table">
            <thead class="bg-gray-100">
                <tr>
                    <th>Category Name</th>
                    <th class="text-center">Agreement Count</th>
                    <th class="text-right">Loan Amount</th>
                    <th class="text-right">Total Paid</th>
                    <th class="text-right">Outstanding Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categorySummary as $cat)
                <tr>
                    <td class="font-bold">{{ $cat['name'] }}</td>
                    <td class="text-center">{{ $cat['count'] }}</td>
                    <td class="text-right">Rs. {{ number_format($cat['loan_amount'], 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($cat['paid_amount'], 2) }}</td>
                    <td class="text-right font-bold text-red-600">Rs. {{ number_format($cat['outstanding_amount'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 font-bold">
                <tr>
                    <td>TOTAL</td>
                    <td class="text-center">{{ $categorySummary->sum('count') }}</td>
                    <td class="text-right">Rs. {{ number_format($categorySummary->sum('loan_amount'), 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($categorySummary->sum('paid_amount'), 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($categorySummary->sum('outstanding_amount'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

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