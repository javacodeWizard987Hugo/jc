<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Tracking Report - {{ now()->format('Y-m-d') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; margin: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 10px; text-align: right; color: #777; }
        .section-title { font-size: 16px; font-weight: bold; margin: 20px 0 10px; color: #111; border-left: 4px solid #ef4444; padding-left: 10px; }
        .comparison-grid { display: grid; grid-template-cols: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px; text-align: center; }
        .comparison-box { border: 1px solid #ddd; padding: 15px; border-radius: 4px; background: #fff; }
        .comparison-label { font-size: 11px; font-weight: bold; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .comparison-value { font-size: 18px; font-weight: bold; color: #000; }
        .difference { font-weight: bold; margin-top: 5px; }
        .positive { color: #10b981; }
        .negative { color: #ef4444; }
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
        <h1>Sales Tracking Report</h1>
        <p>Comparative Tracking Between Two Dates</p>
        <p>Generated on: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <div class="comparison-grid">
        <div class="comparison-box">
            <div class="comparison-label">Date 1: {{ $day1 }}</div>
            <div class="comparison-value">Rs. {{ number_format($day1Data, 2) }}</div>
        </div>
        <div class="comparison-box">
            <div class="comparison-label">Date 2: {{ $day2 }}</div>
            <div class="comparison-value">Rs. {{ number_format($day2Data, 2) }}</div>
        </div>
        <div class="comparison-box">
            <div class="comparison-label">Difference (Day 1 - Day 2)</div>
            <div class="comparison-value">Rs. {{ number_format($day1Data - $day2Data, 2) }}</div>
            <div class="difference {{ $day1Data - $day2Data >= 0 ? 'positive' : 'negative' }}">
                {{ $day2Data > 0 ? number_format((($day1Data - $day2Data) / $day2Data) * 100, 1) : '100' }}% 
                {{ $day1Data - $day2Data >= 0 ? 'Increase' : 'Decrease' }}
            </div>
        </div>
    </div>

    <div class="footer">
        <p>GC SOLUTION POS System - Sales Tracking Report</p>
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
