<!DOCTYPE html>
<html>
<head>
    <title>Supplier Ledger</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .summary { margin-bottom: 20px; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-red { color: #e74a3b; }
        .text-green { color: #1cc88a; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Supplier Ledger</h1>
        <h2>{{ $supplier->name }}</h2>
        @if($startDate || $endDate)
            <p>Period: {{ $startDate ?? 'Beginning' }} to {{ $endDate ?? 'Today' }}</p>
        @endif
    </div>

    <div class="summary">
        <p><strong>Contact Person:</strong> {{ $supplier->contact_person }}</p>
        <p><strong>Phone:</strong> {{ $supplier->phone }}</p>
        <p><strong>Email:</strong> {{ $supplier->email }}</p>
        <p><strong>Outstanding Balance:</strong> <span class="text-red font-bold">Rs. {{ number_format($supplier->outstanding_balance, 2) }}</span></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice Number</th>
                <th class="text-right">Invoice Amount</th>
                <th class="text-right">Paid Amount</th>
                <th class="text-right">Outstanding</th>
                <th>Payment Method</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->payment_date ? $payment->payment_date->format('Y-m-d') : 'N/A' }}</td>
                <td>{{ $payment->invoice_number ?? 'N/A' }}</td>
                <td class="text-right">Rs. {{ number_format($payment->invoice_amount, 2) }}</td>
                <td class="text-right text-green">Rs. {{ number_format($payment->paid_amount, 2) }}</td>
                <td class="text-right text-red">Rs. {{ number_format($payment->outstanding_amount, 2) }}</td>
                <td>{{ ucfirst($payment->payment_method) }}</td>
                <td>{{ $payment->due_date ? $payment->due_date->format('Y-m-d') : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold">
                <td colspan="2" class="text-right">Totals:</td>
                <td class="text-right">Rs. {{ number_format($payments->sum('invoice_amount'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($payments->sum('paid_amount'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($payments->sum('outstanding_amount'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    
   
</body>
</html>
