<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disconnect Report - {{ now()->format('Y-m-d') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; margin: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 10px; text-align: right; color: #777; }
        .summary { margin-top: 20px; display: grid; grid-template-cols: 1fr 1fr; gap: 20px; }
        .summary-box { border: 1px solid #ddd; padding: 10px; border-radius: 4px; }
        .summary-title { font-weight: bold; margin-bottom: 5px; color: #555; }
        .summary-value { font-size: 16px; font-weight: bold; }
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
        <h1>Disconnect Report ({{ $reportType === 'all_time' ? 'All Time' : 'Filtered' }})</h1>
        @if($reportType === 'filtered')
            <p>Period: <strong>{{ $startDate }}</strong> to <strong>{{ $endDate }}</strong></p>
        @else
            <p>Historical Report (All Disconnected/Unlocked Devices)</p>
        @endif
        <p>Generated on: {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="summary-title">Total Disconnected Devices</div>
            <div class="summary-value">{{ $withinMonthCount }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-title">Total Due of Lock</div>
            <div class="summary-value">Rs. {{ number_format($lockBalance, 2) }}</div>
        </div>
        <div class="summary-box">
           <!-- <div class="summary-title">Total Due of Unlock</div>
            <div class="summary-value">Rs. {{ number_format($unlockBalance, 2) }}</div>-->
        </div>
    </div>

    <h3>Overdue & Disconnected Devices</h3>
    <table>
    <thead>
        <tr>
            <th>EMI Number</th>
            <th>Customer</th>
            <th>Disconnected Days</th>
 <th>Guarantor</th>
            <!--
            <th>Delay Days</th>
            <th>Delay Charge</th>
            <th>Remaining Charge</th>
            -->

            <th>Due Premiums</th>
            <th>Status</th>
            <th>Balance</th>
        </tr>
    </thead>
    <tbody>
        @foreach($disconnected as $agreement)
            <tr>
                <td>{{ $agreement->combined_emi_numbers ?? 'N/A' }}</td>
                <td>{{ $agreement->customer->name ?? 'N/A' }}
                <br>
                        <small>{{ $agreement->customer->phone }}</small>
                </td>
                <td style="text-align: center;">{{ (int) $agreement->disconnected_at->startOfDay()->diffInDays(now()->startOfDay()) }} Days</td>
                <td>
 {{ $agreement->guarantor_name ?? 'N/A' }}<br>
                        <small>{{ $agreement->guarantor_mobile_number }}</small>
                    </td>
                <!--
                <td style="text-align: center;">{{ $agreement->delay_days }} Days</td>
                <td>Rs. {{ number_format($agreement->calculated_fine, 2) }}</td>
                <td>Rs. {{ number_format($agreement->remaining_fine, 2) }}</td>
                -->

                <td>Rs. {{ number_format($agreement->overdue_installment_amount, 2) }}</td>
                <td>DISCONNECTED</td>
                <td style="text-align: right;">Rs. {{ number_format($agreement->balance_amount, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

    <h3 style="margin-top: 40px;">Unlocked Devices</h3>
    <table>
        <thead>
            <tr>
                <th>EMI Number</th>
                <th>Customer</th>
                <th>Guarantor</th>
                <th>Unlocked At</th>
                <th>Duration</th>
                <th>Due Premiums</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($unlocked as $agreement)
                <tr>
                    <td>{{ $agreement->combined_emi_numbers ?? 'N/A' }}</td>
                    <td>{{ $agreement->customer->name ?? 'N/A'}}</td>
                    <td>
                        {{ $agreement->guarantor_name ?? 'N/A' }}<br>
                        <small>{{ $agreement->guarantor_mobile_number }}</small>
                    </td>
                    <td>{{ $agreement->unlocked_at ? $agreement->unlocked_at->format('Y-m-d H:i') : 'N/A' }}</td>
                    <td>
                        @if($agreement->disconnected_at && $agreement->unlocked_at)
                          {{ (int) $agreement->disconnected_at->diffInDays($agreement->unlocked_at) }} Days
                        @else
                          -
                        @endif
                    </td>
                    <td>Rs. {{ number_format($agreement->overdue_installment_amount, 2) }}</td>
                    <td style="text-align: right;">Rs. {{ number_format($agreement->balance_amount, 2) }}</td>
                    <td>UNLOCKED</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>GC SOLUTION POS System - Disconnect Report</p>
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
