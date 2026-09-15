<html lang="en">
<head>
<meta charset="UTF-8">
<title>Loan Agreement - {{ $sale->invoice_number }}</title>

<style>
/* Reset & Basics */
* { box-sizing: border-box; }

body {
    font-family: "Times New Roman", serif;
    font-size: 13.5px;
    background: #f3f4f6;
    padding: 15px;
    margin: 0;
}

/* ===== A4 PAGE CONTAINER ===== */
.page {
    width: 210mm;
    min-height: 297mm;
    background: #ffffff;
    margin: auto;
    /* We remove padding here and put it on the table cells 
       to ensure the header aligns correctly on print */
    padding: 22mm; 
    border: 1px solid #ddd;
}

/* ===== TABLE LAYOUT FOR REPEATING HEADER ===== */
table.print-layout {
    width: 100%;
    border-collapse: collapse;
}

/* This is the magic line that repeats the header on every page */
thead { display: table-header-group; } 
tfoot { display: table-footer-group; }
tbody { display: table-row-group; }

/* Spacing for the header image container */
.header-space {
    height: auto;
     margin: 2mm -22mm 15px -22mm; /* cancel page padding */
    padding: 0mm 22mm 8px 22mm;       /* restore inner spacing */
    text-align: center;
    border-bottom: 2px solid #15ff00ff; /* Green line moved here */
   
}

.header-space img {
   width: 100%;
    max-width: 210cm;   /* full A4 width */
    max-height: 45cm;   /* increase height if needed */
    object-fit: contain;
}

/* ===== CONTENT STYLING ===== */
.agreement-meta {
    text-align: center;
    margin-bottom: 18px;
    font-size: 13.5px;
    margin-top: 10px;
}

.section {
    margin-bottom: 10px;
    line-height: 1.55;
    text-align: justify;
}

ol { margin-left: 18px; margin-top: 0; }
li { margin-bottom: 6px; }
hr { margin: 18px 0; border: 1px solid #ddd; }

/* ===== SIGNATURES ===== */
.signature-grid {
    font-size: 14px; /* increase overall size */
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* Adjusted to 3 col for better fit */
    gap: 15px;
    margin-top: 20px;
    page-break-inside: avoid; /* Keeps signatures together */
}

.signature-box { min-height: 100px; }
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
        width: 112%;
        max-width: 112%;
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
.print-btn button {
    background: #00e439ff;
    color: white;
    border: none;
    padding: 8px 18px;
    font-size: 13px;
    border-radius: 4px;
    cursor: pointer;
}

</style>
</head>

<body>

@php
    $installment = $sale->installmentAgreement;
@endphp

<div class="print-btn no-print">
    <button onclick="window.print()">🖨️ Print Loan Agreement</button>
</div>

<div class="page">
    
    <!-- WRAP EVERYTHING IN A TABLE -->
    <table class="print-layout">
        
        <!-- THEAD: Content inside here repeats on every print page -->
        <thead>
            <tr>
                <td>
                    <div class="header-space">
                         <!-- Letterhead Image -->
                        <img src="{{ asset('images/letter_head.png') }}" alt="Letter Head">
                    </div>
                </td>
            </tr>
        </thead>

        <!-- TBODY: The actual content flows here -->
        <tbody>
            <tr>
                <td>
                    <!-- ORIGINAL CONTENT STARTS HERE -->
                    
                    <div class="agreement-meta">
                        <p><strong>LOAN AGREEMENT</strong></p>
                        <p><strong>Agreement No:</strong> {{ $sale->invoice_number }}</p>
                    </div>

                    <div class="section">
                        This Agreement is entered into between:
                    </div>

                    <div class="section">
                        <strong>J.C. ENTERPRISES</strong>
                        (hereinafter referred to as <strong>“the Company”</strong>)
                    </div>

                    <div class="section"><strong>AND</strong></div>

                    <div class="section">
                        <strong>The Borrower</strong>, as detailed in <strong>Schedule (C)</strong>:
                        <span>
                            Name: <strong>{{ $sale->customer->name }}</strong>,
                            NIC: <strong>{{ $sale->customer->nic ?? 'N/A' }}</strong>,
                            Mobile: <strong>{{ $sale->customer->phone }}</strong>,
                            Address: <strong>{{ $sale->customer->address ?? 'N/A' }}</strong>.
                        </span>
                    </div>

                    <div class="section">
                        WHEREAS the Borrower has requested the Company to grant and advance the loan amount
                        specified in <strong>Schedule (C)</strong>, subject to the terms and conditions herein,
                        and has agreed to repay the same together with interest.
                    </div>

                    <div class="section"><strong>NOW IT IS HEREBY AGREED AS FOLLOWS:</strong></div>

                    <ol>
                        <li>
                            The Company agrees to grant a loan for the purchase of a mobile phone/electrical device
                            from the date of purchase, subject to the amount specified in <strong>Schedule (C)</strong>,
                            which shall not exceed
                            <strong>Rs. {{ number_format($sale->installmentAgreement->balance_amount, 2) }}</strong>
                            and/or 70% of the total value of the device, whichever is lower.
                        </li>

                        <li>
                            The Borrower agrees to accept the loan amount specified in <strong>Schedule (C)</strong>.
                        </li>

                        <li>
                            The Borrower acknowledges that the Company has advanced the loan for the purchase of
                            the mobile phone/electrical device and that the amount stated in the receipt
                            has been duly received.
                        </li>

                        <li>
                            The Borrower agrees to repay the loan amount together with interest at the rate
                            specified in <strong>Schedule (E)</strong>, or such higher rate as may be determined
                            by the Company from time to time, together with applicable government taxes and
                            other charges, in accordance with the repayment schedule specified in
                            <strong>Schedule (F)</strong>.
                        </li>

                        <li>
                            In the event of default in payment on the due date, the Borrower shall pay additional
                            interest as specified in <strong>Schedule (G)</strong> from the date of default until
                            full settlement. Such interest shall accrue on a daily basis without prejudice
                            to the Company’s rights.
                        </li>

                        <li>
                            Any balance certified as due by a statement of account prepared from the Company’s books
                            and signed by the Financial Manager or an authorised officer shall be sufficient
                            and conclusive evidence without further proof.
                        </li>

                        <li>
                            (a) If the Borrower defaults in payment, commits any act of insolvency, enters into a
                            composition with creditors, or becomes subject to bankruptcy proceedings,
                            the entire outstanding amount shall become immediately payable.
                            The Company shall also have the right to disconnect or block the device
                            within seven (7) days of default. <br><br>

                            (b) The Borrower shall not sell, transfer, pledge, dismantle or otherwise dispose
                            of the device without prior written consent of the Company. <br><br>

                            (c) Upon default, the Borrower shall immediately surrender the device to the Company.
                        </li>

                        <li>
                            The Borrower shall pay a commitment fee as specified in <strong>Schedule (H)</strong>,
                            being a percentage of the total value of the purchased device,
                            payable at the time of obtaining the facility.
                        </li>

                        <li>
                            All advances, principal, interest and other charges may be repaid by the Borrower
                            at any time on demand, without prior notice, irrespective of the loan period.
                        </li>

                        <li>
                            Any notice or demand shall be sent to the address stated herein or to such other
                            address as notified in writing by either party.
                        </li>

                        <li>
                            The Borrower consents to the disclosure of personal data to third parties for legal,
                            recovery, compliance, or lawful purposes, and agrees that such data may be shared
                            with authorised agents, affiliates, or contractors of
                            J.C. ENTERPRISES.
                        </li>

                        <li>
                            The Borrower consents to receive communications including SMS, messages,
                            notifications, reminders, promotional offers, and loan status updates
                            to the registered mobile number and/or email address.
                        </li>
                    </ol>

                    <hr>

                    <div class="signatures">
                        <p><strong>IN WITNESS WHEREOF</strong>, the parties have signed this Agreement.</p>

                        <div class="signature-grid">

                            <!-- Borrower -->
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <div class="signature-label">
                                    <strong>Borrower Signature</strong><br>
                                    Date: {{ now()->format('Y-m-d') }}
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
                    <!-- END CONTENT -->
                </td>
            </tr>
        </tbody>
    </table>
</div>

</body>
</html>
