<?php $__env->startSection('title', 'Create Item'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1>Create New Item</h1>
            <p>Add a new product to your inventory</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="content-card p-6">
        <form action="<?php echo e(route(($routePrefix ?? 'admin') . '.items.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div class="md:col-span-2">
                    <label for="item_code" class="form-label">Item Code *</label>
                    <input type="text" name="item_code" id="item_code" required
                               class="form-input" value="<?php echo e(old('item_code', $nextItemCode)); ?>">
                    <?php $__errorArgs = ['item_code'];
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


            </div>

            <div class="mb-6">
                <label for="name" class="form-label">Item Name *</label>
                <input type="text" name="name" id="name" required
                       class="form-input" value="<?php echo e(old('name')); ?>">
                <?php $__errorArgs = ['name'];
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="category_id" class="form-label">Category *</label>
                    <select name="category_id" id="category_id" required class="form-input">
                        <option value="">Select Category</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>"
                                <?php echo e(old('category_id') == $category->id ? 'selected' : ''); ?>>
                                <?php echo e($category->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['category_id'];
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
                    <label for="supplier_id" class="form-label">Default Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-input">
                        <option value="">Select Supplier</option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($supplier->id); ?>"
                                <?php echo e(old('supplier_id') == $supplier->id ? 'selected' : ''); ?>>
                                <?php echo e($supplier->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div>
                    <label for="cost_price" class="form-label">Cost Price (Per PCS) *</label>
                    <input type="number" step="0.01" name="cost_price" id="cost_price" required
                           class="form-input" value="<?php echo e(old('cost_price')); ?>">
                    <?php $__errorArgs = ['cost_price'];
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
                    <label for="selling_price" class="form-label">Selling Price (Per PCS) *</label>
                    <input type="number" step="0.01" name="selling_price" id="selling_price" required
                           class="form-input" value="<?php echo e(old('selling_price')); ?>">
                    <?php $__errorArgs = ['selling_price'];
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
                    <label for="retail_price" class="form-label">Retail Price (Per PCS)</label>
                    <input type="number" step="0.01" name="retail_price" id="retail_price"
                           class="form-input" value="<?php echo e(old('retail_price')); ?>">
                    <?php $__errorArgs = ['retail_price'];
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
                    <label for="unit_of_measure" class="form-label">Unit of Measure *</label>
                    <select name="unit_of_measure" id="unit_of_measure" required class="form-input">
                        <option value="pcs" selected>PCS (Pieces)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
               <div>
                    <label for="current_stock" class="form-label">Initial Stock (PCS) *</label>
                    <input type="number" min="0" step="1"
                           name="current_stock" id="current_stock"
                           class="form-input"
                           value="<?php echo e(old('current_stock', 1)); ?>" required>
                    <?php $__errorArgs = ['current_stock'];
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
                    <label for="reorder_level" class="form-label">Reorder Level (PCS)</label>
                    <input type="number" min="0" step="1"
                           name="reorder_level" id="reorder_level"
                           class="form-input"
                           value="<?php echo e(old('reorder_level', 0)); ?>" required>
                    <?php $__errorArgs = ['reorder_level'];
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
            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                 <div class="md:col-span-2">
               
                    <label for="is_active" class="form-label">Status</label>
                    <select name="is_active" id="is_active" class="form-input">
                         <option value="1" <?php echo e(old('is_active', '1') === '1' ? 'selected' : ''); ?>>Active</option>
                        <option value="0" <?php echo e(old('is_active') === '0' ? 'selected' : ''); ?>>Inactive</option>
                    </select>
                </div>

              <!--  <div class="flex items-center">
                    <input type="checkbox" name="requires_serial_number"
                           id="requires_serial_number" value="1"
                           <?php echo e(old('requires_serial_number') ? 'checked' : ''); ?>

                           class="h-4 w-4 text-green-700 focus:ring-green-600 border-gray-300 rounded">
                    <label for="requires_serial_number"
                           class="ml-2 block text-sm text-gray-900">
                        Requires Serial Number
                    </label>
                </div>

                <div>
                    <label for="warranty_duration_months" class="form-label">
                        Warranty (Months)
                    </label>
                    <input type="number" min="0"
                           name="warranty_duration_months"
                           id="warranty_duration_months"
                           class="form-input"
                           value="<?php echo e(old('warranty_duration_months')); ?>">
                </div>
            </div>-->

            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="<?php echo e(route(($routePrefix ?? 'admin') . '.items.index')); ?>"
                   class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Create Item
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\jc\resources\views/admin/items/create.blade.php ENDPATH**/ ?>