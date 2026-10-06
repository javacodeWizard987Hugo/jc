<?php $__env->startSection('content'); ?>
<div class="container-fluid">

    <div class="card border-danger shadow-sm">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0 fw-bold">
                🛠️ Warranty & Service Job Register
            </h4>
            <?php
                $createJobRoute = auth()->user()->isAdmin() ? route('admin.warranty-jobs.create') : route('cashier.warranty-jobs.create');
            ?>
            <a href="<?php echo e($createJobRoute); ?>" class="btn btn-light btn-sm fw-bold">
                ➕ Create Warranty Claim Job
            </a>
        </div>

        <div class="card-body">

            
            <div class="card border-0 shadow-sm mb-4 bg-light">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(auth()->user()->isAdmin() ? route('admin.warranty-jobs.index') : route('cashier.warranty-jobs.index')); ?>">
                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Search Jobs</label>
                                <input
                                    type="text"
                                    name="search"
                                    class="form-control border-danger"
                                    placeholder="Job # | Serial # | Invoice # | Customer Name / NIC / Phone | Item"
                                    value="<?php echo e(request('search')); ?>"
                                >
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Branch</label>
                                <select name="branch_id" class="form-select border-danger">
                                    <option value="">All Branches</option>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($branch->id); ?>" <?php echo e(request('branch_id') == $branch->id ? 'selected' : ''); ?>>
                                            <?php echo e($branch->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select border-danger">
                                    <option value="">All Statuses</option>
                                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($st); ?>" <?php echo e(request('status') == $st ? 'selected' : ''); ?>>
                                            <?php echo e($st); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Claim Date From</label>
                                <input type="date" name="start_date" class="form-control border-danger" value="<?php echo e(request('start_date')); ?>">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Claim Date To</label>
                                <input type="date" name="end_date" class="form-control border-danger" value="<?php echo e(request('end_date')); ?>">
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-danger fw-bold">
                                    🔍 Filter Jobs
                                </button>
                                <a href="<?php echo e(auth()->user()->isAdmin() ? route('admin.warranty-jobs.index') : route('cashier.warranty-jobs.index')); ?>" class="btn btn-secondary fw-semibold">
                                    Reset
                                </a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            
            <div class="table-responsive">
                <table class="table table-hover align-middle border">
                    <thead class="table-danger text-center">
                        <tr>
                            <th>Job #</th>
                            <th>Claim Date</th>
                            <th>Branch</th>
                            <th>Item & Serial Number</th>
                            <th>Invoice & Customer</th>
                            <th>Claim Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <strong class="text-danger fs-6"><?php echo e($job->job_number); ?></strong>
                                </td>
                                <td>
                                    <?php echo e(\Carbon\Carbon::parse($job->claim_date ?? $job->created_at)->format('d M Y')); ?>

                                </td>
                                <td><?php echo e($job->branch->name ?? 'Main Branch'); ?></td>
                                <td>
                                    <strong><?php echo e($job->warranty->serialNumber->item->name ?? 'N/A'); ?></strong>
                                    <div>
                                        <span class="badge bg-dark">
                                            S/N: <?php echo e($job->warranty->serialNumber->serial_number ?? 'N/A'); ?>

                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>Customer:</strong> <?php echo e($job->warranty->customer->name ?? 'N/A'); ?>

                                    </div>
                                    <div class="text-muted small">
                                        <strong>Inv #:</strong> <?php echo e($job->warranty->saleItem->sale->invoice_number ?? 'N/A'); ?>

                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary text-uppercase"><?php echo e($job->claim_type); ?></span>
                                </td>
                                <td>
                                    <?php
                                        $badgeClass = match($job->status) {
                                            'Received' => 'bg-info text-dark',
                                            'Sent to Service Center / Supplier' => 'bg-primary',
                                            'Under Repair' => 'bg-warning text-dark',
                                            'Returned to Branch' => 'bg-secondary',
                                            'Ready for Collection' => 'bg-success',
                                            'Collected' => 'bg-dark',
                                            'Rejected / Not Covered' => 'bg-danger',
                                            'Replaced' => 'bg-success',
                                            default => 'bg-secondary',
                                        };
                                    ?>
                                    <span class="badge <?php echo e($badgeClass); ?> fs-6">
                                        <?php echo e($job->status); ?>

                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $showJobRoute = auth()->user()->isAdmin()
                                            ? route('admin.warranty-jobs.show', $job->id)
                                            : route('cashier.warranty-jobs.show', $job->id);
                                    ?>
                                    <a href="<?php echo e($showJobRoute); ?>" class="btn btn-sm btn-outline-danger">
                                        👁️ View & Manage
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No warranty claim jobs found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="d-flex justify-content-end mt-3">
                <?php echo e($jobs->links('pagination::bootstrap-5')); ?>

            </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\jc\resources\views/admin/warranty-jobs/index.blade.php ENDPATH**/ ?>