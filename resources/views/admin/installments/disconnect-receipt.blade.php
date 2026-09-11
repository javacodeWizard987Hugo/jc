<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disconnect Payment Receipt - {{ $payment->agreement->sale->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            background: #f5f5f5;
        }

        /* ===== SCREEN VIEW ===== */
        .receipt-container {
            width: 72mm; /* POS width */
            min-height: 100mm;
            margin: 20px auto;
            background: white;
            padding: 5mm;
            border: 1px solid #ddd;
        }

        /* ===== HEADER IMAGE ===== */
        .header img {
            width: 100%;
            height: auto;
            margin-bottom: 10px;
        }

        .receipt-info {
            margin-bottom: 15px;
        }

        .receipt-info p {
            font-size: 11px;
            margin-bottom: 5px;
            line-height: 1.4;
            display: flex;
            justify-content: space-between;
        }

       .footer {
    text-align: center;
    margin-top: 15px;
    padding-top: 10px;
    border-top: 1px dashed #ccc;
}

/* Thank you section (bigger & attractive) */
.thank-you p {
    font-size: 14px;
    font-weight: bold;
    letter-spacing: 1px;
}

/* Divider line */
.divider {
    border-top: 1px dashed #000;
    margin: 8px 0;
}

/* Developer text (smaller & subtle) */
.developer {
    font-size: 9px;
    color: #666;
    font-style: italic;
}

        /* ===== PRINT SETTINGS ===== */
        @page {
            size: 72mm auto;
            margin: 0;
        }

        @media print {
            body {
                background: white;
            }

            .receipt-container {
                margin: 0;
                border: none;
                width: 72mm;
                min-height: auto;
                padding: 5mm;
            }

            .no-print {
                display: none !important;
            }
        }

        .print-btn {
            text-align: center;
            margin: 15px 0;
        }

        .print-btn button {
            background: #008f1f;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="print-btn no-print">
        <button onclick="window.print()">🖨️ Print Receipt</button>
        <button onclick="window.history.back()"
            style="background: #6c757d; margin-left: 10px; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: bold;">
            ← Back
        </button>
    </div>

    <div class="receipt-container">
        <div class="header">
            <img
                src="{{ asset('images/letter_head.png') }}"
                alt="Letter Head"
            >
        </div>

        <h3 style="text-align: center; margin-bottom: 15px; text-decoration: underline; font-size: 13px;">DISCONNECT PAYMENT</h3>

        <div class="receipt-info">
            <p><span>Date:</span> <span>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</span></p>
            <p><span>Customer Name:</span> <span>{{ $payment->agreement->customer->name }}</span></p>
            <p><span>Invoice Number:</span> <span>{{ $payment->agreement->sale->invoice_number }}</span></p>
            <p><span>EMI Number:</span> <span>{{ $payment->agreement->combined_emi_numbers }}</span></p>
            <div class="divider"></div>
            <p><span>Installment Amount:</span> <span>Rs. {{ number_format($payment->amount, 2) }}</span></p>
            <p><span>Delay Charge:</span> <span>Rs. {{ number_format($payment->fine_amount, 2) }}</span></p>
            <p style="font-weight: bold; font-size: 12px; margin-top: 5px;"><span>Total Received:</span> <span>Rs. {{ number_format($payment->amount + $payment->fine_amount, 2) }}</span></p>
            <div class="divider"></div>
            <p><span>Balance:</span> <span>Rs. {{ number_format($payment->agreement->balance_amount, 2) }}</span></p>
            <p><span>Next Due date:</span> 
                <span>
                @php
                    $nextDueDate = $payment->agreement->getNextDueDateAsOfPayment($payment);
                @endphp
                @if($nextDueDate instanceof \Carbon\Carbon)
                    {{ $nextDueDate->format('Y-m-d') }}
                @else
                    {{ $nextDueDate }}
                @endif
                </span>
            </p>
            <p><span>Status:</span> <span>{{ $payment->agreement->unlocked_at ? 'Unlocked' : 'Locked' }}</span></p>
        </div>

       <div class="footer">
    <div class="thank-you">
        <br><br>
        <p>Thank You!</p>
        <p>Come Again</p>
    </div>

    <div class="divider"></div>

    <p class="developer">Powered by Aryalabs</p>
</div>

    </div>

    <script>
        // Auto print if requested
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('print')) {
            window.onload = () => window.print();
        }
    </script>
</body>
</html>
