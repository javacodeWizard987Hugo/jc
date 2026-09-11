@extends('layouts.app')

@section('title', 'Paid Off Agreements Report')

@section('content')
<div class="page-header">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Paid Off Agreements Report</h1>
            <p class="text-gray-600">All agreements where the outstanding balance is 0.</p>
        </div>
    </div>
</div>

<div class="content-card p-6 mb-6 print:hidden">
    <form action="{{ route('admin.reports.paid-off-agreements') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
            <label for="search" class="form-label">Search Customer</label>
            <input type="text" name="search" id="search" value="{{ $search }}" class="form-input" placeholder="Name, NIC, Phone or EMI">
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
            <button onclick="window.print()" class="btn btn-secondary">
                🖨️ Print
            </button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="content-card p-6 border-l-4 border-green-500">
        <p class="text-sm font-medium text-gray-500 uppercase">Total Paid Off Agreements</p>
        <p class="text-2xl font-bold text-gray-800">{{ $agreements->count() }}</p>
    </div>
    <div class="content-card p-6 border-l-4 border-blue-500">
        <p class="text-sm font-medium text-gray-500 uppercase">Total Collection from Paid Off</p>
        <p class="text-2xl font-bold text-gray-800">Rs. {{ number_format($agreements->sum('total_collection'), 2) }}</p>
    </div>
</div>

<div class="content-card overflow-hidden">
    <div class="p-4 bg-gray-50 border-b">
        <h2 class="font-bold text-gray-700">Paid Off Agreements List</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Phone / NIC</th>
                    <th>EMI Number</th>
                    <th class="text-right">Invoice Value</th>
                    <th class="text-right">Interest Collected</th>
                    <th class="text-right">Fine Paid</th>
                    <th class="text-right">Total Collection</th>
                    <th class="text-center">Completion Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agreements as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="font-medium">{{ $row['customer_name'] }}</td>
                        <td>
                            <div class="text-sm">{{ $row['phone'] }}</div>
                            <div class="text-xs text-gray-500">{{ $row['nic'] }}</div>
                        </td>
                        <td>{{ $row['emi_number'] }}</td>
                        <td class="text-right">{{ number_format($row['total_invoice_value'], 2) }}</td>
                        <td class="text-right text-blue-600 font-semibold">{{ number_format($row['interest_amount'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['fine_paid'], 2) }}</td>
                        <td class="text-right font-bold text-green-700">Rs. {{ number_format($row['total_collection'], 2) }}</td>
                        <td class="text-center">{{ $row['paid_off_date'] ? \Carbon\Carbon::parse($row['paid_off_date'])->format('Y-m-d') : 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-8 text-gray-500">
                            No paid off agreements found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($agreements->count() > 0)
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="4" class="text-right uppercase">Total Summary</td>
                        <td class="text-right">Rs. {{ number_format($agreements->sum('total_invoice_value'), 2) }}</td>
                        <td class="text-right">Rs. {{ number_format($agreements->sum('interest_amount'), 2) }}</td>
                        <td class="text-right">Rs. {{ number_format($agreements->sum('fine_paid'), 2) }}</td>
                        <td class="text-right">Rs. {{ number_format($agreements->sum('total_collection'), 2) }}</td>
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
            padding: 0 !important;
        }
        .content-card {
            border: none !important;
            box-shadow: none !important;
        }
        .table {
            border: 1px solid #e5e7eb;
            width: 100%;
        }
        .page-header {
            margin-bottom: 10px !important;
            padding: 10px !important;
        }
    }
</style>
@endsection
