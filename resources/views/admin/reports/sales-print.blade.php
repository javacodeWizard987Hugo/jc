<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - {{ now()->format('Y-m-d') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f8f9fa; text-transform: uppercase; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .footer { margin-top: 20px; font-size: 9px; text-align: right; color: #777; }
        .summary-grid { display: grid; grid-template-cols: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; }
        .summary-box { border: 1px solid #ddd; padding: 8px; border-radius: 4px; text-align: center; }
        .summary-title { font-size: 9px; font-weight: bold; margin-bottom: 3px; color: #555; text-transform: uppercase; }
        .summary-value { font-size: 13px; font-weight: bold; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mt-20 { margin-top: 20px; }
        .performance-Best { background-color: #d1fae5; color: #065f46; }
        .performance-Low { background-color: #fee2e2; color: #991b1b; }
        .performance-Average { background-color: #dbeafe; color: #1e40af; }
        .badge { padding: 2px 5px; border-radius: 10px; font-size: 9px; }
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
        <h1>Sales Report</h1>
        <p>Report Period: <strong>{{ $startDate }}</strong> to <strong>{{ $endDate }}</strong></p>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
        @if(isset($reportType))
            <p>Report Type: <strong>{{ ucfirst($reportType) }}</strong></p>
        @endif
        @if(isset($categoryName))
            <p>Category: <strong>{{ $categoryName }}</strong></p>
        @endif
        @if(isset($itemSearch) && $itemSearch)
            <p>Item Keyword: <strong>{{ $itemSearch }}</strong></p>
        @endif
    </div>

    @if(isset($summary))
    <div class="summary-grid">
        <div class="summary-box">
            <div class="summary-title">Total Sales (Net)</div>
            <div class="summary-value">Rs. {{ number_format($summary['total_sales'], 2) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-title">Total MRP Value</div>
            <div class="summary-value">Rs. {{ number_format($summary['total_mrp'], 2) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-title">Loan Outstanding</div>
            <div class="summary-value">Rs. {{ number_format($summary['total_loan_outstanding'], 2) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-title">Gross Profit</div>
            <div class="summary-value">Rs. {{ number_format($summary['total_profit'], 2) }}</div>
        </div>
    </div>
    @endif

    @if($reportType === 'detailed' || $reportType === 'all')
    <h3>Detailed Sales</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Items Sold</th>
                <th class="text-right">Revenue</th>
                <th class="text-right">COGS</th>
                <th class="text-right">Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            @php
                $saleRevenue = 0;
                $saleCogs = 0;
                $filteredItems = $sale->items->filter(function($item) use ($categoryId, $itemSearch) {
                    if ($categoryId && (!$item->item || $item->item->category_id != $categoryId)) return false;
                    if ($itemSearch && $item->item && stripos($item->item->name, $itemSearch) === false) return false;
                    return true;
                });
                
                foreach ($filteredItems as $item) {
                    $saleRevenue += $item->total_price;
                    if ($item->item && $item->item->cost_price !== null) {
                        $saleCogs += $item->quantity * $item->item->cost_price;
                    }
                }
            @endphp
            <tr>
                <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                <td class="font-bold">#{{ $sale->invoice_number }}</td>
                <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                <td>
                    @foreach($filteredItems as $item)
                        {{ $item->item->name ?? 'N/A' }} ({{ (float)$item->quantity }} x {{ number_format($item->unit_price, 2) }})@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td class="text-right font-bold">Rs. {{ number_format($saleRevenue, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($saleCogs, 2) }}</td>
                <td class="text-right font-bold">Rs. {{ number_format($saleRevenue - $saleCogs, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        @if(isset($summary))
        <tfoot>
            <tr class="font-bold">
                <td colspan="4" class="text-right">TOTAL</td>
                <td class="text-right">Rs. {{ number_format($summary['total_sales'], 2) }}</td>
                <td class="text-right">Rs. {{ number_format($summary['total_cogs'], 2) }}</td>
                <td class="text-right">Rs. {{ number_format($summary['total_profit'], 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
    @endif

    @if($reportType === 'item' || $reportType === 'all')
    <div class="mt-20">
        <h3>Item-wise Sales</h3>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Code</th>
                    <th>Perf.</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Times Sold</th>
                <th class="text-right">Revenue</th>
                <th class="text-right">COGS</th>
                <th class="text-right">Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemWiseReport as $item)
                <tr>
                    <td class="font-bold">{{ $item->item->name }}</td>
                    <td>{{ $item->item->item_code ?? 'N/A' }}</td>
                    <td><span class="badge performance-{{ $item->performance_label }}">{{ $item->performance_label }}</span></td>
                    <td class="text-right">{{ number_format($item->total_quantity, 2) }}</td>
                    <td class="text-right">{{ $item->times_sold }}</td>
                    <td class="text-right font-bold">Rs. {{ number_format($item->total_revenue, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($item->total_cogs ?? 0, 2) }}</td>
                <td class="text-right font-bold">Rs. {{ number_format($item->total_revenue - ($item->total_cogs ?? 0), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold">
                    <td colspan="3" class="text-right">TOTAL</td>
                    <td class="text-right">{{ number_format($itemWiseReport->sum('total_quantity'), 2) }}</td>
                    <td class="text-right">{{ $itemWiseReport->sum('times_sold') }}</td>
                    <td class="text-right">Rs. {{ number_format($itemWiseReport->sum('total_revenue'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($itemWiseReport->sum('total_cogs'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($itemWiseReport->sum('total_revenue') - $itemWiseReport->sum('total_cogs'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    @if($reportType === 'category' || $reportType === 'all')
    <div class="mt-20">
        <h3>Category-wise Sales</h3>
        <table>
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th class="text-right">Items Sold</th>
                    <th class="text-right">Total Quantity</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">COGS</th>
                    <th class="text-right">Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categoryWiseReport as $category)
                <tr>
                    <td class="font-bold">{{ $category->category_name }}</td>
                    <td class="text-right">{{ $category->items_count }}</td>
                    <td class="text-right">{{ number_format($category->total_quantity, 2) }}</td>
                    <td class="text-right font-bold">Rs. {{ number_format($category->total_revenue, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($category->total_cogs ?? 0, 2) }}</td>
                    <td class="text-right font-bold">Rs. {{ number_format($category->total_revenue - ($category->total_cogs ?? 0), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold">
                    <td class="text-right">TOTAL</td>
                    <td class="text-right">{{ $categoryWiseReport->sum('items_count') }}</td>
                    <td class="text-right">{{ number_format($categoryWiseReport->sum('total_quantity'), 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($categoryWiseReport->sum('total_revenue'), 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($categoryWiseReport->sum('total_cogs'), 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($categoryWiseReport->sum('total_revenue') - $categoryWiseReport->sum('total_cogs'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>GC SOLUTION POS System - Sales Report</p>
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
