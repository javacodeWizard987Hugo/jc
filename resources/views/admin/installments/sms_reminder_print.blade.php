
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installment Reminders - {{ $date }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .print-btn { margin-bottom: 20px; }
        @media print {
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Installment Reminders Report</h1>
        <p>Installments for Due Day: <strong>{{ \Carbon\Carbon::parse($date)->day }}</strong> (Selected Date: {{ $date }})</p>
    </div>

    <div class="print-btn">
        <button onclick="window.print()">Print This Report</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Customer Name</th>
                <th>Phone</th>
                  <th>Guarantor Name</th>
                <th>Guarantor Phone</th>
                <th>Category</th>
                <th>Invoice #</th>
                <th>Next Due Date</th>
                <th>Monthly Premium</th>
                <th>Remaining Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPremium = 0; @endphp
            @forelse ($agreements as $index => $agreement)
                 @php
                    $totalPremium += $agreement->monthly_installment_amount; 
                    $categories = $agreement->sale && $agreement->sale->items 
                        ? $agreement->sale->items->map(fn($item) => $item->item->category->name ?? 'N/A')->unique()->implode(', ')
                        : 'N/A';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $agreement->customer->name }}</td>
                    <td>{{ $agreement->customer->phone }}</td>
                         <td>{{ $agreement->guarantor_name ?? 'N/A' }}</td>
                    <td>{{ $agreement->guarantor_mobile_number ?? 'N/A' }}</td>
                    <td>{{ $categories }}</td>
                    <td>{{ $agreement->sale->invoice_number ?? 'N/A' }}</td>
                    <td>{{ $agreement->next_due_date instanceof \Carbon\Carbon ? $agreement->next_due_date->format('Y-m-d') : $agreement->next_due_date }}</td>
                    <td>{{ number_format($agreement->monthly_installment_amount, 2) }}</td>
                    <td>{{ number_format($agreement->balance_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">No installments due on or before this date.</td>
                </tr>
            @endforelse
        </tbody>
        @if($agreements->count() > 0)
        <tfoot>
            <tr>
              <th colspan="8" style="text-align: right;">Total Expected Collection:</th>
                <th>{{ number_format($totalPremium, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
        @endif
    </table>

    <div style="margin-top: 30px;">
        <p>Report Generated On: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
