<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Item Report - {{ $startDate }} to {{ $endDate }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .print-btn { margin-bottom: 20px; }
        .text-right { text-align: right; }
        .performance-Best { color: green; font-weight: bold; }
        .performance-Low { color: red; font-weight: bold; }
        .performance-Average { color: blue; font-weight: bold; }
        @media print {
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Item-wise Sales Report</h1>
        <p>Period: <strong>{{ $startDate }}</strong> to <strong>{{ $endDate }}</strong></p>
        @if($categoryName)
            <p>Category: <strong>{{ $categoryName }}</strong></p>
        @endif
    </div>

    <div class="print-btn">
        <button onclick="window.print()">Print This Report</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Code</th>
                <th>Performance</th>
                <th class="text-right">Qty Sold</th>
                <th class="text-right">Times Sold</th>
                <th class="text-right">Avg Price</th>
                <th class="text-right">Total Revenue</th>
                <th class="text-right">Discount</th>
                <th class="text-right">COGS</th>
                <th class="text-right">Net Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itemWiseReport as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->item->name }}</td>
                <td>{{ $item->item->item_code ?? 'N/A' }}</td>
                <td class="performance-{{ $item->performance_label }}">{{ $item->performance_label }}</td>
                <td class="text-right">{{ number_format($item->total_quantity, 2) }}</td>
                <td class="text-right">{{ $item->times_sold }}</td>
                <td class="text-right">Rs. {{ number_format($item->avg_unit_price ?? 0, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($item->total_revenue, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($item->total_discount ?? 0, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($item->total_cogs ?? 0, 2) }}</td>
                <td class="text-right"><strong>Rs. {{ number_format($item->total_revenue - ($item->total_discount ?? 0) - ($item->total_cogs ?? 0), 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="4" class="text-right">Total</td>
                <td class="text-right">{{ number_format($itemWiseReport->sum('total_quantity'), 2) }}</td>
                <td class="text-right">{{ $itemWiseReport->sum('times_sold') }}</td>
                <td class="text-right">-</td>
                <td class="text-right">Rs. {{ number_format($itemWiseReport->sum('total_revenue'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($itemWiseReport->sum('total_discount'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($itemWiseReport->sum('total_cogs'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($itemWiseReport->sum('total_revenue') - $itemWiseReport->sum('total_discount') - $itemWiseReport->sum('total_cogs'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px;">
        <p>Report Generated On: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
