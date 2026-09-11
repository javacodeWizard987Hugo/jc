<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Collection Report - {{ now()->format('Y-m-d') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; word-wrap: break-word; }
        th { background-color: #f8f9fa; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 10px; text-align: right; color: #777; }
        .total-box { border: 2px solid #ddd; padding: 15px; border-radius: 4px; display: inline-block; margin-bottom: 20px; background: #fff; }
        .total-label { font-size: 10px; font-weight: bold; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .total-value { font-size: 20px; font-weight: bold; color: #000; }
        @media print {
            .no-print { display: none; }
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
        <h1>Online Collection Report</h1>
        <p>Date Range: {{ $startDate }} to {{ $endDate }}</p>
        <p>Generated on: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <div class="total-box">
        <div class="total-label">Total Online Collections</div>
        <div class="total-value">Rs. {{ number_format($totalAmount, 2) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Date & Time</th>
                <th style="width: 25%;">Customer</th>
                <th style="width: 15%;">Invoice #</th>
                <th style="width: 15%;">Collection Type</th>
                <th style="width: 15%;">Amount</th>
                <th style="width: 15%;">Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('Y-m-d H:i') }}</td>
                    <td>
                        {{ $payment->agreement->customer->name ?? 'N/A' }}<br>
                        <small>{{ $payment->agreement->customer->phone ?? 'N/A' }}</small>
                    </td>
                    <td>#{{ $payment->agreement->sale->invoice_number ?? 'N/A' }}</td>
                    <td>Installment Payment</td>
                    <td>Rs. {{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #f8f9fa;">
                <td colspan="4" style="text-align: right;">TOTAL:</td>
                <td>Rs. {{ number_format($totalAmount, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>GC SOLUTION POS System - Online Collection Detailed Report</p>
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
