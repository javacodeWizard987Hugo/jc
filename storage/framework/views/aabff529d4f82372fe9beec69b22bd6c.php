<?php $__env->startSection('title', 'Items'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Page Header -->
    <div class="page-header">
        <div class="flex justify-between items-center">
            <div>
                <h1>Items Management</h1>
                <p>Manage all products and inventory items</p>
            </div>
            <a href="<?php echo e(route(($routePrefix ?? 'admin') . '.items.create')); ?>"
               class="btn btn-primary bg-white text-red-600 hover:bg-gray-100">
                <span class="mr-2">➕</span> Add New Item
            </a>
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="content-card overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Items</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-mono text-sm"><?php echo e($item->item_code); ?></td>

                        <td>
                            <div class="font-medium text-gray-900"><?php echo e($item->name); ?></div>
                            <?php if($item->isLowStock(auth()->user()->branch_id)): ?>
                                <span class="badge badge-danger text-xs mt-1">Low Stock</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <span class="badge badge-info"><?php echo e($item->category->name); ?></span>
                        </td>

                        <td>
                            <span class="font-semibold">
                                <?php echo e((int) $item->getBranchStock(auth()->user()->branch_id)); ?> PCS
                            </span>
                        </td>

                        <td class="text-gray-600">
                            Rs. <?php echo e(number_format($item->cost_price, 2)); ?> / PCS
                        </td>

                        <td class="font-semibold text-red-600">
                            Rs. <?php echo e(number_format($item->selling_price, 2)); ?> / PCS
                        </td>

                        <td>
                            <?php if($item->is_active): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="flex items-center space-x-2">
                                <a href="<?php echo e(route(($routePrefix ?? 'admin') . '.items.show', $item)); ?>"
                                   class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                    View
                                </a>

                                <span class="text-gray-300">|</span>

                                <a href="<?php echo e(route(($routePrefix ?? 'admin') . '.items.stock-history', $item)); ?>"
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    History
                                </a>

                                <span class="text-gray-300">|</span>

                                <a href="<?php echo e(route(($routePrefix ?? 'admin') . '.items.edit', $item)); ?>"
                                   class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    Edit
                                </a>

                                 <span class="text-gray-300">|</span>

                                <form action="<?php echo e(route(($routePrefix ?? 'admin') . '.items.destroy', $item)); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');" style="display: inline-block;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-800 text-sm font-medium">
                                        Delete
                                    </button>
                                </form>
                            
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-500">
                            <div class="text-4xl mb-2">📦</div>
                            <p>No items found. Create your first item!</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($items->hasPages()): ?>
        <div class="p-4 border-t border-gray-200">
            <?php echo e($items->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\jc_enterprice\resources\views/admin/items/index.blade.php ENDPATH**/ ?>