<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $sale->invoice_number }}</title>
    @php
        $routePrefix = auth()->user()->isAdmin() ? 'admin' : 'cashier';
    @endphp
    
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
    .invoice-container {
        width: 148mm; /* A5 width */
        min-height: 210mm;
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

    .invoice-info p {
        font-size: 11px;
        margin-bottom: 3px;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .items-table th {
        background: #f2f2f2;
        font-size: 11px;
        padding: 6px;
        border-bottom: 1px solid #ccc;
        text-align: left;
    }

    .items-table td {
        font-size: 11px;
        padding: 6px;
        border-bottom: 1px solid #eee;
    }

    .totals {
        margin-top: 12px;
        border-top: 1px solid #ccc;
        padding-top: 8px;
    }

    .totals-row {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        margin: 4px 0;
    }

    .totals-row.total {
        font-size: 14px;
        font-weight: bold;
        color: #0a3d00;
        border-top: 1px solid #ccc;
        padding-top: 6px;
        margin-top: 6px;
    }

    .footer {
        text-align: center;
        font-size: 10px;
        margin-top: 15px;
        border-top: 1px dashed #ccc;
        padding-top: 6px;
        color: #555;
    }

    /* ===== PRINT SETTINGS ===== */
    @page {
        size: A5;
        margin: 10mm;
    }

    @media print {
        body {
            background: white;
        }

        .invoice-container {
            margin: 0;
            border: none;
            width: 100%;
            min-height: auto;
            padding: 0;
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
    }
</style>

</head>
<body>
    <div class="print-btn no-print">
        <button onclick="window.print()">🖨️ Print Invoice</button>
        <button onclick="window.location.href='{{ route($routePrefix . '.pos') }}'"
            style="background: #6c757d; margin-left: 10px;">
            ← Back to POS
        </button>
    </div>

    <div class="invoice-container">
        <div class="header">
            <img
                src="{{ asset('images/letter_head.png') }}"
                alt="Letter Head"
                style="width:100%; height:auto; display:block;"
            >
        </div>

        <div class="invoice-info">
            <p><strong>Invoice:</strong> {{ $sale->invoice_number }}</p>
            <p><strong>Date:</strong> {{ $sale->created_at->format('Y-m-d H:i:s') }}</p>
            <p><strong>Cashier:</strong> {{ $sale->cashier->name }}</p>
            <p><strong>Payment:</strong> {{ ucfirst($sale->payment_method) }}</p>
        </div>

        @php
            $calculatedSubtotal = 0;
        @endphp

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>PCS</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    @php
                        $unitPrice = $item->item->cost_price; // or selling_price
                        $rowTotal  = $item->quantity * $unitPrice;
                        $calculatedSubtotal += $rowTotal;
                    @endphp
                    <tr>
                        <td>{{ $item->item->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rs. {{ number_format($unitPrice, 2) }}</td>
                        <td>Rs. {{ number_format($rowTotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($sale->payment_method == 'installment' && $sale->installmentAgreement)
        <div class="totals">
            <div class="totals-row">
                <span>Down Payment:</span>
                <span>Rs. {{ number_format($sale->installmentAgreement->down_payment_amount, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Installments:</span>
                <span>{{ $sale->installmentAgreement->number_of_installments }}</span>
            </div>
            <div class="totals-row">
                <span>Monthly Amount:</span>
                <span>Rs. {{ number_format($sale->installmentAgreement->monthly_installment_amount, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Due Date:</span>
                <span>{{ $sale->installmentAgreement->due_day_of_month }} of each month</span>
            </div>
        </div>
        @endif

        @php
            $finalTotal = $calculatedSubtotal
                        - $sale->discount_amount
                        + $sale->tax_amount;
        @endphp

        <div class="totals">
            <div class="totals-row">
                <span>Subtotal:</span>
                <span>Rs. {{ number_format($calculatedSubtotal, 2) }}</span>
            </div>

            @if($sale->discount_amount > 0)
            <div class="totals-row">
                <span>Discount:</span>
                <span>- Rs. {{ number_format($sale->discount_amount, 2) }}</span>
            </div>
            @endif

            @if($sale->tax_amount > 0)
            <div class="totals-row">
                <span>Tax:</span>
                <span>Rs. {{ number_format($sale->tax_amount, 2) }}</span>
            </div>
            @endif

            <div class="totals-row total">
                <span>Total:</span>
                <span>Rs. {{ number_format($finalTotal, 2) }}</span>
            </div>
        </div>

        <div class="footer">
            <br><br><br><br><br>
            <br><br><br><br><br>
            <br><br><br><br><br>
            <p>Thank you! Visit us Again!</p>
            <p>{{ now()->format('Y-m-d H:i:s') }}</p>
            <p style="margin-top: 6px; font-weight: bold; font-size: 11px;">
                AryaLabs POS
            </p>
        </div>
    </div>

    <script>
        window.addEventListener('beforeprint', function () {
            document.body.style.margin = '0';
            document.body.style.padding = '0';
        });

        if (window.matchMedia) {
            const mediaQueryList = window.matchMedia('print');
            mediaQueryList.addListener(function (mql) {
                if (mql.matches) {
                    document.body.style.margin = '0';
                    document.body.style.padding = '0';
                }
            });
        }
    </script>
</body>

</html>

