<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Installment Agreement - {{ $sale->invoice_number }}</title>
  
    <style>
/* Reset & Basics */
* { box-sizing: border-box; }

body {
    font-family: Arial, "Helvetica Neue", sans-serif;
    font-size: 13px;
    background: #f3f4f6;
    padding: 15px;
    margin: 0;
    color: #000;
}

/* ===== A4 PAGE CONTAINER ===== */
.page {
    width: 210mm;
    min-height: 297mm;
    background: #ffffff;
    margin: auto;
    padding: 10mm 12mm;
    border: 1px solid #ddd;
    box-sizing: border-box;
}

.company-header {
    text-align: center;
    border-bottom: 2px solid #000;
    padding-bottom: 8px;
    margin-bottom: 12px;
}

.company-title {
    font-size: 24px;
    font-weight: bold;
    letter-spacing: 1px;
    margin: 0;
}

.company-sub {
    font-size: 13px;
    margin-top: 4px;
    font-weight: bold;
}

.terms-title {
    font-size: 15px;
    font-weight: bold;
    text-align: center;
    text-decoration: underline;
    margin: 12px 0 8px 0;
}

.terms-list {
    margin: 0 0 15px 0;
    padding-left: 0;
    list-style: none;
}

.terms-list li {
    position: relative;
    padding-left: 18px;
    margin-bottom: 6px;
    line-height: 1.4;
    text-align: justify;
    font-size: 12px;
}

.terms-list li::before {
    content: "•";
    position: absolute;
    left: 0;
    top: 0;
    font-weight: bold;
}

.applicant-section {
    border: 1px solid #000;
    padding: 10px 12px;
    margin-bottom: 15px;
}

.applicant-title {
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 8px;
    text-decoration: underline;
}

.agreement-details-section {
    border: 1px solid #000;
    padding: 10px 12px;
    margin-bottom: 15px;
}

.dotted-row {
    margin-bottom: 6px;
    line-height: 1.6;
    font-size: 12.5px;
}

.dotted-line {
    border-bottom: 1px dotted #000;
    display: inline-block;
    padding: 0 5px;
    font-weight: bold;
}

.table-schedule {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    margin-bottom: 10px;
}

.table-schedule th, .table-schedule td {
    border: 1px solid #000;
    padding: 6px 4px;
    text-align: center;
    font-size: 11.5px;
}

.table-schedule th {
    background-color: #f2f2f2;
    font-weight: bold;
}

.footer-note {
    font-size: 11.5px;
    font-weight: bold;
    margin-top: 10px;
    line-height: 1.4;
}

.landmark-box {
    margin-top: 8px;
    font-size: 12px;
    font-weight: bold;
}

/* ===== PRINT ===== */
@page {
    size: A4;
    margin: 10mm;
}

@media print {
    body {
        background: none;
        padding: 0;
        margin: 0;
    }

    .page {
        width: 100%;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        border: none;
    }

    .no-print,
    .print-btn,
    button,
    .btn {
        display: none !important;
        visibility: hidden !important;
    }
}
</style>
</head>
<body>
    @php
        $installment = $sale->installmentAgreement;
        $isAdmin = auth()->user()->isAdmin();
    @endphp

    <div class="no-print" style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px; padding: 10px; background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">🖨️ Print Agreement Card</button>
        
        <a href="{{ route($isAdmin ? 'admin.pos' : 'cashier.pos') }}" 
           style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; text-decoration: none;">
            ← Back to POS
        </a>
    </div>

<div class="page">

    <!-- COMPANY HEADER -->
    <div class="company-header">
        <h1 class="company-title">J.C. ENTERPRISES</h1>
        <div class="company-sub">
            No. 107, Janudagama, Nawagama, Nikaweratiya. Tel: 077-2226894 / 077-1016893
        </div>
    </div>

    <!-- TERMS AND CONDITIONS -->
    <div class="terms-title">TERMS AND CONDITIONS OF THE AGREEMENT</div>
    <ul class="terms-list">
        <li>Ownership of the goods shall remain with the Company until the total amount due under the agreement has been paid in full.</li>
        <li>If any instalment payment is defaulted, the Company shall have the right to repossess the goods, and you shall have no right to oppose such action.</li>
        <li>The goods shall not be transferred, sold, or pledged to another person until all amounts due for the goods have been paid in full.</li>
        <li>If the goods are returned to or repossessed by the Company, amounts already paid to the Company shall not be refunded.</li>
        <li>Goods that have been repossessed may be reclaimed after all outstanding amounts are paid to the Company within 30 days.</li>
        <li>The Company shall not be responsible for any payment made without obtaining an official receipt.</li>
        <li>If payment is made to a service representative who visits you, obtaining a receipt is compulsory.</li>
        <li>A 10% late fee shall be charged on any amount outstanding after the agreed contract period. Please complete all payments within the stipulated period to avoid inconvenience.</li>
        <li>A 5% late fee shall be charged if an instalment remains unpaid for more than five days after its due date.</li>
        <li>You must retain this card until the applicable warranty period for the goods expires. If this card is lost, the warranty for the goods shall become void.</li>
        <li>I declare that all details supplied by me for the above agreement are true and accurate, and I undertake to pay the instalments on their respective due dates.</li>
    </ul>

    <!-- APPLICANT SECTION -->
    <div class="applicant-section">
        <div class="applicant-title">APPLICANT</div>
        <div class="dotted-row">
            <strong>Name:</strong> <span class="dotted-line" style="min-width: 80%;">{{ $sale->customer->name ?? '' }}</span>
        </div>
        <div class="dotted-row">
            <strong>Address:</strong> <span class="dotted-line" style="min-width: 80%;">{{ $sale->customer->address ?? '' }}</span>
        </div>
        <div class="dotted-row">
            <strong>NIC No.:</strong> <span class="dotted-line" style="min-width: 80%;">{{ $sale->customer->nic ?? '' }}</span>
        </div>
        <div class="dotted-row">
            <strong>Telephone No.:</strong> <span class="dotted-line" style="min-width: 75%;">{{ $sale->customer->phone ?? '' }}</span>
        </div>
        <div style="margin-top: 25px; display: flex; justify-content: flex-end;">
            <div style="text-align: center; width: 250px;">
                <div style="border-bottom: 1px dotted #000; height: 20px;"></div>
                <div style="font-weight: bold; margin-top: 4px;">Customer's Signature</div>
            </div>
        </div>
    </div>

    <!-- AGREEMENT DETAILS SECTION -->
    <div class="agreement-details-section">
        <div style="display: flex; justify-content: space-between;" class="dotted-row">
            <div><strong>Agreement No.:</strong> <span class="dotted-line" style="min-width: 150px;">{{ $installment->agreement_number ?: $sale->invoice_number }}</span></div>
            <div><strong>Date:</strong> <span class="dotted-line" style="min-width: 150px;">{{ \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d') }}</span></div>
        </div>

        <div class="dotted-row">
            <strong>Name:</strong> <span class="dotted-line" style="min-width: 60%;">{{ $sale->customer->name ?? '' }}</span>
            <span style="font-weight: bold; margin-left: 10px;">Mr./Ms.</span>
        </div>

        <div class="dotted-row">
            <strong>Address:</strong> <span class="dotted-line" style="min-width: 85%;">{{ $sale->customer->address ?? '' }}</span>
        </div>

        <div style="display: flex; justify-content: space-between;" class="dotted-row">
            <div style="width: 48%;"><strong>Telephone No.:</strong> <span class="dotted-line" style="min-width: 60%;">{{ $sale->customer->phone ?? '' }}</span></div>
            <div style="width: 48%;"><strong>NIC No.:</strong> <span class="dotted-line" style="min-width: 60%;">{{ $sale->customer->nic ?? '' }}</span></div>
        </div>

        <div class="dotted-row">
            <strong>Goods/Item:</strong>
            <span class="dotted-line" style="min-width: 80%;">
                @foreach($sale->items as $item)
                    {{ $item->item->name }}@if(!$loop->last), @endif
                @endforeach
            </span>
        </div>

        <div style="display: flex; justify-content: space-between;" class="dotted-row">
            <div style="width: 48%;"><strong>Initial Payment:</strong> <span class="dotted-line" style="min-width: 50%;">Rs. {{ number_format($installment->down_payment_amount ?? 0, 2) }}</span></div>
            <div style="width: 48%;"><strong>Total Value:</strong> <span class="dotted-line" style="min-width: 50%;">Rs. {{ number_format($sale->total_amount ?? 0, 2) }}</span></div>
        </div>

        <div class="dotted-row">
            <strong>Monthly Instalment and No. of Instalments:</strong>
            <span class="dotted-line" style="min-width: 50%;">Rs. {{ number_format($installment->monthly_installment_amount ?? 0, 2) }} x {{ $installment->number_of_installments ?? 0 }} Months</span>
        </div>
    </div>

    <!-- PAYMENT CARD TABLE -->
    <table class="table-schedule">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 12%;">R/No.</th>
                <th style="width: 8%;">5%</th>
                <th style="width: 8%;">10%</th>
                <th style="width: 15%;">Credit</th>
                <th style="width: 15%;">Debit</th>
                <th style="width: 18%;">Balance</th>
                <th style="width: 12%;">Sig:</th>
            </tr>
        </thead>
        <tbody>
            @php
                $payments = $installment ? $installment->payments : collect();
                $totalRows = max(12, count($payments));
            @endphp
            @for ($i = 0; $i < $totalRows; $i++)
                @php
                    $p = $payments[$i] ?? null;
                @endphp
                <tr>
                    <td>{{ $p ? \Carbon\Carbon::parse($p->payment_date)->format('Y-m-d') : '' }}</td>
                    <td>{{ $p ? ($p->receipt_number ?? $p->id) : '' }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ $p ? number_format($p->amount, 2) : '' }}</td>
                    <td></td>
                    <td>{{ $p ? number_format($p->balance_after ?? 0, 2) : '' }}</td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- FOOTER NOTES -->
    <div class="footer-note">
        This card must be produced when making any payment. (The Company will not be responsible for payments made without a receipt.)
    </div>
    <div class="landmark-box">
        Land Mark:- <span class="dotted-line" style="min-width: 80%;">{{ $sale->customer->landmark ?? '' }}</span>
    </div>

</div>
</body>
</html>
