<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application Form - <?php echo e($sale->invoice_number); ?></title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        .container {
            width: 210mm;
            min-height: 297mm;
            padding: 0 20mm 20mm 20mm;
    margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            margin: 0 0 5px 0;
    padding: 0;
        }
        .header img {
            width: 100%;
            max-height: 100px;
            object-fit: contain;
        }
        .form-title {
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            text-align: center;
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-top: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        .form-row {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
        }
        .label {
            width: 150px;
            font-weight: normal;
        }
        .value-box {
            flex-grow: 1;
            border: 1px solid #000;
            padding: 4px 8px;
            min-height: 18px;
        }
        .checkbox-group {
            display: flex;
            gap: 15px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .square {
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            display: inline-block;
            text-align: center;
            line-height: 12px;
            font-size: 10px;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-line {
            border-top: 1px dotted #000;
            width: 200px;
            text-align: center;
            padding-top: 0px;
        }

        @media print {
            body {
                background-color: white;
            }
            .container {
                margin: 0;
                box-shadow: none;
                width: 100%;
            }
            .no-print {
                display: none;
            }
        }
        
        .print-btn-container {
            text-align: center;
            padding: 20px;
        }
        .btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .signature-box {
    width: 120px;
    height: 60px;
    border: 1px solid #000;
    margin: 0 auto 10px auto;
}

.signature-section {
    text-align: center;
}
    </style>
</head>
<body>
    <div class="no-print print-btn-container">
        <button class="btn" onclick="window.print()">Print Application Form</button>
        <button class="btn" style="background: #6c757d;" onclick="window.history.back()">Back</button>
    </div>

    <?php
        $installment = $sale->installmentAgreement;
    ?>

    <div class="container">
        <div class="header">
            <img src="<?php echo e(asset('images/letter_head.png')); ?>" alt="Company Letterhead">
        </div>

        <div class="section-title">
            <span>Applicant Details</span>
          
     <span>Agreement No:
    <span style="border-bottom: 1px solid #000; padding: 0 50px;">
    </span>
</span>
        </div>

        <div class="form-row">
            <div class="label">Title</div>
            <div class="checkbox-group">
                <div class="checkbox-item"><span class="square"></span> Mr</div>
                <div class="checkbox-item"><span class="square"></span> Mrs</div>
                <div class="checkbox-item"><span class="square"></span> Miss</div>
                <div class="checkbox-item"><span class="square"></span> Other</div>
            </div>
        </div>

        <div class="form-row">
            <div class="label">NIC Number</div>
            <div class="value-box"><?php echo e($sale->customer->nic); ?></div>
        </div>

        <div class="form-row">
            <div class="label">Full Name in NIC</div>
            <div class="value-box" style="height: 40px;"><?php echo e($sale->customer->name); ?></div>
        </div>

        <div class="form-row">
            <div class="label">Address</div>
            <div class="value-box" style="height: 30px;"><?php echo e($sale->customer->address); ?></div>
        </div>

        <div class="grid-2">
            <div class="form-row">
                <div class="label">Mobile No</div>
                <div class="value-box"><?php echo e($sale->customer->phone); ?></div>
            </div>
            <div class="form-row">
                <div class="label">Office No</div>
                <div class="value-box"></div>
            </div>
        </div>

        <div class="form-row">
            <div class="label">Name of Business</div>
            <div class="value-box"><?php echo e($installment->customer_institute_name_address); ?></div>
        </div>

        <div class="form-row">
            <div class="label">Designation</div>
            <div class="value-box"><?php echo e($installment->customer_occupation); ?></div>
        </div>

        <div class="section-title">Details of the Device</div>

        <div class="form-row">
            <div class="label">Device Details</div>
            <div class="checkbox-group">
                <div class="checkbox-item"><span class="square"></span> Phone</div>
                <div class="checkbox-item"><span class="square"></span> Tab</div>
                <div class="checkbox-item"><span class="square"></span> Electronic</div>
            </div>
        </div>

        <div class="form-row">
            <div class="label">Model of the Device</div>
            <div class="value-box">
                <?php $__currentLoopData = $sale->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo e($item->item->name); ?><?php if(!$loop->last): ?>, <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="form-row">
            <div class="label">EMI Number</div>
            <div class="value-box"><?php echo e($installment->combined_emi_numbers); ?></div>
        </div>

        <div class="grid-2">
            <div class="form-row">
                <div class="label">Market Retail Price</div>
                <div class="value-box"><?php echo e(number_format($sale->subtotal, 2)); ?></div>
            </div>
            <div></div>
        </div>

        <div class="grid-2">
            <div class="form-row">
                <div class="label">Down Payment</div>
                <div class="value-box"><?php echo e(number_format($installment->down_payment_amount, 2)); ?></div>
            </div>
            <div></div>
        </div>

        <div class="grid-2">
            <div class="form-row">
                <div class="label">Request Loan Amount</div>
                <div class="value-box"><?php echo e(number_format($installment->balance_amount, 2)); ?></div>
            </div>
            <div></div>
        </div>

        <div class="grid-2">
            <div class="form-row">
                <div class="label">Monthly Installment</div>
                <div class="value-box"><?php echo e(number_format($installment->monthly_installment_amount, 2)); ?></div>
            </div>
            <div class="form-row">
                <div class="label" style="width: 100px; padding-left: 10px;">Installment Date</div>
                <div class="value-box"><?php echo e($installment->due_day_of_month); ?></div>
            </div>
        </div>

        <div class="form-row">
            <div class="label">Loan Tenor</div>
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <span class="square"><?php echo e($installment->number_of_installments == 3 ? '✓' : ''); ?></span> 03 Month
                </div>
                <div class="checkbox-item">
                    <span class="square"><?php echo e($installment->number_of_installments == 6 ? '✓' : ''); ?></span> 06 Month
                </div>
                <div class="checkbox-item">
                    <span class="square"><?php echo e(!in_array($installment->number_of_installments, [3,6]) ? '✓' : ''); ?></span> Other (<?php echo e($installment->number_of_installments); ?> Months)
                </div>
            </div>
        </div>

        <div class="section-title">Particulars of Guarantor</div>

        <div class="form-row">
            <div class="label">Title</div>
            <div class="checkbox-group">
                <div class="checkbox-item"><span class="square"></span> Mr</div>
                <div class="checkbox-item"><span class="square"></span> Mrs</div>
                <div class="checkbox-item"><span class="square"></span> Miss</div>
                <div class="checkbox-item"><span class="square"></span> Other</div>
            </div>
        </div>

        <div class="form-row">
            <div class="label">NIC Number</div>
            <div class="value-box"><?php echo e($installment->guarantor_nic); ?></div>
        </div>

        <div class="form-row">
            <div class="label">Name with Initials</div>
            <div class="value-box"><?php echo e($installment->guarantor_name); ?></div>
        </div>

        <div class="form-row">
            <div class="label">Address</div>
            <div class="value-box" style="height: 30px;"><?php echo e($installment->guarantor_address); ?></div>
        </div>

        <div class="form-row">
            <div class="label">Mobile No</div>
            <div class="value-box"><?php echo e($installment->guarantor_mobile_number); ?></div>
        </div>

        <div class="footer">
    <div class="signature-section">
        <div class="signature-box"></div>
        <div class="signature-line">Applicant Finger Print</div>
    </div>
    <div style="margin-top: 40px; display: flex; justify-content: space-between;">
     <div>
        <div class="signature-line">Guarantor Signature</div>
    </div>
</div>

   <!-- <div class="signature-section">
        <div class="signature-box"></div>
        <div class="signature-line">Guarantor Finger Print</div>
    </div>-->
</div>

<div style="margin-top: 40px; display: flex; justify-content: space-between;">
    <div>
        <div class="signature-line">Applicant Signature</div>
    </div>

    <div>
        <div class="signature-line">Date</div>
    </div>
</div>
    </div>
</body>
</html>
<?php /**PATH E:\jc\resources\views/admin/installments/application-form.blade.php ENDPATH**/ ?>