<?php $__env->startSection('title', 'Daily Installment Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center print:hidden">
        <h1 class="text-3xl font-bold text-black">Daily Installment Report Details</h1>
        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                ЁЯЦия╕П Print Current View
            </button>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="<?php echo e(route('admin.reports.daily-installments')); ?>" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-black mb-1">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="<?php echo e($startDate); ?>"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-black mb-1">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo e($endDate); ?>"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black">
                </div>
                <div>
                    <label for="customer_id" class="block text-sm font-medium text-black mb-1">Customer</label>
                    <select name="customer_id" id="customer_id" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black">
                        <option value="">All Customers</option>
                        <?php $__currentLoopData = $allCustomers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($customer->id); ?>" <?php echo e($customerId == $customer->id ? 'selected' : ''); ?>>
                                <?php echo e($customer->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label for="report_type" class="block text-sm font-medium text-black mb-1">Report Type</label>
                    <select name="report_type" id="report_type" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black">
                        <option value="all" <?php echo e($reportType == 'all' ? 'selected' : ''); ?>>All Collection</option>
                        <option value="down_payment" <?php echo e($reportType == 'down_payment' ? 'selected' : ''); ?>>Down Payments Only</option>
                        <option value="installment" <?php echo e($reportType == 'installment' ? 'selected' : ''); ?>>Installments Only</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                        Filter
                    </button>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                <a href="<?php echo e(route('admin.reports.export', 'daily-installments')); ?>?format=pdf&print=1&start_date=<?php echo e($startDate); ?>&end_date=<?php echo e($endDate); ?>&report_type=all<?php echo e($customerId ? '&customer_id=' . $customerId : ''); ?>" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                    ЁЯЦия╕П Print: Full Report
                </a>
                <a href="<?php echo e(route('admin.reports.export', 'daily-installments')); ?>?format=pdf&print=1&start_date=<?php echo e($startDate); ?>&end_date=<?php echo e($endDate); ?>&report_type=down_payment<?php echo e($customerId ? '&customer_id=' . $customerId : ''); ?>" 
                   class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm">
                    ЁЯЦия╕П Print: Down Payments
                </a>
                <a href="<?php echo e(route('admin.reports.export', 'daily-installments')); ?>?format=pdf&print=1&start_date=<?php echo e($startDate); ?>&end_date=<?php echo e($endDate); ?>&report_type=installment<?php echo e($customerId ? '&customer_id=' . $customerId : ''); ?>" 
                   class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 text-sm">
                    ЁЯЦия╕П Print: Installment Collection
                </a>
                <a href="<?php echo e(route('admin.reports.export', 'daily-installments')); ?>?format=csv&start_date=<?php echo e($startDate); ?>&end_date=<?php echo e($endDate); ?>&report_type=<?php echo e($reportType); ?><?php echo e($customerId ? '&customer_id=' . $customerId : ''); ?>" 
                   class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                    ЁЯУе Export CSV
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Interest</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $reportData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 <?php echo e($row['type'] == 'Down Payment' ? 'bg-blue-50' : ''); ?>">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black"><?php echo e($row['date']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black <?php echo e($row['type'] == 'Down Payment' ? 'font-semibold' : ''); ?>"><?php echo e($row['type']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black"><?php echo e($row['customer']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">#<?php echo e($row['invoice']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black"><?php echo e($row['due_date']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black"><?php echo e($row['method']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-black">Rs. <?php echo e(number_format($row['interest'], 2)); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-black">Rs. <?php echo e(number_format($row['amount'], 2)); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black"><?php echo e($row['notes']); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-black">
                            <p>No transactions found in the selected date range.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-sm text-right text-black">Total</td>
                        <td class="px-6 py-4 text-sm text-right text-black">Rs. <?php echo e(number_format($reportData->sum('amount'), 2)); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\jc_enterprice\resources\views/admin/reports/daily-installments.blade.php ENDPATH**/ ?>