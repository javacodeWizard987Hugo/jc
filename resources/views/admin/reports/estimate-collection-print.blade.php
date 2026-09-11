<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estimate Collection Report - Print</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .summary-item {
            text-align: center;
            flex: 1;
        }
        .summary-item span {
            display: block;
            font-weight: bold;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            text-transform: uppercase;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1>Estimate Collection Report</h1>
        <p>
            @if($startDate && $endDate)
                Period: {{ $startDate }} to {{ $endDate }}
            @else
                Month: {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
            @endif
            @if($statusFilter)
                | Status: {{ ucfirst(str_replace('_', ' ', $statusFilter)) }}
            @endif
        </p>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            Total Estimates
            <span>{{ $counts['total'] }}</span>
        </div>
        <div class="summary-item">
            Paid Off
            <span>{{ $counts['paid_off'] }}</span>
        </div>
        <div class="summary-item">
            Partial Paid
            <span>{{ $counts['partial'] }}</span>
        </div>
        <div class="summary-item">
            Not Paid
            <span>{{ $counts['not_paid'] }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Customer Name</th>
                <th>Phone</th>
                <th>EMI Nos.</th>
                <th>QTY</th>
                <th>Estimated (Rs.)</th>
                <th>Received (Rs.)</th>
                <th>Outstanding (Rs.)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="font-bold">{{ $row['customer_name'] }}</td>
                    <td>{{ $row['phone'] }}</td>
                    <td>{{ $row['combined_emi_numbers'] }}</td>
                    <td>{{ $row['premium_quantity'] }}</td>
                    <td class="text-right">{{ number_format($row['estimated_value'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['actual_received'], 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($row['estimated_value'] - $row['actual_received'], 2) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $row['status'])) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">No data found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
        @if($reportData->count() > 0)
            <tfoot>
                <tr class="font-bold">
                    <td colspan="5" class="text-right">TOTALS</td>
                    <td class="text-right">{{ number_format($reportData->sum('estimated_value'), 2) }}</td>
                    <td class="text-right">{{ number_format($reportData->sum('actual_received'), 2) }}</td>
                    <td class="text-right">{{ number_format($reportData->sum('estimated_value') - $reportData->sum('actual_received'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div style="margin-top: 50px; display: flex; justify-content: space-between;">
        <div style="border-top: 1px solid #000; width: 200px; text-align: center;">
            Prepared By
        </div>
        <div style="border-top: 1px solid #000; width: 200px; text-align: center;">
            Authorized By
        </div>
    </div>
</body>
</html>
