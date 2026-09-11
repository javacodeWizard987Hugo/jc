<!DOCTYPE html>
<html>
<head>
    <title>Expenses Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .summary { margin-bottom: 20px; }
        .total { font-weight: bold; color: #e74a3b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Expenses Report</h1>
        <p>Period: {{ $startDate }} to {{ $endDate }}</p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <p>Total Expenses: <span class="total">Rs. {{ number_format($summary['total'], 2) }}</span></p>
        <h4>By Category:</h4>
        <ul>
            @foreach($summary['by_category'] as $data)
            <li>{{ $data['category'] }}: Rs. {{ number_format($data['amount'], 2) }}</li>
            @endforeach
        </ul>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                <td>{{ $expense->category->name ?? 'N/A' }}</td>
                <td>{{ $expense->description }}</td>
                <td class="total">Rs. {{ number_format($expense->amount, 2) }}</td>
                <td>{{ ucfirst($expense->payment_method) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
