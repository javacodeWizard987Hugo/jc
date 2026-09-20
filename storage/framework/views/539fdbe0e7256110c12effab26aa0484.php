<?php $__env->startSection('title', 'Installment Agreement'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $installment = $sale->installmentAgreement;
    $isAdmin = auth()->user()->isAdmin();
    $customerDetails = $customerDetails ?? session('customer_details') ?? [];
    $isVerified = $installment->otp_verified_at !== null;
    $routePrefix = $isAdmin ? 'admin' : 'cashier';
?>

<div class="max-w-5xl mx-auto px-4 py-6">

    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md border-2 border-gray-200 p-6 mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">🏦 Installment Agreement</h1>
            <p class="text-sm text-gray-600">Review & confirm installment details</p>
        </div>
        <div class="text-right">
            <p class="text-sm font-semibold text-gray-700">Invoice</p>
            <p class="text-lg font-bold text-red-600">#<?php echo e($sale->invoice_number); ?></p>
            <?php if($installment->agreement_number): ?>
                <p class="text-xs font-semibold text-gray-500">Agmt: <?php echo e($installment->agreement_number); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if($errors->any()): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Error!</strong>
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    
    <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-5 mb-6 no-print">
        <h3 class="font-bold text-yellow-900 mb-3">📱 SMS OTP Verification</h3>
        <p class="text-sm text-yellow-800 mb-4">An OTP must be verified before the agreement can be saved.</p>
        
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number</label>
                <input type="text" id="otp_phone" value="<?php echo e($sale->customer->phone); ?>" class="border-2 border-gray-300 rounded-lg px-3 py-2 bg-gray-100" readonly>
            </div>
            
          <?php if(!$isVerified): ?>
                <button type="button" id="send_otp_btn"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700">
                    Send OTP
                </button>

                <div id="otp_input_section" class="hidden flex items-end gap-2">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Enter OTP</label>
                        <input type="text" id="otp_code" maxlength="6" class="border-2 border-gray-300 rounded-lg px-3 py-2 w-32">
                    </div>
                    <button type="button" id="verify_otp_btn" class="bg-green-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-green-700">
                        Verify OTP
                    </button>
                </div>
            <?php else: ?>
                <div class="bg-green-100 text-green-700 px-4 py-2 rounded-lg font-bold border border-green-300">
                    ✅ Verified at <?php echo e($installment->otp_verified_at); ?>

                </div>
            <?php endif; ?>
        </div>
        <div id="otp_message" class="mt-2 text-sm font-semibold"></div>
    </div>

    
    <form id="agreement_form" method="POST" action="<?php echo e(route($routePrefix . '.installment-agreement.update', $sale->id)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <!-- Customer & Sale Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            <!-- Agreement No -->
            <div class="md:col-span-2 bg-white border-2 border-red-200 rounded-lg p-5">
                <h3 class="font-bold text-red-900 mb-3">📄 Agreement Number</h3>
                <div class="flex items-center gap-4">
                    <div class="flex-grow">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Agreement Number (Manual Entry)</label>
                        <input type="text" name="agreement_number" id="field_agreement_number" value="<?php echo e(old('agreement_number', $installment->agreement_number)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2" placeholder="Enter Agreement Number">
                    </div>
                </div>
            </div>

            <!-- Customer -->
            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-5">
                <h3 class="font-bold text-blue-900 mb-3">👤 Borrower Details</h3>
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="customer_name" id="field_customer_name" value="<?php echo e(old('customer_name', $customerDetails['name'] ?? $sale->customer->name)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Phone</label>
                            <input type="text" name="customer_phone" id="field_customer_phone" value="<?php echo e(old('customer_phone', $customerDetails['phone'] ?? $sale->customer->phone)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Customer NIC</label>
                            <input type="text" name="customer_nic" id="field_customer_nic" value="<?php echo e(old('customer_nic', $customerDetails['nic'] ?? $sale->customer->nic ?? '')); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Age</label>
                            <input type="text" name="customer_age" id="field_customer_age" value="<?php echo e(old('customer_age', $installment->customer_age)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Occupation/Profession</label>
                            <input type="text" name="customer_occupation" id="field_customer_occupation" value="<?php echo e(old('customer_occupation', $installment->customer_occupation)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Permanent Address</label>
                        <textarea name="customer_address" id="field_customer_address" rows="2" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist"><?php echo e(old('customer_address', $customerDetails['address'] ?? $sale->customer->address ?? 'N/A')); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Name & Address of the institute</label>
                        <input type="text" name="customer_institute_name_address" id="field_customer_institute_name_address" value="<?php echo e(old('customer_institute_name_address', $installment->customer_institute_name_address)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Monthly Net Salary</label>
                            <input type="text" name="customer_monthly_salary" id="field_customer_monthly_salary" value="<?php echo e(old('customer_monthly_salary', $installment->customer_monthly_salary)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Bank & Branch</label>
                            <input type="text" name="customer_bank_branch" id="field_customer_bank_branch" value="<?php echo e(old('customer_bank_branch', $installment->customer_bank_branch)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sale -->
            <div class="bg-green-50 border-2 border-green-200 rounded-lg p-5">
                <h3 class="font-bold text-green-900 mb-3">💰 Sale Summary</h3>
                <p><strong>Total Amount:</strong> Rs. <?php echo e(number_format($sale->total_amount, 2)); ?></p>
                <p><strong>Down Payment:</strong> Rs. <?php echo e(number_format($installment->down_payment_amount, 2)); ?></p>
                <p>
                    <strong>Balance:</strong>
                    <span class="text-red-600 font-bold">
                        Rs. <?php echo e(number_format($installment->balance_amount, 2)); ?>

                    </span>
                </p>
            </div>
        </div>

        <!-- 1st Guarantor Details -->
        <div class="bg-purple-50 border-2 border-purple-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-bold text-purple-900 mb-4">🧑‍⚖️ 1st Guarantor Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Full Name</label>
                    <input type="text" name="guarantor_name" id="field_guarantor_name" value="<?php echo e(old('guarantor_name', $installment->guarantor_name)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">NIC</label>
                    <input type="text" name="guarantor_nic" id="field_guarantor_nic" value="<?php echo e(old('guarantor_nic', $installment->guarantor_nic)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Mobile Number</label>
                    <input type="text" name="guarantor_mobile_number" id="field_guarantor_mobile_number" value="<?php echo e(old('guarantor_mobile_number', $installment->guarantor_mobile_number)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Address</label>
                    <input type="text" name="guarantor_address" id="field_guarantor_address" value="<?php echo e(old('guarantor_address', $installment->guarantor_address)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Occupation/Profession</label>
                    <input type="text" name="guarantor_1_occupation" id="field_guarantor_1_occupation" value="<?php echo e(old('guarantor_1_occupation', $installment->guarantor_1_occupation)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-bold mb-1">Monthly Income</label>
                        <input type="text" name="guarantor_1_monthly_income" id="field_guarantor_1_monthly_income" value="<?php echo e(old('guarantor_1_monthly_income', $installment->guarantor_1_monthly_income)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Bank & Branch</label>
                        <input type="text" name="guarantor_1_bank_branch" id="field_guarantor_1_bank_branch" value="<?php echo e(old('guarantor_1_bank_branch', $installment->guarantor_1_bank_branch)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 persist">
                    </div>
                </div>
            </div>
        </div>
        <!-- 2nd Guarantor Details -->
        <div class="bg-indigo-50 border-2 border-indigo-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-bold text-indigo-900 mb-4">🧑‍⚖️ 2nd Guarantor Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Full Name</label>
                    <input type="text" name="guarantor_2_name" id="field_guarantor_2_name" value="<?php echo e(old('guarantor_2_name', $installment->guarantor_2_name)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">NIC</label>
                    <input type="text" name="guarantor_2_nic" id="field_guarantor_2_nic" value="<?php echo e(old('guarantor_2_nic', $installment->guarantor_2_nic)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Mobile Number</label>
                    <input type="text" name="guarantor_2_phone" id="field_guarantor_2_phone" value="<?php echo e(old('guarantor_2_phone', $installment->guarantor_2_phone)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Address</label>
                    <input type="text" name="guarantor_2_address" id="field_guarantor_2_address" value="<?php echo e(old('guarantor_2_address', $installment->guarantor_2_address)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Occupation/Profession</label>
                    <input type="text" name="guarantor_2_occupation" id="field_guarantor_2_occupation" value="<?php echo e(old('guarantor_2_occupation', $installment->guarantor_2_occupation)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-bold mb-1">Monthly Income</label>
                        <input type="text" name="guarantor_2_monthly_income" id="field_guarantor_2_monthly_income" value="<?php echo e(old('guarantor_2_monthly_income', $installment->guarantor_2_monthly_income)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Bank & Branch</label>
                        <input type="text" name="guarantor_2_bank_branch" id="field_guarantor_2_bank_branch" value="<?php echo e(old('guarantor_2_bank_branch', $installment->guarantor_2_bank_branch)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2">
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial & Agreement Details -->
        <div class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-bold text-red-900 mb-4">💰 Financial & Agreement Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Item Price (Total Invoice Value)</label>
                    <input type="number" name="total_invoice_value" id="field_total_invoice_value" value="<?php echo e(old('total_invoice_value', $installment->total_invoice_value)); ?>" step="0.01" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2" required oninput="calculateBalance()">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Down Payment Amount</label>
                    <input type="number" name="down_payment_amount" id="field_down_payment_amount" value="<?php echo e(old('down_payment_amount', $installment->down_payment_amount)); ?>" step="0.01" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2" required oninput="calculateBalance()">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Down Payment Method</label>
                    <select name="down_payment_method" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2">
                        <option value="cash" <?php echo e(old('down_payment_method', $installment->down_payment_method) == 'cash' ? 'selected' : ''); ?>>Cash</option>
                        <option value="card" <?php echo e(old('down_payment_method', $installment->down_payment_method) == 'card' ? 'selected' : ''); ?>>Card</option>
                        <option value="cheque" <?php echo e(old('down_payment_method', $installment->down_payment_method) == 'cheque' ? 'selected' : ''); ?>>Cheque</option>
                        <option value="online" <?php echo e(old('down_payment_method', $installment->down_payment_method) == 'online' ? 'selected' : ''); ?>>Online</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Down Payment Date</label>
                    <input type="date" name="down_payment_date" value="<?php echo e(old('down_payment_date', $installment->down_payment_date ? \Carbon\Carbon::parse($installment->down_payment_date)->format('Y-m-d') : '')); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Balance Amount</label>
                    <input type="number" id="field_balance_amount" value="<?php echo e($installment->total_invoice_value - $installment->down_payment_amount); ?>" step="0.01" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Number of Installments</label>
                    <input type="number" name="number_of_installments" value="<?php echo e(old('number_of_installments', $installment->number_of_installments)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Monthly Installment Amount</label>
                    <input type="number" name="monthly_installment_amount" value="<?php echo e(old('monthly_installment_amount', $installment->monthly_installment_amount)); ?>" step="0.01" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Interest / Service Charge</label>
                    <input type="number" name="interest_service_charge" value="<?php echo e(old('interest_service_charge', $installment->interest_service_charge)); ?>" step="0.01" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">First Due Date</label>
                    <input type="date" name="first_due_date" value="<?php echo e(old('first_due_date', $installment->first_due_date)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Due Day of Month</label>
                    <input type="number" name="due_day_of_month" value="<?php echo e(old('due_day_of_month', $installment->due_day_of_month)); ?>" min="1" max="31" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Loan Tenor (Initial Months)</label>
                    <input type="number" name="loan_tenor" value="<?php echo e(old('loan_tenor', $installment->loan_tenor ?? $installment->number_of_installments)); ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2">
                </div>
            </div>
        </div>
        <!-- Agreement Confirmation -->
        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-5 mb-6 no-print">
            <label class="flex items-center gap-2 font-semibold text-sm">
                <input type="checkbox" required class="h-4 w-4">
                I confirm that the borrower and guarantors have agreed to these terms.
            </label>
        </div>

        <!-- Save Agreement -->
        <div class="flex justify-end gap-4 no-print">
            <a href="<?php echo e(route($isAdmin ? 'admin.installments.index' : 'cashier.pos')); ?>" class="px-6 py-3 bg-gray-400 text-white rounded-lg font-bold hover:bg-gray-500">
                Cancel
            </a>
             <a href="<?php echo e(route($routePrefix . '.installment-agreement.application-form', $sale->id)); ?>" target="_blank"
               class="px-6 py-3 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">
                🖨️ Print Application Form
            </a>

            <button type="submit" id="save_agreement_btn" <?php if(!$isVerified && !$isAdmin): ?> disabled <?php endif; ?>
                class="px-6 py-3 <?php echo e(($isVerified || $isAdmin) ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-400 cursor-not-allowed'); ?> text-white rounded-lg font-bold">
                💾 Save Agreement
            </button>
        </div>
    </form>

<div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-6 mt-8 no-print">

    <h3 class="text-lg font-bold text-gray-800 mb-4">📎 NIC Documents</h3>

    
    <form action="<?php echo e(route('admin.installments.nic-files', $installment->id)); ?>"
          method="POST"
          enctype="multipart/form-data"
          class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <?php echo csrf_field(); ?>

        <div>
            <label class="font-semibold">Customer NIC (Front)</label>
            <input type="file" name="customer_nic_front" class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="font-semibold">Customer NIC (Back)</label>
            <input type="file" name="customer_nic_back" class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="font-semibold">Guarantor NIC (Front)</label>
            <input type="file" name="guarantor_nic_front" class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="font-semibold">Guarantor NIC (Back)</label>
            <input type="file" name="guarantor_nic_back" class="border p-2 rounded w-full">
        </div>

        <div class="md:col-span-2 text-right">
            <button type="submit"
                class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg font-bold hover:bg-indigo-700">
                💾 Save NIC Files
            </button>
        </div>
    </form>

    
    <div class="space-y-2 text-sm">
        <?php $__currentLoopData = [
            'customer_nic_front' => 'Customer NIC (Front)',
            'customer_nic_back' => 'Customer NIC (Back)',
            'guarantor_nic_front' => 'Guarantor NIC (Front)',
            'guarantor_nic_back' => 'Guarantor NIC (Back)',
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <?php if($installment->$field): ?>
                <a href="<?php echo e(asset('storage/'.$installment->$field)); ?>"
                   target="_blank"
                   class="text-blue-600 font-semibold block">
                    👁 <?php echo e($label); ?>

                </a>
            <?php endif; ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

</div>



</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function calculateBalance() {
        const total = parseFloat(document.getElementById('field_total_invoice_value').value) || 0;
        const downPayment = parseFloat(document.getElementById('field_down_payment_amount').value) || 0;
        document.getElementById('field_balance_amount').value = (total - downPayment).toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function () {

        console.log("🚀 OTP SCRIPT LOADED");

        const sendOtpBtn = document.getElementById('send_otp_btn');
        const verifyOtpBtn = document.getElementById('verify_otp_btn');
        const otpInputSection = document.getElementById('otp_input_section');
        const otpMessage = document.getElementById('otp_message');
        const saveAgreementBtn = document.getElementById('save_agreement_btn');
        const otpCodeInput = document.getElementById('otp_code');
        const phoneInput = document.getElementById('otp_phone');
        
        // Form persistence logic
        const storageKey = 'installment_agreement_<?php echo e($sale->id); ?>';
        const persistFields = document.querySelectorAll('.persist');
        const agreementForm = document.getElementById('agreement_form');

        // Load persisted data
        const savedData = JSON.parse(localStorage.getItem(storageKey) || '{}');
        Object.keys(savedData).forEach(id => {
            const field = document.getElementById(id);
            if (field && !field.value) { // only if empty
                field.value = savedData[id];
            }
        });

        // Save data on change
        persistFields.forEach(field => {
            field.addEventListener('input', () => {
                const data = JSON.parse(localStorage.getItem(storageKey) || '{}');
                data[field.id] = field.value;
                localStorage.setItem(storageKey, JSON.stringify(data));
            });
        });

        // Clear persistence on form submit
        agreementForm.addEventListener('submit', () => {
            localStorage.removeItem(storageKey);
        });

        /* ================= SEND OTP ================= */
        if (sendOtpBtn) {
            sendOtpBtn.addEventListener('click', function () {

                const phone = phoneInput?.value;

                console.log("🔥 SEND OTP CLICKED");
                console.log("PHONE:", phone);

                if (!phone) {
                    alert("Phone missing!");
                    return;
                }

                // UI Loading State
                sendOtpBtn.disabled = true;
                sendOtpBtn.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span> Sending...';
                otpMessage.innerText = "Sending OTP to " + phone;
                otpMessage.className = "text-blue-600";

                fetch("<?php echo e(route($routePrefix . '.installment-agreement.otp.send', $sale->id)); ?>", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ phone })
                })
                .then(res => res.json())
                .then(data => {

                    console.log("OTP SEND RESPONSE:", data);

                    // Always show input section if we attempted to send, 
                    // because the gateway might have received it even if the response timed out
                    otpInputSection.classList.remove('hidden');

                    if (data.success) {
                        otpMessage.innerText = "OTP sent successfully";
                        otpMessage.className = "text-green-600";
                    } else {
                        otpMessage.innerText = data.message || "Failed to send OTP";
                        otpMessage.className = "text-red-600";
                    }
                })
                .catch(err => {
                    console.error("SEND OTP ERROR:", err);
                    otpMessage.innerText = "Server error sending OTP (Timeout?). Try verifying if OTP was received.";
                    otpMessage.className = "text-orange-600";
                    otpInputSection.classList.remove('hidden');
                })
                .finally(() => {
                    sendOtpBtn.disabled = false;
                    sendOtpBtn.innerText = "Resend OTP";
                });
            });
        }

        /* ================= VERIFY OTP ================= */
        if (verifyOtpBtn) {
            verifyOtpBtn.addEventListener('click', function () {

                const otp = otpCodeInput.value.trim();
                const phone = phoneInput?.value;

                console.log("🔥 VERIFY OTP CLICKED");
                console.log("OTP:", otp);

                if (!otp) {
                    alert("Enter OTP");
                    return;
                }

                // UI Loading State
                verifyOtpBtn.disabled = true;
                verifyOtpBtn.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span> Checking...';

                fetch("<?php echo e(route($routePrefix . '.installment-agreement.otp.verify', $sale->id)); ?>", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ phone, otp })
                })
                .then(res => res.json())
                .then(data => {

                    console.log("OTP VERIFY RESPONSE:", data);

                    if (data.success) {
                        otpMessage.innerText = "OTP Verified";
                        otpMessage.className = "text-green-600";

                        saveAgreementBtn.disabled = false;
                        saveAgreementBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                        saveAgreementBtn.classList.add('bg-red-600', 'hover:bg-red-700');
                        
                        otpInputSection.classList.add('hidden');
                        sendOtpBtn?.classList.add('hidden');
                    } else {
                        otpMessage.innerText = data.message || "Invalid OTP";
                        otpMessage.className = "text-red-600";
                    }
                })
                .catch(err => {
                    console.error("VERIFY OTP ERROR:", err);
                    otpMessage.innerText = "Server error verifying OTP";
                    otpMessage.className = "text-red-600";
                })
                .finally(() => {
                    verifyOtpBtn.disabled = false;
                    verifyOtpBtn.innerText = "Verify OTP";
                });
            });
        }

    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\jc\resources\views/cashier/installment_agreement_edit.blade.php ENDPATH**/ ?>