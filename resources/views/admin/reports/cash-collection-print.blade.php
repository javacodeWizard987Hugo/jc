<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Collection Forecast - {{ now()->format('Y-m-d') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; word-wrap: break-word; }
        th { background-color: #f8f9fa; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 10px; text-align: right; color: #777; }
        .month-header { background: #f1f5f9; font-weight: bold; font-size: 13px; color: #1e293b; }
        .month-total { font-weight: bold; background: #fffbeb; }
        .summary-grid { display: grid; grid-template-cols: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; text-align: center; }
        .summary-box { border: 1px solid #ddd; padding: 10px; border-radius: 4px; background: #fff; }
        .summary-label { font-size: 9px; font-weight: bold; color: #666; text-transform: uppercase; margin-bottom: 3px; }
        .summary-value { font-size: 14px; font-weight: bold; color: #000; }
        @media print {
            .no-print { display: none; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    @if(!isset($isPdf) || !$isPdf)
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Print Report</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 10px;">Close</button>
    </div>
    @endif

    <div class="header">
        <h1>Cash Collection Forecast Report</h1>
        <p>Forecast for next {{ $selectedMonthCount }} Months - As of {{ $asOfDate }}</p>
        <p>Generated on: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <div class="summary-grid">
        @foreach($upcomingCollections as $monthKey => $monthData)
            <div class="summary-box">
                <div class="summary-label">{{ $monthData['month'] }}</div>
                <div class="summary-value">Rs. {{ number_format($monthData['expected'], 2) }}</div>
            </div>
        @endforeach
    </div>

    @foreach($upcomingCollections as $monthKey => $monthData)
        <h3 class="month-header" style="padding: 8px; margin-bottom: 0;">{{ $monthData['month'] }} Forecast</h3>
        <table style="margin-top: 0; margin-bottom: 30px;">
            <thead>
                <tr>
                    <th style="width: 40%;">Customer</th>
                    <th style="width: 20%;">Phone</th>
                    <th style="width: 20%;">Due Date</th>
                    <th style="width: 20%;">Expected Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthData['customers'] as $customer)
                    <tr>
                        <td>{{ $customer['customer'] }}</td>
                        <td>{{ $customer['phone'] }}</td>
                        <td>{{ $customer['due_date'] }}</td>
                        <td>Rs. {{ number_format($customer['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="month-total">
                    <td colspan="3" style="text-align: right;">Total Expected for {{ $monthData['month'] }}:</td>
                    <td>Rs. {{ number_format($monthData['expected'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endforeach

    <div class="footer">
        <p>GC SOLUTION POS System - Collection Forecast</p>
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
