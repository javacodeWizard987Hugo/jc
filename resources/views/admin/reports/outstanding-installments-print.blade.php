<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outstanding Installments Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 15px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f8f9fa; text-transform: uppercase; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .footer { margin-top: 20px; font-size: 9px; text-align: right; color: #777; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-info { background-color: #d1ecf1; color: #0c5460; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
        @media print {
            .no-print { display: none; }
            @page { size: landscape; margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Print Report</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 10px;">Close</button>
    </div>

    <div class="header">
        <h1>Outstanding Installments Report</h1>
        <p>Report Type: <strong>{{ ucfirst($reportType) }}</strong></p>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
        
        <div style="display: flex; justify-content: center; gap: 20px; margin: 15px 0;">
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                <div style="font-size: 10px; color: #666; text-transform: uppercase;">Total Value Issued</div>
                <div style="font-size: 16px; font-weight: bold;">Rs. {{ number_format($summary['total_loan_amount'], 2) }}</div>
            </div>
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                <div style="font-size: 10px; color: #666; text-transform: uppercase;">Total Paid</div>
                <div style="font-size: 16px; font-weight: bold;">Rs. {{ number_format($summary['total_paid_amount'], 2) }}</div>
            </div>
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                <div style="font-size: 10px; color: #666; text-transform: uppercase;">Total Company Outstanding</div>
                <div style="font-size: 16px; font-weight: bold;">Rs. {{ number_format($summary['total_outstanding_amount'], 2) }}</div>
            </div>
        </div>

        <div style="margin-top: 10px;">
            <span class="badge badge-info">Filtered Agreements: {{ $reportData->count() }}</span>
            <span class="badge badge-warning">Unique Customers: {{ $reportData->unique('customer_id')->count() }}</span>
            @if(isset($search) && $search)
                <span class="badge badge-info">Search: "{{ $search }}"</span>
            @endif
            @if($categoryId)
                <span class="badge badge-info">Category: {{ $categorySummary->where('id', $categoryId)->first()['name'] ?? 'N/A' }}</span>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Customer Name</th>
                <th>Phone Number</th>
                <th>Guarantor Name</th>
                <th>Guarantor Phone</th>
                <th class="text-right">Premium (Rs.)</th>
                <th class="text-right">Loan Amount (Rs.)</th>
                <th class="text-right">Total Paid (Rs.)</th>
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
                    <td class="font-bold">{{ $row['customer_name'] }}</td>
                    <td>{{ $row['phone'] }}</td>
                    <td>{{ $row['guarantor_name'] }}</td>
                    <td>{{ $row['guarantor_phone'] }}</td>
                    <td class="text-right">{{ number_format($row['premium'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['loan_amount'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['total_paid'], 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($row['outstanding_amount'], 2) }}</td>
                    <td class="text-center font-bold">{{ $row['due_quantity'] }}</td>
                    <td class="text-center">{{ $row['paid_quantity'] }}</td>
                    <td>{{ $row['emi_number'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center py-8">
                        No outstanding customers found.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($reportData->count() > 0)
            <tfoot style="background-color: #f9f9f9; font-weight: bold;">
                <tr>
                    <td colspan="5" class="text-right">TOTAL (OUTSTANDING ONLY)</td>
                    <td class="text-right">Rs. {{ number_format($totalPremium, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($totalLoanAmount, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($totalPaidAmount, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($totalOutstandingAmount, 2) }}</td>
                    <td class="text-center">{{ $reportData->sum('due_quantity') }}</td>
                    <td class="text-center">{{ $totalPaidQty }}</td>
                    <td></td>
                </tr>
                @if($reportType === 'total')
                <tr style="background-color: #eff6ff;">
                    <td colspan="6" class="text-right" style="color: #1e40af; text-transform: uppercase;">Total Company Collection (Including Paid Off)</td>
                    <td class="text-right" style="color: #1e40af;">Rs. {{ number_format($summary['total_loan_amount'], 2) }}</td>
                    <td class="text-right" style="color: #1e40af;">Rs. {{ number_format($summary['total_paid_amount'], 2) }}</td>
                    <td class="text-right" style="color: #1e40af;">Rs. {{ number_format($summary['total_outstanding_amount'], 2) }}</td>
                    <td colspan="3"></td>
                </tr>
                @endif
            </tfoot>
        @endif
    </table>

    @if($categorySummary && $categorySummary->count() > 0)
    <div style="margin-top: 30px;">
        <h3>Category-wise Outstanding Summary</h3>
        <table>
            <thead>
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
                    <td class="text-right font-bold">Rs. {{ number_format($cat['outstanding_amount'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot style="background-color: #f9f9f9; font-weight: bold;">
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
    @endif

    <div class="footer">
        <p>GC SOLUTION POS System - Outstanding Installments Report</p>
    </div>

    <script>
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('print')) {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        };
    </script>
</body>
</html>
