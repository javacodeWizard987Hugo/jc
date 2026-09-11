<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Installment Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { bg-color: #f2f2f2; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; text-align: right; font-style: italic; }
        .summary-table { width: 40%; margin-left: auto; }
        .bg-blue { background-color: #eef2ff; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Daily Installment Report</h2>
        <p>Period: {{ $startDate }} to {{ $endDate }}</p>
        <p>Report Type: {{ ucfirst(str_replace('_', ' ', $reportType)) }}</p>
        @if($customer)
            <p>Customer: {{ $customer->name }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Customer</th>
                <th>Invoice</th>
                <th>Due Date</th>
                <th>Method</th>
                <th class="text-right">Interest</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $row)
            <tr class="{{ $row['type'] == 'Down Payment' ? 'bg-blue' : '' }}">
                <td>{{ $row['date'] }}</td>
                <td class="{{ $row['type'] == 'Down Payment' ? 'font-bold' : '' }}">{{ $row['type'] }}</td>
                <td>{{ $row['customer'] }}</td>
                <td>#{{ $row['invoice'] }}</td>
                <td>{{ $row['due_date'] }}</td>
                <td>{{ $row['method'] }}</td>
                <td class="text-right">Rs. {{ number_format($row['interest'], 2) }}</td>
                <td class="text-right font-bold">Rs. {{ number_format($row['amount'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold">
                <td colspan="7" class="text-right">Total Collection</td>
                <td class="text-right">Rs. {{ number_format($reportData->sum('amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
