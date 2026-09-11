<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proposal & Agreement - {{ $sale->invoice_number }}</title>
  
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
       to ensu2re the header aligns correctly on print */
    padding:2mm; 
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
     margin: 2mm -0mm 1px -22mm; /* cancel page padding */
    padding: 0mm 10mm 0px 22mm;       /* restore inner spacing */
    text-align: center;
    border-bottom: 2px solid #15ff00ff; /* Green line moved here */
   
}

.dots-container {
    display: flex;
    align-items: baseline;
    width: 100%;
    margin-bottom: 5px;
}
.dots-label {
    white-space: nowrap;
    padding-right: 10px;
    font-weight: bold;
}
.dots-value {
    font-weight: bold;
    padding: 0 5px;
}
.dots-space {
    flex-grow: 1;
    border-bottom: 1px dotted #000;
    margin-bottom: 3px;
}
.schedule-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.full-width {
    grid-column: span 2;
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

    .page {
        width: 100%;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        border: none;
    }

    /* Header image */
    .header-space img {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        max-height: 120px;
        object-fit: contain;
        display: block;
        margin: 0 auto;
    }

    /* Hide buttons when printing */
    .no-print,
    .print-btn,
    button,
    .btn {
        display: none !important;
        visibility: hidden !important;
    }

    .content,
    .content-wrapper,
    .print-content {
        width: 100%;
        max-width: 100%;
        margin: 0;
        box-sizing: border-box;
    }
}

</style>
</head>
<body>
    @php
        $installment = $sale->installmentAgreement;
    @endphp

    @php
        $isAdmin = auth()->user()->isAdmin();
    @endphp
    <div class="no-print" style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px; padding: 10px; background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">🖨️ Print Agreement</button>
        
        <a href="{{ route($isAdmin ? 'admin.pos' : 'cashier.pos') }}" 
           style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; text-decoration: none;">
            ← Back to POS
        </a>
    </div>
<div class="page">

    <!-- LETTERHEAD -->
    <div class="header-space">
        <img src="{{ asset('images/letter_head.png') }}" alt="Letter Head">
    </div>

    <!-- META -->
    <div class="agreement-meta" style="text-align:center;margin-bottom:18px;">
            <h1>GALLE CITY SOLUTION PVT LTD</h1>
            <p>(Incorporated in Sri Lanka under company act No.7 of 2007 – Reg No. PV 0332403)</p>
            <p>No 01, Opposite Bus stand, Kaduruwela.</p>
    </div>

        <<div class="section">
            PROPOSAL & AGREEMENT TO FINANCE THE FACILITY MENTION BELLOW
            <span class="cont-no">Cont. No {{ $installment->agreement_number ?: 'GCSL /' . str_pad($sale->id, 4, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="clear"></div>

        <p>The borrower bellow mentioned offer to take on finance, the facility mentioned below and enter into a GC Smart Life Loan agreement with Galle City Solution company on terms and condition set out in the schedule below and on the reveres hereof.</p>

        <div class="schedule-title">THE SCHEDULE</div>
<div class="section">
    <div class="dots-container">
        <div class="dots-label">Borrower Full name:</div>
        <div class="dots-value">{{ $sale->customer->name }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="schedule-grid">
        <div class="dots-container">
            <div class="dots-label">NIC No:</div>
            <div class="dots-value">{{ $sale->customer->nic }}</div>
            <div class="dots-space"></div>
        </div>
        <div class="dots-container">
            <div class="dots-label">Age:</div>
            <div class="dots-value">{{ $installment->customer_age }}</div>
            <div class="dots-space"></div>
        </div>
    </div>
    <div class="dots-container">
        <div class="dots-label">Permanent Address:</div>
        <div class="dots-value">{{ $sale->customer->address }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="dots-container">
        <div class="dots-label">T.P. No:</div>
        <div class="dots-value">{{ $sale->customer->phone }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="dots-container">
        <div class="dots-label">Occupation/Profession:</div>
        <div class="dots-value">{{ $installment->customer_occupation }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="dots-container">
        <div class="dots-label">Name & Address of the institute:</div>
        <div class="dots-value">{{ $installment->customer_institute_name_address }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="schedule-grid">
        <div class="dots-container">
            <div class="dots-label">Monthly Net Salary: Rs.</div>
            <div class="dots-value">{{ $installment->customer_monthly_salary }}</div>
            <div class="dots-space"></div>
        </div>
        <div class="dots-container">
            <div class="dots-label">Bank & Branch:</div>
            <div class="dots-value">{{ $installment->customer_bank_branch }}</div>
            <div class="dots-space"></div>
        </div>
    </div>
</div>

<div class="section-divider"></div>

<div class="section">
    <div class="dots-container">
        <div class="dots-label">Facility:</div>
        <div class="dots-value">
            @foreach($sale->items as $item)
                {{ $item->item->name }}@if(!$loop->last), @endif
            @endforeach
        </div>
        <div class="dots-space"></div>
    </div>
    
    <div class="schedule-grid">
        <div class="dots-container">
            <div class="dots-label">Cost of the Item: Rs.</div>
            <div class="dots-value">{{ number_format($sale->subtotal, 2) }}</div>
            <div class="dots-space"></div>
        </div>
        <div class="dots-container">
            <div class="dots-label">Vendor:</div>
            <div class="dots-value">Galle City fancy House</div>
            <div class="dots-space"></div>
        </div>

        <div class="dots-container">
            <div class="dots-label">Initial Payment: Rs.</div>
            <div class="dots-value">{{ number_format($installment->down_payment_amount, 2) }}</div>
            <div class="dots-space"></div>
        </div>
        <div class="dots-container">
            <div class="dots-label">Agreed Value: Rs.</div>
            <div class="dots-value">{{ number_format($sale->total_amount, 2) }}</div>
            <div class="dots-space"></div>
        </div>

        <div class="dots-container">
            <div class="dots-label">Amount Finance: Rs.</div>
            <div class="dots-value">{{ number_format($installment->balance_amount, 2) }}</div>
            <div class="dots-space"></div>
        </div>
        @php
            function numberToWords($num) {
                $ones = array(0 =>"Zero", 1 => "One", 2 => "Two", 3 => "Three", 4 => "Four", 5 => "Five", 6 => "Six", 7 => "Seven", 8 => "Eight", 9 => "Nine", 10 => "Ten", 11 => "Eleven", 12 => "Twelve", 13 => "Thirteen", 14 => "Fourteen", 15 => "Fifteen", 16 => "Sixteen", 17 => "Seventeen", 18 => "Eighteen", 19 => "Nineteen");
                $tens = array(0 => "Zero", 1 => "Ten", 2 => "Twenty", 3 => "Thirty", 4 => "Forty", 5 => "Fifty", 6 => "Sixty", 7 => "Seventy", 8 => "Eighty", 9 => "Ninety");
                if ($num < 20) return $ones[$num];
                if ($num < 100) return $tens[floor($num/10)] . ($num % 10 > 0 ? " " . $ones[$num % 10] : "");
                return $num;
            }
            $periodWords = numberToWords($installment->number_of_installments) . " Months";
            $noOfInstWords = numberToWords($installment->number_of_installments) . " (" . str_pad($installment->number_of_installments, 2, '0', STR_PAD_LEFT) . ")";
            
            $day = (int) $installment->due_day_of_month;
            $ends = array('th','st','nd','rd','th','th','th','th','th','th');
            if ((($day % 100) >= 11) && (($day % 100) <= 13)) $ordinal = $day. 'th';
            else $ordinal = $day. $ends[$day % 10];
        @endphp
        <div class="dots-container">
            <div class="dots-label">Period:</div>
            <div class="dots-value">{{ $periodWords }}</div>
            <div class="dots-space"></div>
        </div>

        <div class="dots-container">
            <div class="dots-label">Monthly Installment: Rs.</div>
            <div class="dots-value">{{ number_format($installment->monthly_installment_amount, 2) }}</div>
            <div class="dots-space"></div>
        </div>
        <div class="dots-container">
            <div class="dots-label">No of Installments:</div>
            <div class="dots-value">{{ $noOfInstWords }}</div>
            <div class="dots-space"></div>
        </div>
    </div>
    <div class="dots-container">
        <div class="dots-label">Next Installment due on:</div>
        <div class="dots-value">{{ \Carbon\Carbon::parse($installment->first_due_date)->format('jS F Y') }}</div>
        <div class="dots-space"></div>
        <div class="dots-label" style="padding-left: 10px;">And thereafter on the {{ $ordinal }}.day of each succeeding month</div>
    </div>
</div>

        <div class="section-divider"></div>

<div class="section">
    <div class="dots-container">
        <div class="dots-label">1st Guarantor Full name:</div>
        <div class="dots-value">{{ $installment->guarantor_name }}</div>
        <div class="dots-space"></div>
        <div class="dots-label" style="padding-left:10px;">NIC:</div>
        <div class="dots-value">{{ $installment->guarantor_nic }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="dots-container">
        <div class="dots-label">Address:</div>
        <div class="dots-value">{{ $installment->guarantor_address }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="dots-container">
        <div class="dots-label">T.P. No:</div>
        <div class="dots-value">{{ $installment->guarantor_mobile_number }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="dots-container">
        <div class="dots-label">Occupation/Profession:</div>
        <div class="dots-value">{{ $installment->guarantor_1_occupation }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="schedule-grid">
        <div class="dots-container">
            <div class="dots-label">Monthly Income: Rs.</div>
            <div class="dots-value">{{ $installment->guarantor_1_monthly_income }}</div>
            <div class="dots-space"></div>
        </div>
        <div class="dots-container">
            <div class="dots-label">Bank & Branch:</div>
            <div class="dots-value">{{ $installment->guarantor_1_bank_branch }}</div>
            <div class="dots-space"></div>
        </div>
    </div>
</div>

<div class="section-divider"></div>
<!--
<div class="section">
    <div class="dots-container">
        <div class="dots-label">2nd Guarantor Full name:</div>
        <div class="dots-value">{{ $installment->guarantor_2_name }}</div>
        <div class="dots-space"></div>
        <div class="dots-label" style="padding-left:10px;">NIC:</div>
        <div class="dots-value">{{ $installment->guarantor_2_nic }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="dots-container">
        <div class="dots-label">Address:</div>
        <div class="dots-value">{{ $installment->guarantor_2_address }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="dots-container">
        <div class="dots-label">T.P. No:</div>
        <div class="dots-value">{{ $installment->guarantor_2_phone }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="dots-container">
        <div class="dots-label">Occupation/Profession:</div>
        <div class="dots-value">{{ $installment->guarantor_2_occupation }}</div>
        <div class="dots-space"></div>
    </div>
    <div class="schedule-grid">
        <div class="dots-container">
            <div class="dots-label">Monthly Income: Rs.</div>
            <div class="dots-value">{{ $installment->guarantor_2_monthly_income }}</div>
            <div class="dots-space"></div>
        </div>
        <div class="dots-container">
            <div class="dots-label">Bank & Branch:</div>
            <div class="dots-value">{{ $installment->guarantor_2_bank_branch }}</div>
            <div class="dots-space"></div>
        </div>
    </div>
</div>

        <div class="section-divider"></div>

        <p>The borrower and the guarantors warrant that all particulars provided by them herein or other to the company in respect of their request for the above facility are true and correct. Executed by the borrower and the guarantors on this <span class="dotted-line">{{ now()->format('jS') }}</span> day of <span class="dotted-line">{{ now()->format('F Y') }}</span> at Polonnaruwa.</p>

        <div class="signature-row" style="display: flex; justify-content: space-between; margin-top: 40px;">
            <div style="width: 30%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">Signature of Borrower</div>
            <div style="width: 30%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">Signature of 1st Guarantor</div>
            <div style="width: 30%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">Signature of 2nd Guarantor</div>
        </div>

        <div style="margin-top: 20px;">
            Witness 1: <span class="dotted-line"></span> Name: <span class="dotted-line"></span> NIC NO: <span class="dotted-line"></span>
        </div>
        <div style="margin-top: 10px;">
            Accepted for and behalf of the company by <span class="dotted-line"></span> Authorized officer on the <span class="dotted-line"></span> day of <span class="dotted-line"></span> 20 <span class="dotted-line"></span> at <span class="dotted-line"></span>
        </div>

        <div class="signature-row" style="display: flex; justify-content: space-between; margin-top: 40px;">
            <div style="width: 45%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">
                Authorized Officer<br>(Company seal)
            </div>
            <div style="width: 45%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">
                Witness to the authorized officer<br>
                Name: <span class="dotted-line"></span>
            </div>
        </div>

        <!-- PAGE 2 -->
        <div class="page-break"></div>


        <p>This Agreement is made between Galle City Solution Company (hereinafter referred to as “the Company”) and Mr./Mrs. <span class="dotted-line">{{ $sale->customer->name }}</span>, the borrower described in the Schedule hereto (hereinafter referred to as “the Borrower”).</p>
        <p>Whereas the borrower is desirous of availing himself/herself of the facility more fully described in the schedule hereto and whereas the borrower has requested the company to provide him/her with a financial facility to enable the borrower to avail himself/herself of the said facility.</p>
        <p>And whereas the company has agreed to grant the finance facility for the said purpose subject to the terms and conditions set out below and, in the schedule, hereto.</p>

        <div class="section">
            <h2>TERMS AND CONDITIONS</h2>
            <ol>
                <li>The company shall pay to the Borrower the said requested loan facility amount of <span class="dotted-line"></span> (Rs. <span class="dotted-line">{{ number_format($installment->balance_amount, 2) }}</span>) to be repaid with an interest at the rate of 6% per month within a period of <span class="dotted-line">{{ $installment->number_of_installments }}</span> months from the date hereof.</li>
                <li>That in consideration of the Company granting to the Borrower the said loan of <span class="dotted-line"></span> (Rs. <span class="dotted-line">{{ number_format($installment->balance_amount, 2) }}</span>) as aforesaid the Borrower shall promptly and regularly repay the Company in <span class="dotted-line">{{ $installment->number_of_installments }}</span> monthly instalments of <span class="dotted-line"></span> (Rs <span class="dotted-line">{{ number_format($installment->monthly_installment_amount, 2) }}</span>) per month on or before the <span class="dotted-line">{{ $installment->due_day_of_month }}</span> day of each month commencing from <span class="dotted-line">{{ \Carbon\Carbon::parse($installment->first_due_date)->format('jS F Y') }}</span>. For the due and faithful fulfilment by the Borrower and as security for the monies so lent the Borrower shall make available,
                    <br>(I) Personal Guarantees of the said (1st) <span class="dotted-line">{{ $installment->guarantor_name }}</span> (2nd) <span class="dotted-line">{{ $installment->guarantor_2_name }}</span>
                </li>
                <li>The borrower shall pay default interest on all amounts that are not paid by the borrower on the due date at the rate of four (04) per month on all installments due and unpaid from the date they are due until the date of the payment default interest payable hereunder shall accrue and shall be payable duly without the necessity for any demand by the company.</li>
                <li>(a) The borrower agreed to indemnify the company of all stamp duty and other taxes at the time of agreement or in the future (including any fines and penalties) payable on or levied in respect of the facility granted in respect of any transaction contemplated by this agreement.
                    <br>(b)The borrower shall indemnity the company against all costs incurred in the enforcement of the agreements or any security ad for the collection of an amount payable hereunder including the company’s administration costs.
                </li>
                <li>The borrower agrees that the company may at any time assign the company’s rights interests and entitlement under these agreements.</li>
                <li>Upon the happening of any of the following events the company shall be entitled to terminate this agreement and to exercises all of its right and remedies against the borrower under the agreements and under any security (if any) including the institution of proceedings against the borrower and then the entire amount outstanding under the contract shall become immediately due and payable.
                    <br>(i) If the borrower fails or neglect to pay two or more instalments, payable under these agreements.
                    <br>(ii) If the borrower fails to company with any term of this agreement or shall breach any warranty made by it in respect of this agreement.
                    <br>(iii) If the borrower ceases threatens ceases to carry on its business or substantial part of business.
                    <br>(iv) If any writ, distress or execution is levied or issued against any property of the borrower.
                    <br>(v) If insolvency bankruptcy proceedings are institute against the borrower.
                </li>
                <li>IN CONSIDERATION of the foreseeing the guarantor hereby.
                    <br>(a) Jointly and severally guarantees to the company the regular and punctual payment by the borrower of all the monthly rentals specified in the schedule hereof and the performance and observance by the borrower of the severely terms and conditions herein contain
                    <br>(b) Bind him/herself jointly and severally to pay all monies fourth which may become payable to the company here under this whether by of rentals, debt, damage, interest, cost charges or any other monies whatsoever.
                    <br>(c) Agree that the company shall be entitled to sue the borrower and guarantor jointly and severely or to sue the guarantor only in the first instant before recourse is hard to the borrower.
                    <br>(d) Bind him/herself jointly and severally to pay forth with on demand to the company the amount of any judgment or decree that the company may obtain against the borrower under this agreement.
                    <br>(e) Renounce the right and privilege which the sureties by low are initialed to which is referred to in clause 9.
                    <br>(f) Agree that each of the guarantors is liable in all reselect here under to the same extent and in the same manner.
                </li>
                <li>The rights and the privilege of the sureties referred to in clause 8 (e) are as follows.
                    <br>(i) The beneficium ordines sue excursions –that is to say the right or privilege whereby a surety is entitled to claim that his Liability is of an accessory character and as such shall not be enforceable against him until the creditor has proceeded against the principal debtor in the first instant and failed to obtain satisfaction.
                    <br>(ii) The beneficium dives or the right or privilege where when several persons are sureties for a debt each of them when Sued for the whole amount may require the creditor to divide the claim and bring his action also against the other co-sureties each for his portion pro rata in so far as they are not insolvent.
                </li>
                <li>Any notice required to be given here under shall be deemed to be given if send by registered post to the parties at their respective addresses given above.</li>
                <li>Repossession and Device Control
                    <br>(I) Upon termination of this Agreement in terms of Clause 7, the Company may, without further notice, repossess the financed item wherever located. Ownership of the item shall remain with the Company until full settlement of all sums due. The Borrower shall surrender the item on demand and permit lawful entry for repossession.
                    <br>(II) If any instalment remains unpaid for seven (7) days from the due date, the Company may, without prior notice, disconnect, deactivate, suspend, or electronically block the device until all arrears are fully settled.
                    <br>(III) Repossession or blocking shall not prejudice the Company’s right to recover the full outstanding balance and exercise any other remedies available under this Agreement or at law.
                </li>
                <li>This agreement shall be deemed to have been entered into at Polonnaruwa and the District court of Polonnaruwa will have jurisdiction to hear and determine any action arising from this agreement.</li>
            </ol>
        </div>

        <p>We agree to the above terms.</p>

        <div class="signature-row" style="display: flex; justify-content: space-between; margin-top: 40px;">
            <div style="width: 30%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">The borrower</div>
            <div style="width: 30%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">1st guarantor</div>
            <div style="width: 30%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">2nd guarantor</div>
        </div>

        <div style="margin-top: 20px;">
            Witness 1: <span class="dotted-line"></span> Name: <span class="dotted-line"></span> NIC NO: <span class="dotted-line"></span>
        </div>
        <div style="margin-top: 10px;">
            Accepted for and behalf of the company by <span class="dotted-line"></span> Authorized officer on the <span class="dotted-line"></span> day of <span class="dotted-line"></span> 20 <span class="dotted-line"></span> at <span class="dotted-line"></span>
        </div>

        <div class="signature-row" style="display: flex; justify-content: space-between; margin-top: 40px;">
            <div style="width: 45%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">
                Authorized Officer<br>(Company seal)
            </div>
            <div style="width: 45%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">
                Witness to the authorized officer<br>
                Name: <span class="dotted-line"></span>
            </div>
        </div>
    </div>
</body>
</html>
