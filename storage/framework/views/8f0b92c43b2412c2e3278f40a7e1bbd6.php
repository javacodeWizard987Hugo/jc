<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="card border-danger shadow-sm col-md-10 mx-auto">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0 fw-bold">
                🛠️ Create Warranty Claim Job
            </h4>
            <?php
                $backRoute = auth()->user()->isAdmin() ? route('admin.warranty-jobs.index') : route('cashier.warranty-jobs.index');
            ?>
            <a href="<?php echo e($backRoute); ?>" class="btn btn-light btn-sm fw-bold">
                ⬅️ Back to Jobs List
            </a>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="<?php echo e(auth()->user()->isAdmin() ? route('admin.warranty-jobs.store') : route('cashier.warranty-jobs.store')); ?>">
                <?php echo csrf_field(); ?>

                <div class="row g-3">
                    
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Select Warranty Item / Serial Number <span class="text-danger">*</span></label>
                        <select name="warranty_id" class="form-select border-danger" required id="warranty_id_select">
                            <option value="">-- Select Warranty Record --</option>
                            <?php $__currentLoopData = $warranties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warranty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($warranty->id); ?>" <?php echo e(request('warranty_id') == $warranty->id ? 'selected' : ''); ?>>
                                    Item: <?php echo e($warranty->serialNumber->item->name ?? 'N/A'); ?> | 
                                    S/N: <?php echo e($warranty->serialNumber->serial_number ?? 'N/A'); ?> | 
                                    Inv #: <?php echo e($warranty->saleItem->sale->invoice_number ?? 'N/A'); ?> | 
                                    Customer: <?php echo e($warranty->customer->name ?? 'N/A'); ?> (<?php echo e($warranty->customer->nic ?? 'No NIC'); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Branch Receiving Claim <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select border-danger" required>
                            <option value="">-- Select Branch --</option>
                            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($branch->id); ?>" <?php echo e((auth()->user()->branch_id == $branch->id || old('branch_id') == $branch->id) ? 'selected' : ''); ?>>
                                    <?php echo e($branch->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Claim Date <span class="text-danger">*</span></label>
                        <input type="date" name="claim_date" class="form-control border-danger" value="<?php echo e(old('claim_date', now()->format('Y-m-d'))); ?>" required>
                    </div>

                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Claim Type <span class="text-danger">*</span></label>
                        <select name="claim_type" class="form-select border-danger" required>
                            <option value="repair" <?php echo e(old('claim_type') == 'repair' ? 'selected' : ''); ?>>Repair</option>
                            <option value="replacement" <?php echo e(old('claim_type') == 'replacement' ? 'selected' : ''); ?>>Replacement</option>
                            <option value="inspection" <?php echo e(old('claim_type') == 'inspection' ? 'selected' : ''); ?>>Inspection Only</option>
                        </select>
                    </div>

                    
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Problem Description <span class="text-danger">*</span></label>
                        <textarea name="problem_description" class="form-control border-danger" rows="3" placeholder="Describe the fault reported by customer..." required><?php echo e(old('problem_description')); ?></textarea>
                    </div>

                    
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes..."><?php echo e(old('remarks')); ?></textarea>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-danger btn-lg px-5 fw-bold">
                            ➕ Create Warranty Job
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\jc\resources\views/admin/warranty-jobs/create.blade.php ENDPATH**/ ?>