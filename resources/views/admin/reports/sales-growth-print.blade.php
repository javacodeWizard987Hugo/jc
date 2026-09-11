<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Growth Report - {{ now()->format('Y-m-d') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; margin: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 10px; text-align: right; color: #777; }
        .section-title { font-size: 16px; font-weight: bold; margin: 20px 0 10px; color: #111; border-left: 4px solid #ef4444; padding-left: 10px; }
        .metric-grid { display: grid; grid-template-cols: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .metric-box { border: 1px solid #ddd; padding: 15px; border-radius: 4px; background: #fff; }
        .metric-label { font-size: 11px; font-weight: bold; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .metric-value { font-size: 18px; font-weight: bold; color: #000; }
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
        <h1>Sales Growth Analysis</h1>
        <p>Comparison and Historical Trends</p>
        <p>Generated on: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <div class="section-title">Sales Comparison</div>
    <div class="metric-grid">
        <div class="metric-box">
            <div class="metric-label">Today's Sales ({{ now()->format('Y-m-d') }})</div>
            <div class="metric-value">Rs. {{ number_format($todaySales, 2) }}</div>
        </div>
        <div class="metric-box">
            <div class="metric-label">Yesterday's Sales ({{ now()->subDay()->format('Y-m-d') }})</div>
            <div class="metric-value">Rs. {{ number_format($yesterdaySales, 2) }}</div>
        </div>
        <div class="metric-box">
            <div class="metric-label">This Month's Sales ({{ now()->format('F Y') }})</div>
            <div class="metric-value">Rs. {{ number_format($thisMonthSales, 2) }}</div>
        </div>
        <div class="metric-box">
            <div class="metric-label">Last Month's Sales ({{ now()->subMonth()->format('F Y') }})</div>
            <div class="metric-value">Rs. {{ number_format($lastMonthSales, 2) }}</div>
        </div>
    </div>

    <div class="section-title">Last 30 Days Daily Sales Performance</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailySales as $day)
                <tr>
                    <td>{{ $day['date'] }}</td>
                    <td>Rs. {{ number_format($day['revenue'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #f8f9fa;">
                <td>TOTAL</td>
                <td>Rs. {{ number_format($dailySales->sum('revenue'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>GC SOLUTION POS System - Sales Growth Report</p>
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
