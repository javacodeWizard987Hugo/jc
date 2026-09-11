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
            padding: 20px;
            background: white;
        }
        .invoice-container {
            width: 72mm;
            max-width: 72mm;
            margin: 0 auto;
            background: white;
            padding: 7px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 7px;
            margin-bottom: 10px;
        }
        .header h1 {
            color: #dc3545;
            font-size: 20px;
            margin-bottom: 3px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .invoice-info {
            margin-bottom: 10px;
        }
        .invoice-info p {
            margin: 2px 0;
            font-size: 11px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .items-table th {
            background: #f5f5f5;
            padding: 4px;
            text-align: left;
            font-size: 10px;
            border-bottom: 1px solid #ddd;
        }
        .items-table td {
            padding: 4px;
            font-size: 10px;
            border-bottom: 1px solid #eee;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .totals {
            border-top: 2px solid #ddd;
            padding-top: 7px;
            margin-top: 7px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }
        .totals-row span:last-child {
            margin-right: 20px;
        }
        .totals-row.total {
            font-weight: bold;
            font-size: 14px;
            color: #dc3545;
            border-top: 1px solid #ddd;
            padding-top: 4px;
            margin-top: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 12px;
            padding-top: 7px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        /* Set page size for 72mm POS receipt */
        @page {
            size: 72mm auto;
            margin: 0;
        }
        
        @media print {
            @page {
                size: 72mm auto;
                margin: 0;
            }
            body {
                padding: 0;
                margin: 0;
                width: 72mm;
            }
            .invoice-container {
                border: none;
                width: 72mm;
                max-width: 72mm;
                margin: 0;
                padding: 7px;
            }
            .no-print {
                display: none;
            }
            /* Remove any headers/footers added by browser */
            html, body {
                height: auto;
            }
        }
        .print-btn {
            text-align: center;
            margin: 20px 0;
        }
        .print-btn button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
        }
        .print-btn button:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="print-btn no-print">
        <button onclick="window.print()">🖨️ Print Invoice</button>
        <button onclick="window.location.href='{{ route($routePrefix . '.pos') }}'" style="background: #6c757d; margin-left: 10px;">← Back to POS</button>
    </div>

    <div class="invoice-container">
        <div class="header">
            @php
                $logoPath = null;
                $logoExtensions = ['png', 'jpg', 'jpeg', 'svg', 'gif', 'webp'];
                foreach ($logoExtensions as $ext) {
                    if (file_exists(public_path('images/logo.' . $ext))) {
                        $logoPath = asset('images/logo.' . $ext);
                        break;
                    }
                }
                $storeAddress = \App\Models\SystemSetting::getValue('store_address', 'Kegalu Stores');
                $storeContact = \App\Models\SystemSetting::getValue('store_contact', '071 405 6490');
            @endphp
            @if($logoPath)
                <img src="{{ $logoPath }}" 
                     alt="RED CHICKEN Logo" 
                     style="height: 45px; width: auto; max-width: 135px; margin: 0 auto 6px; display: block;">
            @else

                <h1>GC SOLUTION
                </h1>
            @endif
            <p>Point of Sale System</p>
            <p style="font-size: 9px; margin-top: 3px; color: #333;">{{ $storeAddress }}</p>
            <p style="font-size: 9px; color: #333;">{{ $storeContact }}</p>
        </div>

        <div class="invoice-info">
            <p><strong>Invoice:</strong> {{ $sale->invoice_number }}</p>
            <p><strong>Date:</strong> {{ $sale->created_at->format('Y-m-d H:i:s') }}</p>
            <p><strong>Cashier:</strong> {{ $sale->cashier->name }}</p>
            <p><strong>Payment:</strong> {{ ucfirst($sale->payment_method) }}</p>
            @if($sale->customer)
                <p><strong>Customer:</strong> {{ $sale->customer->name }}</p>
                <p><strong>EMI Lock Mode:</strong> {{ $sale->customer->emi_lock_mode }}</p>
                <p><strong>EMI Number:</strong> {{ $sale->customer->emi_number }}</p>
            @endif
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Weight</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                @php
                    $weightDisplay = '-';
                    if ($item->weight) {
                        // Weight is stored in kg (quantity is in kg)
                        $weightInKg = $item->weight;
                        
                        // Extract whole kg and grams
                        $wholeKg = floor($weightInKg);
                        $remainingGrams = round(($weightInKg - $wholeKg) * 1000);
                        
                        // Format as "Xkg Yg" with padded grams (000, 500, etc.)
                        $gFormatted = str_pad($remainingGrams, 3, '0', STR_PAD_LEFT);
                        
                        if ($wholeKg > 0 && $remainingGrams > 0) {
                            $weightDisplay = $wholeKg . 'kg ' . $gFormatted . 'g';
                        } else if ($wholeKg > 0) {
                            $weightDisplay = $wholeKg . 'kg 000g';
                        } else if ($remainingGrams > 0) {
                            $weightDisplay = '0kg ' . $gFormatted . 'g';
                        } else {
                            $weightDisplay = '0kg 000g';
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $item->item->name }}</td>
                    <td>{{ $weightDisplay }}</td>
                    <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
                    <td>Rs. {{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>Subtotal:</span>
                <span>Rs. {{ number_format($sale->subtotal, 2) }}</span>
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
                <span>Rs. {{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        @if($sale->installmentAgreement)
        <div class="totals">
            <div class="totals-row">
                <span>Down Payment:</span>
                <span>Rs. {{ number_format($sale->installmentAgreement->down_payment_amount, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Remaining Balance:</span>
                <span>Rs. {{ number_format($sale->installmentAgreement->balance_amount, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Installments:</span>
                <span>{{ $sale->installmentAgreement->number_of_installments }} x Rs. {{ number_format($sale->installmentAgreement->monthly_installment_amount, 2) }}</span>
            </div>
        </div>
        @endif

        <div class="footer">
            <p>Thank you! Visit us Again!</p>
            <p>{{ now()->format('Y-m-d H:i:s') }}</p>
            <p style="margin-top: 6px; font-weight: bold; font-size: 11px;">AryaLabs POS</p>
        </div>
    </div>

    <script>
        // Auto print on load (optional - uncomment if needed)
        // window.onload = function() {
        //     window.print();
        // }
        
        // Remove page numbers and URLs from print
        window.addEventListener('beforeprint', function() {
            // Hide any elements that might show page numbers or URLs
            document.body.style.margin = '0';
            document.body.style.padding = '0';
        });
        
        // Use CSS to hide headers and footers in print
        if (window.matchMedia) {
            const mediaQueryList = window.matchMedia('print');
            mediaQueryList.addListener(function(mql) {
                if (mql.matches) {
                    // Before print
                    document.body.style.margin = '0';
                    document.body.style.padding = '0';
                }
            });
        }
    </script>
</body>
</html>


