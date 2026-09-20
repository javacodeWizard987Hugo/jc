<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Welcome back, <?php echo e(auth()->user()->name); ?>!</p>
    </div>

    <!-- Today's Sales Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Held Bills Widget -->
        <a href="<?php echo e(route('admin.pos')); ?>?resume_held=1" class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-600 hover:bg-gray-50 transition relative overflow-hidden group">
            <div class="absolute right-0 top-0 mt-2 mr-2 text-purple-200 group-hover:text-purple-300 transition-colors">
                <svg class="h-12 w-12" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path>
                </svg>
            </div>
            <div class="relative z-10">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Held Bills</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-bold text-gray-900" id="heldBillsCount">...</p>
                            <p class="ml-2 text-xs font-semibold text-purple-600">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="mt-2 text-xs font-bold text-purple-700 bg-purple-100 py-1 px-2 rounded inline-block">
                    Resume in POS →
                </div>
            </div>
        </a>

        <a href="<?php echo e(route('admin.reports.sales')); ?>" class="bg-white rounded-lg shadow p-6 border-l-4 border-red-600 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Today's Sales</p>
                    <p class="text-2xl font-semibold text-gray-900">Rs. <?php echo e(number_format($todayTotal, 2)); ?></p>
                </div>
            </div>
        </a>

        <a href="<?php echo e(route('admin.reports.sales')); ?>" class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-600 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Today's Bills</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($todayBills); ?></p>
                </div>
            </div>
        </a>

        <!--<?php if($widgetSettings['show_expired_items'] ?? true): ?>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-600">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Expired Items</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($expiredItems->count()); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>-->

        <?php if($widgetSettings['show_low_stock'] ?? true): ?>
        <a href="<?php echo e(route('admin.reports.stock')); ?>" class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-600 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Low Stock Items</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($lowStockItems->count()); ?></p>
                </div>
            </div>
        </a>
        <?php endif; ?>

        <?php if($widgetSettings['show_profit'] ?? true): ?>
        <a href="<?php echo e(route('admin.reports.profit-loss')); ?>" class="bg-white rounded-lg shadow p-6 border-l-4 border-green-600 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Today's Profit</p>
                    <p class="text-2xl font-semibold <?php echo e($todayProfit >= 0 ? 'text-green-600' : 'text-red-600'); ?>">Rs. <?php echo e(number_format($todayProfit, 2)); ?></p>
                </div>
            </div>
        </a>
        <?php endif; ?>
    </div>

    <!-- Installment Monitoring -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <a href="<?php echo e(route('admin.installments.index')); ?>" class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-600 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Installments</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($activeInstallmentAgreements); ?></p>
                </div>
            </div>
        </a>
        <a href="<?php echo e(route('admin.installments.index')); ?>" class="bg-white rounded-lg shadow p-6 border-l-4 border-pink-600 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Outstanding Balance</p>
                    <p class="text-2xl font-semibold text-gray-900">Rs. <?php echo e(number_format($totalOutstandingInstallmentBalance, 2)); ?></p>
                </div>
            </div>
        </a>
        <a href="<?php echo e(route('admin.installments.overdue')); ?>" class="bg-white rounded-lg shadow p-6 border-l-4 border-red-600 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Overdue Customers</p>
                    <p class="text-2xl font-semibold text-gray-900"><?php echo e($overdueInstallmentCustomers); ?></p>
                </div>
            </div>
        </a>
        <a href="<?php echo e(route('admin.installments.overdue')); ?>" class="bg-white rounded-lg shadow p-6 border-l-4 border-red-600 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Overdue Amount</p>
                    <p class="text-2xl font-semibold text-gray-900">Rs. <?php echo e(number_format($overdueInstallmentAmount, 2)); ?></p>
                </div>
            </div>
        </a>
        <a href="<?php echo e(route('admin.installments.index')); ?>" class="bg-white rounded-lg shadow p-6 border-l-4 border-green-600 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Upcoming Payments</p>
                    <p class="text-2xl font-semibold text-gray-900">Rs. <?php echo e(number_format($upcomingInstallmentPayments, 2)); ?></p>
                </div>
            </div>
        </a>
    </div>


    <!-- Notifications Section -->
    <?php if(isset($notifications) && $notifications->count() > 0): ?>
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="h-6 w-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            Notifications
            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"><?php echo e($notifications->count()); ?></span>
        </h2>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-start p-4 rounded-lg border-l-4 
                <?php if($notification['color'] === 'red'): ?> bg-red-50 border-red-500
                <?php elseif($notification['color'] === 'orange'): ?> bg-orange-50 border-orange-500
                <?php elseif($notification['color'] === 'yellow'): ?> bg-yellow-50 border-yellow-500
                <?php else: ?> bg-blue-50 border-blue-500
                <?php endif; ?>
                <?php echo e(isset($notification['link']) ? 'cursor-pointer hover:shadow-md transition-shadow' : ''); ?>"
                <?php if(isset($notification['link'])): ?>
                onclick="window.location.href='<?php echo e($notification['link']); ?>'"
                <?php endif; ?>>
                <div class="flex-shrink-0 text-2xl mr-3"><?php echo e($notification['icon']); ?></div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900"><?php echo e($notification['title']); ?></h3>
                        <span class="text-xs text-gray-500"><?php echo e($notification['date']); ?></span>
                    </div>
                    <p class="mt-1 text-sm text-gray-700"><?php echo e($notification['message']); ?></p>
                    <?php if(isset($notification['link'])): ?>
                    <p class="mt-1 text-xs text-blue-600 font-medium">Click to view ledger →</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Alerts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Expired Items -->
      <!--  <?php if(($widgetSettings['show_expired_items'] ?? true) && $expiredItems->count() > 0): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-800 mb-4">⚠️ Expired Items</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $expiredItems->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between items-center bg-white p-3 rounded">
                    <span class="font-medium"><?php echo e($item->name); ?></span>
                    <span class="text-sm text-red-600">Expired: <?php echo e($item->expiry_date->format('M d, Y')); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>-->

        <!-- Near Expiry Items -->
      <!--  <?php if(($widgetSettings['show_expired_items'] ?? true) && $nearExpiryItems->count() > 0): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-yellow-800 mb-4">⚠️ Items Nearing Expiry</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $nearExpiryItems->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between items-center bg-white p-3 rounded">
                    <span class="font-medium"><?php echo e($item->name); ?></span>
                    <span class="text-sm text-yellow-600">Expires: <?php echo e($item->expiry_date->format('M d, Y')); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>-->

    <!-- Supplier Payments -->
    <?php if($widgetSettings['show_supplier_payments'] ?? true): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <?php if($overduePayments->count() > 0): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-800 mb-4">🔴 Overdue Supplier Payments</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $overduePayments->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between items-center bg-white p-3 rounded">
                    <div>
                        <span class="font-medium"><?php echo e($payment->supplier->name); ?></span>
                        <p class="text-xs text-gray-500">Due: <?php echo e($payment->due_date->format('M d, Y')); ?></p>
                    </div>
                    <span class="text-sm font-semibold text-red-600">Rs. <?php echo e(number_format($payment->outstanding_amount, 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($upcomingPayments->count() > 0): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-yellow-800 mb-4">⏰ Upcoming Supplier Payments</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $upcomingPayments->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between items-center bg-white p-3 rounded">
                    <div>
                        <span class="font-medium"><?php echo e($payment->supplier->name); ?></span>
                        <p class="text-xs text-gray-500">Due: <?php echo e($payment->due_date->format('M d, Y')); ?></p>
                    </div>
                    <span class="text-sm font-semibold text-yellow-600">Rs. <?php echo e(number_format($payment->outstanding_amount, 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Upcoming Expenses Widget -->
    <?php if(($widgetSettings['show_expenses'] ?? true) && $upcomingExpenses->count() > 0): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-yellow-800 mb-4">⏰ Upcoming Recurring Expenses</h3>
        <div class="space-y-2">
            <?php $__currentLoopData = $upcomingExpenses->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex justify-between items-center bg-white p-3 rounded">
                <div>
                    <span class="font-medium"><?php echo e($expense->description); ?></span>
                    <p class="text-xs text-gray-500"><?php echo e($expense->category->name); ?> | Due: <?php echo e($expense->next_due_date->format('M d, Y')); ?></p>
                </div>
                <span class="text-sm font-semibold text-yellow-600">Rs. <?php echo e(number_format($expense->amount, 2)); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payment Method Breakdown -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Sales by Payment Method</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php $__currentLoopData = $paymentBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="text-center p-4 bg-gray-50 rounded">
                <p class="text-sm text-gray-500"><?php echo e(ucfirst($method)); ?></p>
                <p class="text-xl font-semibold text-gray-900">Rs. <?php echo e(number_format($data['amount'], 2)); ?></p>
                <p class="text-xs text-gray-400"><?php echo e($data['count']); ?> bills</p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Sales by Cashier -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Sales by Cashier</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php $__currentLoopData = $cashierBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cashierId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="text-center p-4 bg-gray-50 rounded">
                <p class="text-sm text-gray-500"><?php echo e($data['name']); ?></p>
                <p class="text-xl font-semibold text-gray-900">Rs. <?php echo e(number_format($data['amount'], 2)); ?></p>
                <p class="text-xs text-gray-400"><?php echo e($data['count']); ?> bills</p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Customer Credit Dates -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <?php if($overdueCredits->count() > 0): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-800 mb-4">🔴 Overdue Customer Credits</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $overdueCredits->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $credit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between items-center bg-white p-3 rounded">
                    <div>
                        <span class="font-medium"><?php echo e($credit->customer->name ?? 'Unknown'); ?></span>
                        <p class="text-xs text-gray-500">Due: <?php echo e($credit->due_date->format('M d, Y')); ?></p>
                        <p class="text-xs text-gray-400">Invoice: <?php echo e($credit->sale->invoice_number ?? 'N/A'); ?></p>
                    </div>
                    <span class="text-sm font-semibold text-red-600">Rs. <?php echo e(number_format($credit->outstanding_amount, 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($upcomingCredits->count() > 0): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-yellow-800 mb-4">⏰ Upcoming Customer Credits</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $upcomingCredits->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $credit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between items-center bg-white p-3 rounded">
                    <div>
                        <span class="font-medium"><?php echo e($credit->customer->name ?? 'Unknown'); ?></span>
                        <p class="text-xs text-gray-500">Due: <?php echo e($credit->due_date->format('M d, Y')); ?></p>
                        <p class="text-xs text-gray-400">Invoice: <?php echo e($credit->sale->invoice_number ?? 'N/A'); ?></p>
                    </div>
                    <span class="text-sm font-semibold text-yellow-600">Rs. <?php echo e(number_format($credit->outstanding_amount, 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Supplier Cheque Dates -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <?php if($overdueCheques->count() > 0): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-800 mb-4">🔴 Overdue Supplier Cheques</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $overdueCheques->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cheque): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between items-center bg-white p-3 rounded">
                    <div>
                        <span class="font-medium"><?php echo e($cheque->supplier->name); ?></span>
                        <p class="text-xs text-gray-500">Cheque Date: <?php echo e($cheque->cheque_date->format('M d, Y')); ?></p>
                        <p class="text-xs text-gray-400">Cheque #: <?php echo e($cheque->cheque_number ?? 'N/A'); ?> | Bank: <?php echo e($cheque->bank_name ?? 'N/A'); ?></p>
                    </div>
                    <span class="text-sm font-semibold text-red-600">Rs. <?php echo e(number_format($cheque->outstanding_amount, 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($upcomingCheques->count() > 0): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-yellow-800 mb-4">⏰ Upcoming Supplier Cheques</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $upcomingCheques->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cheque): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between items-center bg-white p-3 rounded">
                    <div>
                        <span class="font-medium"><?php echo e($cheque->supplier->name); ?></span>
                        <p class="text-xs text-gray-500">Cheque Date: <?php echo e($cheque->cheque_date->format('M d, Y')); ?></p>
                        <p class="text-xs text-gray-400">Cheque #: <?php echo e($cheque->cheque_number ?? 'N/A'); ?> | Bank: <?php echo e($cheque->bank_name ?? 'N/A'); ?></p>
                    </div>
                    <span class="text-sm font-semibold text-yellow-600">Rs. <?php echo e(number_format($cheque->outstanding_amount, 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Customer Cheque Dates -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <?php if($overdueCustomerCheques->count() > 0): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-800 mb-4">🔴 Overdue Customer Cheques</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $overdueCustomerCheques->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cheque): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between items-center bg-white p-3 rounded">
                    <div>
                        <span class="font-medium"><?php echo e($cheque->sale->customer->name ?? 'Walk-in Customer'); ?></span>
                        <p class="text-xs text-gray-500">Cheque Date: <?php echo e($cheque->cheque_date->format('M d, Y')); ?></p>
                        <p class="text-xs text-gray-400">Invoice: <?php echo e($cheque->sale->invoice_number ?? 'N/A'); ?> | Cheque #: <?php echo e($cheque->cheque_number ?? 'N/A'); ?> | Bank: <?php echo e($cheque->bank_name ?? 'N/A'); ?></p>
                    </div>
                    <span class="text-sm font-semibold text-red-600">Rs. <?php echo e(number_format($cheque->amount, 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($upcomingCustomerCheques->count() > 0): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-yellow-800 mb-4">⏰ Upcoming Customer Cheques</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $upcomingCustomerCheques->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cheque): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between items-center bg-white p-3 rounded">
                    <div>
                        <span class="font-medium"><?php echo e($cheque->sale->customer->name ?? 'Walk-in Customer'); ?></span>
                        <p class="text-xs text-gray-500">Cheque Date: <?php echo e($cheque->cheque_date->format('M d, Y')); ?></p>
                        <p class="text-xs text-gray-400">Invoice: <?php echo e($cheque->sale->invoice_number ?? 'N/A'); ?> | Cheque #: <?php echo e($cheque->cheque_number ?? 'N/A'); ?> | Bank: <?php echo e($cheque->bank_name ?? 'N/A'); ?></p>
                    </div>
                    <span class="text-sm font-semibold text-yellow-600">Rs. <?php echo e(number_format($cheque->amount, 2)); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>


<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch held bills count from API
    fetch('/admin/bills/held')
        .then(response => response.json())
        .then(data => {
            const count = Array.isArray(data) ? data.length : 0;
            document.getElementById('heldBillsCount').textContent = count;
        })
        .catch(err => {
            console.error('Error fetching held bills:', err);
            document.getElementById('heldBillsCount').textContent = '0';
        });
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\jc\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>