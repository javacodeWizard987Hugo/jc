<?php $__env->startSection('title', 'Warranty & Service Job Register / Create Warranty Claim Job'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Page Header -->
    <div class="page-header border-l-4 border-red-600 bg-white p-6 rounded-lg shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 style="color: #DC2626;" class="text-2xl font-bold">
                🛠️ Warranty & Service Job Register / Create Warranty Claim Job
            </h1>
            <p class="text-sm text-gray-600 mt-1">Register a new warranty claim job for a customer item</p>
        </div>
        <?php
            $backRoute = auth()->user()->isAdmin() ? route('admin.warranty-jobs.index') : route('cashier.warranty-jobs.index');
        ?>
        <a href="<?php echo e($backRoute); ?>" class="btn btn-secondary text-sm font-semibold">
            ⬅️ Back to Jobs List
        </a>
    </div>

    <!-- Form Card -->
    <div class="content-card bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <form method="POST" action="<?php echo e(auth()->user()->isAdmin() ? route('admin.warranty-jobs.store') : route('cashier.warranty-jobs.store')); ?>">
            <?php echo csrf_field(); ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="md:col-span-2">
                    <label for="warranty_id_select" class="form-label font-bold text-gray-800">
                        Select Warranty Item / Serial Number <span class="text-red-600">*</span>
                    </label>
                    <select name="warranty_id" id="warranty_id_select" class="form-select w-full border-red-400 focus:border-red-600 focus:ring-red-200" required>
                        <option value="">-- Select Warranty Record --</option>
                        <?php $__currentLoopData = $warranties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warranty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $itemName = $warranty->serialNumber->item->name ?? 'N/A';
                                $serialNo = $warranty->serialNumber->serial_number ?? 'N/A';
                                $invNo = $warranty->saleItem->sale->invoice_number ?? 'N/A';
                                $cust = $warranty->customer ?? $warranty->saleItem?->sale?->customer;
                                $custName = $cust?->name ?? 'N/A';
                                $custNic = $cust?->nic ?? 'No NIC';
                            ?>
                            <option value="<?php echo e($warranty->id); ?>" <?php echo e((request('warranty_id') == $warranty->id || old('warranty_id') == $warranty->id) ? 'selected' : ''); ?>>
                                Item: <?php echo e($itemName); ?> | S/N: <?php echo e($serialNo); ?> | Inv #: <?php echo e($invNo); ?> | Customer: <?php echo e($custName); ?> (<?php echo e($custNic); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['warranty_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div>
                    <label for="branch_id" class="form-label font-bold text-gray-800">
                        Branch Receiving Claim <span class="text-red-600">*</span>
                    </label>
                    <select name="branch_id" id="branch_id" class="form-select w-full border-red-400 focus:border-red-600 focus:ring-red-200" required>
                        <option value="">-- Select Branch --</option>
                        <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($branch->id); ?>" <?php echo e((auth()->user()->branch_id == $branch->id || old('branch_id') == $branch->id) ? 'selected' : ''); ?>>
                                <?php echo e($branch->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['branch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div>
                    <label for="claim_date" class="form-label font-bold text-gray-800">
                        Claim Date <span class="text-red-600">*</span>
                    </label>
                    <input type="date" name="claim_date" id="claim_date" class="form-control w-full border-red-400 focus:border-red-600 focus:ring-red-200" value="<?php echo e(old('claim_date', now()->format('Y-m-d'))); ?>" required>
                    <?php $__errorArgs = ['claim_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="md:col-span-2">
                    <label for="claim_type" class="form-label font-bold text-gray-800">
                        Claim Type <span class="text-red-600">*</span>
                    </label>
                    <select name="claim_type" id="claim_type" class="form-select w-full border-red-400 focus:border-red-600 focus:ring-red-200" required>
                        <option value="repair" <?php echo e(old('claim_type') == 'repair' ? 'selected' : ''); ?>>Repair</option>
                        <option value="replacement" <?php echo e(old('claim_type') == 'replacement' ? 'selected' : ''); ?>>Replacement</option>
                        <option value="inspection" <?php echo e(old('claim_type') == 'inspection' ? 'selected' : ''); ?>>Inspection Only</option>
                    </select>
                    <?php $__errorArgs = ['claim_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="md:col-span-2">
                    <label for="problem_description" class="form-label font-bold text-gray-800">
                        Problem Description <span class="text-red-600">*</span>
                    </label>
                    <textarea name="problem_description" id="problem_description" class="form-control w-full border-red-400 focus:border-red-600 focus:ring-red-200" rows="3" placeholder="Describe the fault reported by customer..." required><?php echo e(old('problem_description')); ?></textarea>
                    <?php $__errorArgs = ['problem_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="md:col-span-2">
                    <label for="remarks" class="form-label font-bold text-gray-800">
                        Remarks / Notes
                    </label>
                    <textarea name="remarks" id="remarks" class="form-control w-full" rows="2" placeholder="Optional notes..."><?php echo e(old('remarks')); ?></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="btn btn-danger px-8 py-3 font-bold text-lg">
                    ➕ Create Warranty Job
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\jc\resources\views/admin/warranty-jobs/create.blade.php ENDPATH**/ ?>