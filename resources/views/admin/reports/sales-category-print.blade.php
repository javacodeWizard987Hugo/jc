<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Category Report - {{ $startDate }} to {{ $endDate }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .print-btn { margin-bottom: 20px; }
        .text-right { text-align: right; }
        @media print {
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Category-wise Sales Report</h1>
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
                <th>Category Name</th>
                <th class="text-right">Items Sold</th>
                <th class="text-right">Total Quantity</th>
                <th class="text-right">Total Revenue</th>
                <th class="text-right">Discount</th>
                <th class="text-right">COGS</th>
                <th class="text-right">Net Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoryWiseReport as $index => $category)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $category->category_name }}</td>
                <td class="text-right">{{ $category->items_count }}</td>
                <td class="text-right">{{ number_format($category->total_quantity, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($category->total_revenue, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($category->total_discount ?? 0, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($category->total_cogs ?? 0, 2) }}</td>
                <td class="text-right"><strong>Rs. {{ number_format($category->total_revenue - ($category->total_discount ?? 0) - ($category->total_cogs ?? 0), 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="2" class="text-right">Total</td>
                <td class="text-right">{{ $categoryWiseReport->sum('items_count') }}</td>
                <td class="text-right">{{ number_format($categoryWiseReport->sum('total_quantity'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($categoryWiseReport->sum('total_revenue'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($categoryWiseReport->sum('total_discount'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($categoryWiseReport->sum('total_cogs'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($categoryWiseReport->sum('total_revenue') - $categoryWiseReport->sum('total_discount') - $categoryWiseReport->sum('total_cogs'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px;">
        <p>Report Generated On: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
