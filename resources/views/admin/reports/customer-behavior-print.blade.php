<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Behavior Report - {{ now()->format('Y-m-d') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; word-wrap: break-word; }
        th { background-color: #f8f9fa; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 10px; text-align: right; color: #777; }
        .behavior-badge { font-weight: bold; }
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
        <h1>Customer Behavior & Credit Risk Report</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Customer Name</th>
                <th style="width: 12%;">Phone</th>
                <th style="width: 10%;">Total Bills</th>
                <th style="width: 13%;">Sales Value</th>
                <th style="width: 10%;">Agreements</th>
                <th style="width: 10%;">payment Ratio</th>
                <th style="width: 15%;">History</th>
                <th style="width: 10%;">Locking</th>
                <th style="width: 10%;">Behavior</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td>
                        <strong>{{ $customer->name }}</strong>
                    </td>
                    <td>{{ $customer->phone ?? 'N/A' }}</td>
                    <td style="text-align: center;">{{ $customer->sales_count }}</td>
                    <td>Rs. {{ number_format($customer->sales_sum_total_amount ?? 0, 2) }}</td>
                    <td style="text-align: center;">{{ $customer->installment_agreements_count }}</td>
                    <td style="text-align: center;">{{ number_format($customer->payment_ratio, 1) }}%</td>
                    <td style="text-align: center;">
                        <span style="color: #059669;">{{ $customer->on_time_premiums }} On-time</span><br>
                        <span style="color: #dc2626;">{{ $customer->delayed_premiums }} Delayed</span>
                    </td>
                    <td style="text-align: center;">
                        @if($customer->total_lock_duration > 0)
                            {{ $customer->total_lock_duration }} days
                            @if($customer->is_locked_now)
                                <br><small style="color: red; font-weight: bold;">(Locked Now)</small>
                            @endif
                        @else
                            <span style="color: #059669;">Never</span>
                        @endif
                    </td>
                    <td>
                        <span class="behavior-badge {{ $customer->behavior_class }}">
                            {{ $customer->behavior }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>GC SOLUTION POS System - Customer Behavior Analysis</p>
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
