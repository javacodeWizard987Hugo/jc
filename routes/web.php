<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\GrnController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Admin\WarrantyController;
use App\Http\Controllers\Admin\WarrantyJobController;
use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Cashier\ShiftController;
use App\Http\Controllers\Admin\InstallmentController;
use App\Http\Controllers\Cashier\InstallmentAgreementController;
use App\Http\Controllers\Admin\CustomerHistoryController;

use App\Services\SmsService;


use App\Models\Sale;

Route::get('/proposal-agreement/{id}', function ($id) {
    $sale = Sale::findOrFail($id);

    return view('admin.installments.proposal-agreement', compact('sale'));
});
Route::post(
    '/installments/{agreement}/nic-files',
    [InstallmentAgreementController::class, 'updateNicFiles']
)->name('admin.installments.nic-files');

Route::get('/test-template-sms', function (\App\Services\SmsService $sms) {
    return $sms->sendSms(
        '94769689423',
        'installment_payment_received',
        [
            'CustomerName' => 'Test Customer',
            'Amount' => '2,000',
            'Date' => now()->toDateString(),
            'Balance' => '8,000',
            'NextDueDate' => now()->addMonth()->toDateString(),
        ]
    );
});

Route::get('/test-bulk-sms', function () {
    $sms = new SmsService();
    $recipients = ['94769689423','94771234567'];
    $message = 'This is a test bulk SMS from Laravel';
    return response()->json($sms->sendBulkSms($recipients, $message));
});

Route::get('/test-sms', function () {
    try {
        $sms = new SmsService();
        return response()->json(
            $sms->sendRawSms('94769689423', 'Test SMS from Laravel')
        );
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
});

// Login Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password Change (authenticated users only)
Route::middleware('auth')->group(function () {
    Route::get('/password/change', [\App\Http\Controllers\Auth\PasswordController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password/change', [\App\Http\Controllers\Auth\PasswordController::class, 'changePassword']);
});

// Registration Routes
Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);

Route::get('/', function () {
    return redirect()->route('login');
});

// CSRF Token endpoint for AJAX token refresh
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->middleware('web');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/installments/payments/{payment}/disconnect-receipt', [InstallmentController::class, 'printDisconnectReceipt'])->name('installments.payments.disconnect-receipt');
    
    // User Management
    Route::middleware('permission:user_management')->group(function () {
        Route::resource('users', UserController::class);
    });
    
    // Audit Logs
    Route::middleware('permission:audit_logs')->group(function () {
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])->name('audit-logs.show');
    });
    
    // Settings & Master Data
    Route::middleware('permission:settings')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::post('/suppliers/{supplier}/payments', [SupplierController::class, 'recordPayment'])->name('suppliers.payments');
        Route::get('/suppliers/payments/{payment}/cheque-details', [SupplierController::class, 'chequeDetails'])->name('suppliers.payments.cheque-details');
        
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('system-settings', [\App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('system-settings.index');
        Route::post('system-settings', [\App\Http\Controllers\Admin\SystemSettingController::class, 'store'])->name('system-settings.store');
        
        Route::get('/sms-campaigns', [App\Http\Controllers\Admin\SmsCampaignController::class, 'index'])->name('sms-campaigns.index');
        Route::get('/sms-campaigns/create', [App\Http\Controllers\Admin\SmsCampaignController::class, 'create'])->name('sms-campaigns.create');
        Route::post('/sms-campaigns', [App\Http\Controllers\Admin\SmsCampaignController::class, 'store'])->name('sms-campaigns.store');
        Route::get('/sms-campaigns/summary', [App\Http\Controllers\Admin\SmsCampaignController::class, 'summary'])->name('sms-campaigns.summary');
        Route::resource('sms-templates', \App\Http\Controllers\Admin\SmsTemplateController::class)->except(['show']);
        
        Route::resource('warranties', WarrantyController::class)->only(['index', 'show']);
        Route::resource('warranty-jobs', WarrantyJobController::class);
        Route::post('/warranty-jobs/{id}/collect', [WarrantyJobController::class, 'collect'])->name('warranty-jobs.collect');
    });
    
    // Billing & Inventory
    Route::middleware('permission:billing')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos');
        Route::post('/sales', [PosController::class, 'createSale'])->name('sales.create');
        Route::post('/sales/{sale}/cancel', [PosController::class, 'cancelSale'])->name('sales.cancel');
        Route::post('/sales/{sale}/return', [PosController::class, 'returnSale'])->name('sales.return');
        Route::post('/bills/hold', [PosController::class, 'holdBill'])->name('bills.hold');
        Route::get('/bills/held', [PosController::class, 'heldBills'])->name('bills.held');
        Route::post('/bills/{heldBill}/resume', [PosController::class, 'resumeBill'])->name('bills.resume');
        Route::get('/items/stock', [PosController::class, 'checkStock'])->name('items.stock');
        Route::get('/invoice/{sale}/print', [PosController::class, 'printInvoice'])->name('invoice.print');
        Route::get('/shift/summary', [ShiftController::class, 'summary'])->name('shift.summary');

        Route::get('/items/search', [PosController::class, 'searchItems'])->name('items.search');
        Route::resource('items', ItemController::class);
        Route::get('/items/{item}/stock-history', [ItemController::class, 'stockHistory'])->name('items.stock-history');
        Route::get('/items/{item}/stock-history/api', [ItemController::class, 'stockHistoryApi'])->name('items.stock-history.api');
        Route::post('/items/{item}/stock-adjustment', [ItemController::class, 'stockAdjustment'])->name('items.stock-adjustment');

        Route::resource('grns', GrnController::class);
        Route::post('/grns/{grn}/receive', [GrnController::class, 'receive'])->name('grns.receive');
        
        Route::resource('stock-adjustments', \App\Http\Controllers\Admin\StockAdjustmentController::class)->except(['edit', 'update', 'destroy']);
        Route::get('/stock-adjustments/api', [\App\Http\Controllers\Admin\StockAdjustmentController::class, 'indexApi'])->name('stock-adjustments.api');
        
        Route::resource('stock-transfers', StockTransferController::class);
        Route::post('/stock-transfers/{stock_transfer}/approve', [StockTransferController::class, 'approve'])->name('stock-transfers.approve');
        Route::post('/stock-transfers/{stock_transfer}/reject', [StockTransferController::class, 'reject'])->name('stock-transfers.reject');
        
        Route::get('/sale-approvals', [\App\Http\Controllers\Admin\SaleApprovalController::class, 'index'])->name('sale-approvals.index');
        Route::post('/sale-approvals/{sale}/approve', [\App\Http\Controllers\Admin\SaleApprovalController::class, 'approve'])->name('sale-approvals.approve');
        Route::delete('/sale-approvals/{sale}/reject', [\App\Http\Controllers\Admin\SaleApprovalController::class, 'reject'])->name('sale-approvals.reject');
    });
    
    // Customers
    Route::middleware('permission:customers')->group(function () {
        Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);
        Route::get('/customers/api', [\App\Http\Controllers\Admin\CustomerController::class, 'indexApi'])->name('customers.api');
        Route::get('/customers/search', [PosController::class, 'searchCustomers'])->name('customers.search');
        Route::get('/customer-history', [CustomerHistoryController::class, 'index'])->name('customer-history.index');
        Route::post('/customer-history/search', [CustomerHistoryController::class, 'search'])->name('customer-history.search');
    });
    
    // Installments
    Route::middleware('permission:installments')->group(function () {
        Route::get('/installments', [App\Http\Controllers\Admin\InstallmentController::class, 'index'])->name('installments.index');
        Route::get('/installments/overdue', [InstallmentController::class, 'overdue'])->name('installments.overdue');
        Route::get('/installments/overdue/export', [InstallmentController::class, 'overdueExport'])->name('installments.overdue-export');
        Route::get('/installments/overdue/print', [InstallmentController::class, 'overduePrint'])->name('installments.overdue-print');
        Route::get('/installments/sms-reminder', [InstallmentController::class, 'smsReminder'])->name('installments.sms-reminder');
        Route::get('/installments/sms-reminder-print', [InstallmentController::class, 'printSmsReminders'])->name('installments.sms-reminder-print');
        Route::post('/installments/send-sms-reminder', [InstallmentController::class, 'sendSmsReminders'])->name('installments.send-sms-reminders');
        Route::post('/installments/add-comment', [InstallmentAgreementController::class, 'addComment'])->name('installments.add-comment');
        Route::delete('/installments/customer/{customer}', [App\Http\Controllers\Admin\InstallmentController::class, 'destroyCustomer'])->name('installments.customer.destroy');
        Route::get('/installments/{agreement}', [App\Http\Controllers\Admin\InstallmentController::class, 'show'])->name('installments.show');
        Route::delete('/installments/{agreement}', [App\Http\Controllers\Admin\InstallmentController::class, 'destroy'])->name('installments.destroy');
        Route::post('/installments/{agreement}/payments', [App\Http\Controllers\Admin\InstallmentController::class, 'storePayment'])->name('installments.payments.store');
        Route::post('/installments/{agreement}/delay-payments', [App\Http\Controllers\Admin\InstallmentController::class, 'storeDelayPayment'])->name('installments.delay-payments.store');
        Route::post('/installments/{agreement}/disconnect', [App\Http\Controllers\Admin\InstallmentController::class, 'disconnect'])->name('installments.disconnect');
        Route::post('/installments/{agreement}/unlock', [App\Http\Controllers\Admin\InstallmentController::class, 'unlock'])->name('installments.unlock');
        Route::post('/installments/{agreement}/update-call-log', [App\Http\Controllers\Admin\InstallmentController::class, 'updateCallLog'])->name('installments.update-call-log');
        Route::get('/installments/payments/{payment}/receipt', [App\Http\Controllers\Admin\InstallmentController::class, 'printReceipt'])->name('installments.payments.receipt');
        Route::delete('/installments/payments/{payment}', [App\Http\Controllers\Admin\InstallmentController::class, 'destroyPayment'])->name('installments.payments.destroy');
        Route::get('/installments/payments/{payment}/disconnect-receipt', [InstallmentController::class, 'printDisconnectReceipt'])->name('installments.payments.disconnect-receipt');
        Route::post('/installments/{agreement}/send-overdue-reminder', [App\Http\Controllers\Admin\InstallmentController::class, 'sendOverdueReminder'])->name('installments.send-overdue-reminder');
        Route::post('/installments/{agreement}/send-payment-completed-notification', [App\Http\Controllers\Admin\InstallmentController::class, 'sendPaymentCompletedNotification'])->name('installments.send-payment-completed-notification');
        Route::delete('/installments/delete-all', [App\Http\Controllers\Admin\InstallmentController::class, 'deleteAll'])->name('installments.delete-all');
    });
    
    // Agreements
    Route::middleware('permission:agreements')->group(function () {
        Route::get('/installment-agreement/{id}', [InstallmentAgreementController::class, 'show'])->name('installment-agreement.show');
        Route::get('/installment-agreement/{id}/download', [InstallmentAgreementController::class, 'download'])->name('installment-agreement.download');
        Route::post('/installment-agreement/preview', [InstallmentAgreementController::class, 'preview'])->name('installment-agreement.preview');
        Route::post('/installment-agreement/{id}/otp/send', [InstallmentAgreementController::class, 'sendOtp'])->name('installment-agreement.otp.send');
        Route::post('/installment-agreement/{id}/otp/verify', [InstallmentAgreementController::class, 'verifyOtp'])->name('installment-agreement.otp.verify');
        Route::get('/installment-agreement/{id}/print-agreement', [InstallmentAgreementController::class, 'printAgreement'])->name('installment-agreement.print');
        Route::get('/installment-agreement/{id}/application-form', [InstallmentAgreementController::class, 'printApplicationForm'])->name('installment-agreement.application-form');
        Route::get('/installment-agreement/{id}/edit', [InstallmentAgreementController::class, 'edit'])->name('installment-agreement.edit');
        Route::put('/installment-agreement/{id}', [InstallmentAgreementController::class, 'update'])->name('installment-agreement.update');
        Route::get('installment/{id}/guarantee-bond', [InstallmentAgreementController::class, 'printGuaranteeBond'])->name('installment.guarantee-bond');
    });

    // Payments & Credits
    Route::middleware('permission:payments')->group(function () {
        Route::get('/credits', [\App\Http\Controllers\Admin\CreditController::class, 'index'])->name('credits.index');
        Route::get('/credits/api', [\App\Http\Controllers\Admin\CreditController::class, 'indexApi'])->name('credits.api');
        Route::get('/credits/{credit}', [\App\Http\Controllers\Admin\CreditController::class, 'show'])->name('credits.show');
        Route::post('/credits/{credit}/payment', [\App\Http\Controllers\Admin\CreditController::class, 'recordPayment'])->name('credits.payment');
        Route::get('/customers/credits', [PosController::class, 'getCustomerCredits'])->name('customers.credits');
        
        Route::resource('expenses', ExpenseController::class);
        Route::resource('expense-categories', \App\Http\Controllers\Admin\ExpenseCategoryController::class)->except(['show'])->names([
            'index' => 'expense-categories.index',
            'create' => 'expense-categories.create',
            'store' => 'expense-categories.store',
            'edit' => 'expense-categories.edit',
            'update' => 'expense-categories.update',
            'destroy' => 'expense-categories.destroy',
        ]);
    });

    // Reports
    Route::middleware('permission:reports')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/sales/category-print', [ReportController::class, 'salesCategoryPrint'])->name('sales-category-print');
        Route::get('/sales/item-print', [ReportController::class, 'salesItemPrint'])->name('sales-item-print');
        Route::get('/warranty-jobs', [ReportController::class, 'warrantyJobs'])->name('warranty-jobs');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/installment-income', [ReportController::class, 'installmentIncome'])->name('installment-income');
        Route::get('/supplier-ledger', [ReportController::class, 'supplierLedger'])->name('supplier-ledger');
        Route::get('/supplier-ledger-api', [ReportController::class, 'supplierLedgerApi'])->name('supplier-ledger-api');
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/daily-installments', [ReportController::class, 'dailyInstallments'])->name('daily-installments');
        Route::get('/daily-installment-income', [ReportController::class, 'dailyInstallmentIncome'])->name('daily-installment-income');
        Route::get('/disconnect', [ReportController::class, 'disconnectReport'])->name('disconnect');
        Route::get('/sales-growth', [ReportController::class, 'salesGrowth'])->name('sales-growth');
        Route::get('/on-date-installments', [ReportController::class, 'onDateInstallments'])->name('on-date-installments');
        Route::get('/sales-tracking', [ReportController::class, 'salesTracking'])->name('sales-tracking');
        Route::get('/outstanding-installments', [ReportController::class, 'outstandingInstallments'])->name('outstanding-installments');
        Route::get('/outstanding-installments-print', [ReportController::class, 'outstandingInstallmentsPrint'])->name('outstanding-installments-print');
         Route::get('/paid-off-agreements', [ReportController::class, 'paidOffAgreements'])->name('paid-off-agreements');
        Route::get('/customer-behavior', [ReportController::class, 'customerBehavior'])->name('customer-behavior');
        Route::get('/cash-collection', [ReportController::class, 'cashCollection'])->name('cash-collection');
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
        Route::get('/stock-by-location', [ReportController::class, 'stockByLocation'])->name('stock-by-location');
        Route::get('/cash-collection-print', [ReportController::class, 'cashCollectionPrint'])->name('cash-collection-print');
        Route::get('/online-collection', [ReportController::class, 'onlineCollection'])->name('online-collection');
        Route::get('/delay-payments', [ReportController::class, 'delayPayments'])->name('delay-payments');
   
  Route::get('/estimate-collection', [ReportController::class, 'estimateCollectionReport'])->name('estimate-collection');
    Route::get('/estimate-collection/print', [ReportController::class, 'estimateCollectionReportPrint'])->name('estimate-collection-print');
        Route::get('/disconnect-print', [ReportController::class, 'disconnectReportPrint'])->name('disconnect-print');
        Route::get('/sales-growth-print', [ReportController::class, 'salesGrowthPrint'])->name('sales-growth-print');
        Route::get('/sales-tracking-print', [ReportController::class, 'salesTrackingPrint'])->name('sales-tracking-print');
        Route::get('/customer-behavior-print', [ReportController::class, 'customerBehaviorPrint'])->name('customer-behavior-print');
        Route::get('/online-collection-print', [ReportController::class, 'onlineCollectionPrint'])->name('online-collection-print');
    });

    Route::middleware('permission:reports')->group(function () {
          
  Route::get('/estimate-collection', [ReportController::class, 'estimateCollectionReport'])->name('estimate-collection');
    Route::get('/estimate-collection/print', [ReportController::class, 'estimateCollectionReportPrint'])->name('estimate-collection-print');
          Route::get('/estimate-report', [ReportController::class, 'estimateReport'])->name('estimate-report');
    Route::get('/estimate-report/print', [ReportController::class, 'estimateReportPrint'])->name('estimate-report-print');

 
   
     
    });
});

// Cashier Routes
Route::middleware(['auth', 'role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {
    // Dashboard
    Route::middleware('permission:dashboard')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Cashier\DashboardController::class, 'index'])->name('dashboard');
    });
    
    // Billing
    Route::middleware('permission:billing')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos');
        Route::get('/items/search', [PosController::class, 'searchItems'])->name('items.search');
        Route::post('/sales', [PosController::class, 'createSale'])->name('sales.create');
        Route::post('/sales/{sale}/cancel', [PosController::class, 'cancelSale'])->name('sales.cancel');
        Route::post('/sales/{sale}/return', [PosController::class, 'returnSale'])->name('sales.return');
        Route::post('/bills/hold', [PosController::class, 'holdBill'])->name('bills.hold');
        Route::get('/bills/held', [PosController::class, 'heldBills'])->name('bills.held');
        Route::post('/bills/{heldBill}/resume', [PosController::class, 'resumeBill'])->name('bills.resume');
        Route::get('/shift/summary', [ShiftController::class, 'summary'])->name('shift.summary');
        Route::get('/items/stock', [PosController::class, 'checkStock'])->name('items.stock');
        Route::get('/invoice/{sale}/print', [PosController::class, 'printInvoice'])->name('invoice.print');
        
        Route::get('/items', [\App\Http\Controllers\Admin\ItemController::class, 'index'])->name('items.index');
        Route::get('/items/{item}', [\App\Http\Controllers\Admin\ItemController::class, 'show'])->name('items.show');
        Route::get('/items/{item}/edit', [\App\Http\Controllers\Admin\ItemController::class, 'edit'])->name('items.edit');
        Route::put('/items/{item}', [\App\Http\Controllers\Admin\ItemController::class, 'update'])->name('items.update');
        Route::get('/items/{item}/stock-history', [\App\Http\Controllers\Admin\ItemController::class, 'stockHistory'])->name('items.stock-history');
        Route::get('/items/{item}/stock-history/api', [\App\Http\Controllers\Admin\ItemController::class, 'stockHistoryApi'])->name('items.stock-history.api');
        Route::post('/items/{item}/stock-adjustment', [\App\Http\Controllers\Admin\ItemController::class, 'stockAdjustment'])->name('items.stock-adjustment');

        Route::resource('grns', \App\Http\Controllers\Admin\GrnController::class);
        Route::post('/grns/{grn}/receive', [\App\Http\Controllers\Admin\GrnController::class, 'receive'])->name('grns.receive');
        
        Route::resource('stock-adjustments', \App\Http\Controllers\Admin\StockAdjustmentController::class)->except(['edit', 'update', 'destroy']);
        Route::get('/stock-adjustments/api', [\App\Http\Controllers\Admin\StockAdjustmentController::class, 'indexApi'])->name('stock-adjustments.api');
        
        Route::get('/sale-approvals', [\App\Http\Controllers\Admin\SaleApprovalController::class, 'index'])->name('sale-approvals.index');
        Route::post('/sale-approvals/{sale}/approve', [\App\Http\Controllers\Admin\SaleApprovalController::class, 'approve'])->name('sale-approvals.approve');
        Route::delete('/sale-approvals/{sale}/reject', [\App\Http\Controllers\Admin\SaleApprovalController::class, 'reject'])->name('sale-approvals.reject');
    });
    
    // Customers
    Route::middleware('permission:customers')->group(function () {
        Route::get('/customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [\App\Http\Controllers\Cashier\CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [\App\Http\Controllers\Cashier\CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}', [\App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [\App\Http\Controllers\Admin\CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [\App\Http\Controllers\Admin\CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [\App\Http\Controllers\Admin\CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::get('/customers/search', [PosController::class, 'searchCustomers'])->name('customers.search');
    });
    
    // Agreements
    Route::middleware('permission:agreements')->group(function () {
        Route::get('/installment-agreement/{id}', [InstallmentAgreementController::class, 'show'])->name('installment-agreement.show');
        Route::get('/installment-agreement/{id}/download', [InstallmentAgreementController::class, 'download'])->name('installment-agreement.download');
        Route::post('/installment-agreement/preview', [InstallmentAgreementController::class, 'preview'])->name('installment-agreement.preview');
        Route::get('/installment-agreement/{id}/edit', [InstallmentAgreementController::class, 'edit'])->name('installment-agreement.edit');
        Route::put('/installment-agreement/{id}', [InstallmentAgreementController::class, 'update'])->name('installment-agreement.update');
        Route::post('/installment-agreement/{id}/otp/send', [InstallmentAgreementController::class, 'sendOtp'])->name('installment-agreement.otp.send');
        Route::post('/installment-agreement/{id}/otp/verify', [InstallmentAgreementController::class, 'verifyOtp'])->name('installment-agreement.otp.verify');
        Route::get('/installment-agreement/{id}/print-agreement', [InstallmentAgreementController::class, 'printAgreement'])->name('installment-agreement.print');
        Route::get('installment/{id}/guarantee-bond', [InstallmentAgreementController::class, 'printGuaranteeBond'])->name('installment.guarantee-bond');
        Route::get('/installment-agreement/{id}/application-form', [InstallmentAgreementController::class, 'printApplicationForm'])->name('installment-agreement.application-form');
    });

    // Installments
    Route::middleware('permission:installments')->group(function () {
        Route::get('/installments', [App\Http\Controllers\Admin\InstallmentController::class, 'index'])->name('installments.index');
        Route::get('/installments/{agreement}', [App\Http\Controllers\Admin\InstallmentController::class, 'show'])->name('installments.show');
        Route::post('/installments/{agreement}/payments', [App\Http\Controllers\Admin\InstallmentController::class, 'storePayment'])->name('installments.payments.store');
    });
    
    // Payments
    Route::middleware('permission:payments')->group(function () {
        Route::get('/credits', [\App\Http\Controllers\Cashier\CreditController::class, 'index'])->name('credits.index');
        Route::get('/credits/api', [\App\Http\Controllers\Cashier\CreditController::class, 'indexApi'])->name('credits.api');
        Route::get('/credits/{customer}', [\App\Http\Controllers\Cashier\CreditController::class, 'show'])->name('credits.show');
        Route::post('/credits/{credit}/payment', [\App\Http\Controllers\Admin\CreditController::class, 'recordPayment'])->name('credits.payment');
        Route::get('/customers/credits', [PosController::class, 'getCustomerCredits'])->name('customers.credits');
        
        Route::resource('expenses', \App\Http\Controllers\Admin\ExpenseController::class);
        Route::resource('expense-categories', \App\Http\Controllers\Admin\ExpenseCategoryController::class)->except(['show'])->names([
            'index' => 'expense-categories.index',
            'create' => 'expense-categories.create',
            'store' => 'expense-categories.store',
            'edit' => 'expense-categories.edit',
            'update' => 'expense-categories.update',
            'destroy' => 'expense-categories.destroy',
        ]);
        
        Route::post('/suppliers/{supplier}/payments', [\App\Http\Controllers\Admin\SupplierController::class, 'recordPayment'])->name('suppliers.payments');
        Route::get('/suppliers/payments/{payment}/cheque-details', [\App\Http\Controllers\Admin\SupplierController::class, 'chequeDetails'])->name('suppliers.payments.cheque-details');
    });
    
    // Settings
    Route::middleware('permission:settings')->group(function () {
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class);
    });
    
    // Reports
    Route::middleware('permission:reports')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/supplier-ledger', [ReportController::class, 'supplierLedger'])->name('supplier-ledger');
        Route::get('/supplier-ledger-api', [ReportController::class, 'supplierLedgerApi'])->name('supplier-ledger-api');
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
        Route::get('/daily-installments', [ReportController::class, 'dailyInstallments'])->name('daily-installments');
        Route::get('/daily-installment-income', [ReportController::class, 'dailyInstallmentIncome'])->name('daily-installment-income');
    });

    // Audit Logs
    Route::middleware('permission:audit_logs')->group(function () {
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])->name('audit-logs.show');
    });
});
