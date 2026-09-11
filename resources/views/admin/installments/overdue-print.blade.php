<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Installment Report - {{ now()->format('Y-m-d') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { margin-top: 20px; font-size: 10px; text-align: right; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    @if(!isset($isPdf) || !$isPdf)
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Report</button>
    </div>
    @endif
    <div class="header">
        <h2>Overdue Installment Report</h2>
        <p>Date Range: {{ $startDate ?? 'All' }} to {{ $endDate ?? now()->toDateString() }}</p>
        @if($search)
            <p>Search Criteria: "{{ $search }}"</p>
        @endif
        <p>Generated on: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Phone</th>
                <th>Guarantor Name</th>
                <th>Guarantor Phone</th>
                <th>Invoice</th>
                <th>EMI Info</th>
                <th>Premium</th>
                <th>Qty</th>
                <th>Due Date</th>
                <th>Late Days</th>
                <th>Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @foreach($overdueAgreements as $agreement)
                @php
                    $nextDueDate = $agreement->next_due_date;
                    $daysOverdue = 0;
                    if ($nextDueDate instanceof \Carbon\Carbon) {
                        $daysOverdue = now()->startOfDay()->diffInDays($nextDueDate->startOfDay(), false);
                        $daysOverdue = abs($daysOverdue);
                    }
                    $totalPaid = $agreement->payments->sum('amount');
                    $emiNumber = floor($totalPaid / $agreement->monthly_installment_amount) + 1;
                    $totalQty = $agreement->sale ? $agreement->sale->items->sum('quantity') : 0;
                @endphp
                <tr>
                    <td>{{ $agreement->customer->name }}</td>
                    <td>{{ $agreement->customer->phone ?? 'N/A' }}</td>
                    <td>{{ $agreement->guarantor_name ?? 'N/A' }}</td>
                    <td>{{ $agreement->guarantor_mobile_number ?? 'N/A' }}</td>
                    <td>{{ $agreement->sale->invoice_number }}</td>
                    <td>
                        <!--EMI #{{ (int)$emiNumber }}-->
                       <medium>Lock: {{ $agreement->emi_lock_mode ?? 'None' }} <br>Emi number: {{ $agreement->combined_emi_numbers }}</small>
                    </td>
                    <td>Rs. {{ number_format($agreement->monthly_installment_amount, 2) }}</td>
                    <td>{{ $totalQty }}</td>
                    <td>{{ $nextDueDate->format('Y-m-d') }}</td>
                    <td>{{ $daysOverdue }}</td>
                    <td>Rs. {{ number_format($agreement->balance_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="10" style="text-align: right;">Total Outstanding:</th>
                <th style="text-align: right;">Rs. {{ number_format($overdueAgreements->sum('balance_amount'), 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Total Customers: {{ $overdueAgreements->count() }}</p>
    </div>
