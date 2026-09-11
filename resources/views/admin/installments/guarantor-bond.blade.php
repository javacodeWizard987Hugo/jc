<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Guarantor Bond - {{ $sale->invoice_number }}</title>

<style>
* { box-sizing: border-box; }

body {
    font-family: "Times New Roman", serif;
    font-size: 14.0px;
    background: #f3f4f6;
    padding: 15px;
}

/* ===== A4 PAGE ===== */
.page {
    width: 148mm;        /* A5 width */
    min-height: 210mm;   /* A5 height */
    background: #ffffff;
    margin: auto;
    padding: 22mm;
    border: 1px solid #ddd;
}

/* ===== HEADER ===== */
.header-space {
    margin: 0 0 15px 0;
    padding-bottom: 6mm;
    border-bottom: 2px solid #15ff00ff;
    text-align: center;
}

.header-space img {
    width: 100%;
    height: 30mm;
    object-fit: contain;
    display: block;
}


.header h1 {
    font-size: 20px;
    text-transform: uppercase;
    margin-bottom: 4px;
    color: #006311ff;
}

.header p {
    font-size: 12.5px;
    margin: 2px 0;
}

/* ===== LETTERHEAD ===== */
.letterhead img {
    width: 100%;
    max-height: 35mm;
    object-fit: contain;
}

/* ===== CONTENT ===== */


ol {
    margin-left: 18px;
}

li {
    margin-bottom: 6px;
}

hr {
    margin: 18px 0;
    border: 1px solid #ddd;
}

/* ===== SIGNATURES ===== */
.signature-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 20px;
}

.signature-box {
    min-height: 100px;
}

.signature-line {
   border-bottom: 1px solid #000;
    height: 25px;
    margin-bottom: 5px;
}

.signature-label {
    font-size: 14px;
    line-height: 1.6;
}
.signature-label strong {
    font-size: 15px; /* headings slightly bigger */
}

/* ===== PRINT ===== */

/* ===== PRINT ===== */

@page {
    size: A4;
    margin: 0;
}

@media print {

    body {
        background: none;
        padding: 0;
        margin: 0;
    }

    /* Page wrapper */
    .page {
        width: 100%;
        margin: 0;
        padding: 15mm; /* optional inner margin */
        box-sizing: border-box;
        border: none;
    }

    /* Header */
    .header-space,
    .header-space img {
        width: 100%;
        max-width: 100%;
        height: auto;
        display: block;
    }

    /* Content MUST match header width */
    .content,
    .content-wrapper,
    .print-content {
        width: 100%;
        max-width: 100%;
        margin: 0;
        box-sizing: border-box;
    }

    .no-print {
        display: none;
    }
}



/* ===== PRINT BUTTON ===== */
.print-btn {
    text-align: center;
    margin-bottom: 15px;
}
</style>
</head>

<body>

@php
    $installment = $sale->installmentAgreement;
@endphp

<div class="print-btn no-print">
    <button onclick="window.print()">🖨️ Print Guarantor Bond</button>
</div>

<div class="page">

    <!-- LETTERHEAD -->
    <div class="header-space">
        <img src="{{ asset('images/letter_head.png') }}" alt="Letter Head">
    </div>

    <!-- META -->
    <div class="agreement-meta" style="text-align:center;margin-bottom:18px;">
        <p><strong>GUARANTEE & INDEMNITY BOND</strong></p>
        <p><strong>Agreement No:</strong> {{ $sale->invoice_number }}</p>
        <p><strong>Business Registration No:</strong> PV 0332403</p>
    </div>

    <div class="section">
        This Guarantee and Indemnity is made in favour of:
    </div>

    <div class="section">
        <strong>Galle City Solution Pvt Ltd</strong>, a company duly incorporated under the laws of Sri Lanka
        with Business Registration No. PV 0332403
        (hereinafter referred to as <strong>“the Company”</strong>).
    </div>

    <div class="section"><strong>AND</strong></div>

    <div class="section">
        <strong>The Guarantor</strong>:
        
        Name: <strong>{{ $installment->guarantor_name }}</strong>,
        NIC: <strong>{{ $installment->guarantor_nic }}</strong>,
        Mobile: <strong>{{ $installment->guarantor_mobile_number }}</strong>,
        Address: <strong>{{ $installment->guarantor_address }}</strong>.
    </div>

    <div class="section">
        In consideration of the Company granting a loan facility to
        <strong>{{ $sale->customer->name }}</strong>,
        NIC No <strong>{{ $sale->customer->nic ?? 'N/A' }}</strong>
        (hereinafter referred to as <strong>“the Borrower”</strong>),
        the Guarantor hereby irrevocably agrees as follows:
    </div>

    <ol>
        <li>
            The Guarantor hereby jointly and severally guarantees the due and punctual
            repayment of the loan amount of
            <strong>Rs. {{ number_format($installment->total_invoice_value, 2) }}</strong>
            together with interest, penalties, costs and all other monies payable by the Borrower.
        </li>

        <li>
            This Guarantee shall be a continuing security and shall remain in full force
            until the loan facility is fully settled.
        </li>

        <li>
            The Guarantor’s liability shall not be affected by any indulgence, extension of time,
            variation, compromise or failure by the Company to enforce its rights against the Borrower.
        </li>

        <li>
            The Company shall be entitled to proceed against the Guarantor directly without
            first taking action against the Borrower.
        </li>

        <li>
            The Guarantor agrees to indemnify the Company against all losses, legal costs
            and expenses incurred due to default by the Borrower.
        </li>

        <li>
            This Guarantee shall bind the Guarantor’s heirs, successors and legal representatives.
        </li>
    </ol>

    <hr>

    <div class="section">
        <strong>Date:</strong> {{ now()->format('Y-m-d') }}
    </div>

    <!-- SIGNATURES -->
    <div class="signature-grid">

        <!-- Guarantor -->
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">
                <strong>Guarantor Signature</strong><br>
                Name: {{ $installment->guarantor_name }}
            </div>
        </div>

      
                        <!-- Witness A (Guarantor) -->
                            <div class="signature-box">
                                <div class="signature-line"></div>
                               <div class="signature-label">
                                    <strong>Signature</strong><br>
                                    <strong>Witness "A"</strong><br>

                                    Name:
                                    <span class="signature-data">{{ $installment->guarantor_name }}</span><br>

                                    NIC:
                                    <span class="signature-data">{{ $installment->guarantor_nic }}</span><br>

                                    Address:
                                    <span class="signature-data">{{ $installment->guarantor_address }}</span>
                                </div>

                            </div>

 
                            <!-- Witness B -->
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <div class="signature-label">
                                    <strong>Signature</strong>
                                    <strong>Witness "B"</strong><br>
                                    Name: __________________________<br>
                                    NIC: ___________________________<br>
                                    Address: _______________________
                                </div>
                            </div>
    </div>

</div>

</body>
</html>
