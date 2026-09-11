<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estimate Report - {{ $selectedMonth }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f8f9fa; text-transform: uppercase; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .footer { margin-top: 20px; font-size: 9px; text-align: right; color: #777; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Print Report</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 10px;">Close</button>
    </div>

    <div class="header">
        <h1>Monthly Collection Estimate</h1>
        <p>
            @if($startDate && $endDate)
                Estimate for: <strong>{{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($endDate)->format('Y-m-d') }}</strong>
            @else
                Estimate for: <strong>{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</strong>
            @endif
        </p>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Customer Details</th>
                <th>Guarantor Details</th>
                <th>EMI Numbers</th>
                <th>QTY</th>
                <th>Due Date</th>
                
                <th class="text-right">TOTAL (RS.)</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <strong>{{ $row['customer_name'] }}</strong><br>
                    {{ $row['phone'] }}
                </td>
                <td>
                    {{ $row['guarantor_name'] }}<br>
                    {{ $row['guarantor_phone'] }}
                </td>
               <td>{{ $row['combined_emi_numbers'] }}</td>
                <td>{{ $row['premium_quantity'] }}</td>
                <td>{{ $row['due_date'] }}</td>
              
                <td class="text-right font-bold">Rs. {{ number_format($row['premium'], 2) }}</td>
                <td class="text-right">Rs. {{ number_format($row['balance'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
              <tr class="font-bold" style="background-color: #f9f9f9;">
                <td colspan="4" class="text-right">TOTAL PREMIUM QUANTITY</td>
                <td>{{ $reportData->sum('premium_quantity') }}</td>
                <td colspan="3"></td>
            </tr>
            <tr class="font-bold" style="background-color: #f9f9f9;">
                <td colspan="6" class="text-right">TOTAL VALUE OF PREMIUM QUANTITY</td>
                <td class="text-right">Rs. {{ number_format($reportData->sum('premium'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>GC SOLUTION POS System - Monthly Estimate Report</p>
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
