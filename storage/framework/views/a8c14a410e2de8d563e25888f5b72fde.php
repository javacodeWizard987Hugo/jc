<?php $__env->startSection('content'); ?>
<div class="container-fluid">

    <div class="card border-danger shadow-sm">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-shield-alt me-2"></i> Warranty Register
            </h4>
        </div>

        <div class="card-body">

            
            <div class="card border-0 shadow-sm mb-4 bg-light">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(auth()->user()->isAdmin() ? route('admin.warranties.index') : route('cashier.warranties.index')); ?>">
                        <div class="row g-3 align-items-center">

                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Search Warranty Register</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-danger text-white border-danger">
                                        🔍
                                    </span>
                                    <input
                                        type="text"
                                        name="search"
                                        class="form-control border-danger"
                                        placeholder="Serial No | Invoice No | Customer Name / NIC / Phone | Item / Model"
                                        value="<?php echo e(request('search')); ?>"
                                    >
                                </div>
                            </div>

                            <div class="col-md-3">
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

                            <div class="col-md-2 d-grid" style="margin-top: 32px;">
                                <button type="submit" class="btn btn-danger fw-semibold">
                                    Search
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            
            <div class="table-responsive">
                <table class="table table-hover align-middle border">
                    <thead class="table-danger text-center">
                        <tr>
                            <th>Item / Model</th>
                            <th>Serial Number</th>
                            <th>Customer Name & NIC</th>
                            <th>Invoice # & Date</th>
                            <th>Branch</th>
                            <th>Start Date & Duration</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $warranties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warranty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($warranty->serialNumber->item->name ?? 'N/A'); ?></strong>
                                    <?php if(!empty($warranty->serialNumber->item->item_code)): ?>
                                        <div class="text-muted small">Code: <?php echo e($warranty->serialNumber->item->item_code); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-dark fs-6">
                                        <?php echo e($warranty->serialNumber->serial_number ?? 'N/A'); ?>

                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo e($warranty->customer->name ?? 'Walk-in Customer'); ?></strong>
                                    <?php if(!empty($warranty->customer->nic)): ?>
                                        <div class="text-muted small">NIC: <?php echo e($warranty->customer->nic); ?></div>
                                    <?php endif; ?>
                                    <?php if(!empty($warranty->customer->phone)): ?>
                                        <div class="text-muted small">Phone: <?php echo e($warranty->customer->phone); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-primary fw-bold">
                                        <?php echo e($warranty->saleItem->sale->invoice_number ?? 'N/A'); ?>

                                    </span>
                                    <?php if(!empty($warranty->saleItem->sale->created_at)): ?>
                                        <div class="text-muted small"><?php echo e($warranty->saleItem->sale->created_at->format('d M Y')); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo e($warranty->saleItem->sale->branch->name ?? 'Main Branch'); ?>

                                </td>
                                <td>
                                    <div><?php echo e(\Carbon\Carbon::parse($warranty->start_date)->format('d M Y')); ?></div>
                                    <span class="badge bg-info text-dark"><?php echo e($warranty->duration); ?> Months</span>
                                </td>
                                <td>
                                    <?php
                                        $isExpired = \Carbon\Carbon::parse($warranty->expiry_date)->isPast();
                                    ?>
                                    <span class="badge <?php echo e($isExpired ? 'bg-danger' : 'bg-success'); ?> fs-6">
                                        <?php echo e(\Carbon\Carbon::parse($warranty->expiry_date)->format('d M Y')); ?>

                                    </span>
                                    <?php if($isExpired): ?>
                                        <div class="text-danger small fw-bold mt-1">Expired</div>
                                    <?php else: ?>
                                        <div class="text-success small fw-bold mt-1">Active</div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $showRoute = auth()->user()->isAdmin()
                                            ? route('admin.warranties.show', $warranty->id)
                                            : route('cashier.warranties.show', $warranty->id);
                                    ?>
                                    <a href="<?php echo e($showRoute); ?>" class="btn btn-sm btn-outline-danger">
                                        👁️ View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No warranty records found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="d-flex justify-content-end mt-3">
                <?php echo e($warranties->links('pagination::bootstrap-5')); ?>

            </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\jc\resources\views/admin/warranties/index.blade.php ENDPATH**/ ?>