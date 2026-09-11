<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SupplierPayment;
use App\Models\Expense;
use App\Models\SystemSetting;
use App\Models\CustomerCredit;
use App\Models\User;
use App\Models\InstallmentAgreement;
use App\Models\InstallmentPayment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $expiryDays = SystemSetting::getValue('expiry_alert_days', 30);
        
        // Items nearing expiry
        $nearExpiryItems = Item::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays($expiryDays))
            ->where('is_active', true)
            ->get();
        
        // Expired items
        $expiredItems = Item::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->where('is_active', true)
            ->get();
        
        // Upcoming supplier payments (next 7 days)
        $upcomingPayments = SupplierPayment::where('outstanding_amount', '>', 0)
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->with('supplier')
            ->get();
        
        // Overdue supplier payments
        $overduePayments = SupplierPayment::where('outstanding_amount', '>', 0)
            ->where('due_date', '<', now())
            ->with('supplier')
            ->get();
        
        // Upcoming recurring expenses
        $upcomingExpenses = Expense::where('is_recurring', true)
            ->where('next_due_date', '>=', now())
            ->where('next_due_date', '<=', now()->addDays(7))
            ->with('category')
            ->get();
        
        // Today's sales summary
        $todaySales = Sale::whereDate('created_at', today())
            ->where('status', 'completed')
            ->get();
        
        $todayTotal = $todaySales->sum('total_amount');
        $todayBills = $todaySales->count();
        
        // Sales breakdown by payment method
        $paymentBreakdown = $todaySales->groupBy('payment_method')
            ->map(function ($sales) {
                return [
                    'amount' => $sales->sum('total_amount'),
                    'count' => $sales->count(),
                ];
            });
        
        // Sales breakdown by cashier
        $cashierBreakdown = $todaySales->groupBy('cashier_id')
            ->map(function ($sales) {
                $cashier = User::find($sales->first()->cashier_id);
                return [
                    'name' => $cashier ? $cashier->name : 'Unknown',
                    'amount' => $sales->sum('total_amount'),
                    'count' => $sales->count(),
                ];
            });
        
        // Customer credit dates (upcoming and overdue)
        $upcomingCredits = collect([]);
        $overdueCredits = collect([]);
        
        try {
            $upcomingCredits = CustomerCredit::where('outstanding_amount', '>', 0)
                ->where('due_date', '>=', now())
                ->where('due_date', '<=', now()->addDays(7))
                ->with('customer', 'sale')
                ->get();
            
            $overdueCredits = CustomerCredit::where('outstanding_amount', '>', 0)
                ->where('due_date', '<', now())
                ->with('customer', 'sale')
                ->get();
        } catch (\Exception $e) {
            // Table might not exist yet, use empty collection
        }
        
        // Supplier cheque dates (upcoming and overdue)
        $upcomingCheques = collect([]);
        $overdueCheques = collect([]);
        $upcomingCustomerCheques = collect([]);
        $overdueCustomerCheques = collect([]);
        
        try {
            $upcomingCheques = SupplierPayment::where('payment_method', 'cheque')
                ->whereNotNull('cheque_date')
                ->where('cheque_date', '>=', now())
                ->where('cheque_date', '<=', now()->addDays(7))
                ->where('outstanding_amount', '>', 0)
                ->with('supplier')
                ->get();
            
            $overdueCheques = SupplierPayment::where('payment_method', 'cheque')
                ->whereNotNull('cheque_date')
                ->where('cheque_date', '<', now())
                ->where('outstanding_amount', '>', 0)
                ->with('supplier')
                ->get();
            
            // Customer cheque dates (upcoming and overdue)
            $upcomingCustomerCheques = \App\Models\Payment::where('payment_method', 'cheque')
                ->whereNotNull('cheque_date')
                ->where('cheque_date', '>=', now())
                ->where('cheque_date', '<=', now()->addDays(7))
                ->with('sale.customer')
                ->get();
            
            $overdueCustomerCheques = \App\Models\Payment::where('payment_method', 'cheque')
                ->whereNotNull('cheque_date')
                ->where('cheque_date', '<', now())
                ->with('sale.customer')
                ->get();
        } catch (\Exception $e) {
            // Tables might not have cheque_date column yet, use empty collections
        }
        
        // Low stock items
        $lowStockItems = Item::where('is_active', true)
            ->whereHas('stock', function ($query) {
                $query->whereColumn('quantity', '<=', 'items.reorder_level');
            })
            ->with('stock.branch')
            ->get();
        
        // Calculate profit for today (if widget enabled)
        $todayProfit = 0;
        $todayCOGS = 0;
        if (SystemSetting::getValue('dashboard_show_profit', true)) {
            foreach ($todaySales as $sale) {
                foreach ($sale->items as $saleItem) {
                    $todayCOGS += $saleItem->quantity * $saleItem->item->cost_price;
                }
            }
            $todayExpenses = Expense::whereDate('expense_date', today())->sum('amount');
            $todayProfit = $todayTotal - $todayCOGS - $todayExpenses;
        }
        
        // Notifications for bills, expenses, supplier payments, and customer credits
        $notifications = collect([]);
        
        // Today's bills notifications
        if ($todayBills > 0) {
            $notifications->push([
                'type' => 'bill',
                'icon' => '📄',
                'title' => 'Today\'s Bills',
                'message' => "{$todayBills} bill(s) generated today with total amount of Rs. " . number_format($todayTotal, 2),
                'date' => today()->format('Y-m-d'),
                'color' => 'blue',
            ]);
        }
        
        // Upcoming expenses (next 7 days)
        foreach ($upcomingExpenses as $expense) {
            $notifications->push([
                'type' => 'expense',
                'icon' => '💰',
                'title' => 'Upcoming Expense',
                'message' => "{$expense->description} - Rs. " . number_format($expense->amount, 2) . " due on " . $expense->next_due_date->format('Y-m-d'),
                'date' => $expense->next_due_date->format('Y-m-d'),
                'color' => 'yellow',
            ]);
        }
        
        // Overdue supplier payments
        foreach ($overduePayments as $payment) {
            $notifications->push([
                'type' => 'supplier_payment',
                'icon' => '🏭',
                'title' => 'Overdue Supplier Payment',
                'message' => "{$payment->supplier->name} - Rs. " . number_format($payment->outstanding_amount, 2) . " overdue since " . $payment->due_date->format('Y-m-d'),
                'date' => $payment->due_date->format('Y-m-d'),
                'color' => 'red',
            ]);
        }
        
        // Upcoming supplier payments (next 7 days)
        foreach ($upcomingPayments as $payment) {
            $notifications->push([
                'type' => 'supplier_payment',
                'icon' => '🏭',
                'title' => 'Upcoming Supplier Payment',
                'message' => "{$payment->supplier->name} - Rs. " . number_format($payment->outstanding_amount, 2) . " due on " . $payment->due_date->format('Y-m-d'),
                'date' => $payment->due_date->format('Y-m-d'),
                'color' => 'orange',
            ]);
        }
        
        // Overdue customer credits
        foreach ($overdueCredits as $credit) {
            $notifications->push([
                'type' => 'customer_credit',
                'icon' => '👤',
                'title' => 'Overdue Customer Credit',
                'message' => ($credit->customer ? $credit->customer->name : 'Unknown') . " - Rs. " . number_format($credit->outstanding_amount, 2) . " overdue since " . $credit->due_date->format('Y-m-d'),
                'date' => $credit->due_date->format('Y-m-d'),
                'color' => 'red',
            ]);
        }
        
        // Upcoming customer credits (next 7 days)
        foreach ($upcomingCredits as $credit) {
            $notifications->push([
                'type' => 'customer_credit',
                'icon' => '👤',
                'title' => 'Upcoming Customer Credit',
                'message' => ($credit->customer ? $credit->customer->name : 'Unknown') . " - Rs. " . number_format($credit->outstanding_amount, 2) . " due on " . $credit->due_date->format('Y-m-d'),
                'date' => $credit->due_date->format('Y-m-d'),
                'color' => 'orange',
            ]);
        }
        
        // Supplier credit repay dates (2 days before and overdue)
        try {
            $twoDaysFromNow = now()->addDays(2);
            $upcomingSupplierCredits = SupplierPayment::where('payment_method', 'credit')
                ->whereNotNull('credit_repay_date')
                ->where('credit_repay_date', '>=', now())
                ->where('credit_repay_date', '<=', $twoDaysFromNow)
                ->where('outstanding_amount', '>', 0)
                ->with('supplier')
                ->get();
            
            $overdueSupplierCredits = SupplierPayment::where('payment_method', 'credit')
                ->whereNotNull('credit_repay_date')
                ->where('credit_repay_date', '<', now())
                ->where('outstanding_amount', '>', 0)
                ->with('supplier')
                ->get();
            
            foreach ($overdueSupplierCredits as $payment) {
                $notifications->push([
                    'type' => 'supplier_credit',
                    'icon' => '🏭',
                    'title' => 'Overdue Supplier Credit Repay',
                    'message' => "{$payment->supplier->name} - Rs. " . number_format($payment->outstanding_amount, 2) . " credit repay overdue since " . $payment->credit_repay_date->format('Y-m-d'),
                    'date' => $payment->credit_repay_date->format('Y-m-d'),
                    'color' => 'red',
                ]);
            }
            
            foreach ($upcomingSupplierCredits as $payment) {
                $daysUntil = now()->diffInDays($payment->credit_repay_date);
                $notifications->push([
                    'type' => 'supplier_credit',
                    'icon' => '🏭',
                    'title' => 'Upcoming Supplier Credit Repay',
                    'message' => "{$payment->supplier->name} - Rs. " . number_format($payment->outstanding_amount, 2) . " credit repay due in {$daysUntil} day(s) on " . $payment->credit_repay_date->format('Y-m-d'),
                    'date' => $payment->credit_repay_date->format('Y-m-d'),
                    'color' => 'orange',
                ]);
            }
        } catch (\Exception $e) {
            // Column might not exist yet
        }
        
        // Supplier cheque repay dates (2 days before and overdue)
        try {
            $twoDaysFromNow = now()->addDays(2);
            $upcomingSupplierChequeRepays = SupplierPayment::where('payment_method', 'cheque')
                ->whereNotNull('cheque_repay_date')
                ->where('cheque_repay_date', '>=', now())
                ->where('cheque_repay_date', '<=', $twoDaysFromNow)
                ->where('outstanding_amount', '>', 0)
                ->with('supplier')
                ->get();
            
            $overdueSupplierChequeRepays = SupplierPayment::where('payment_method', 'cheque')
                ->whereNotNull('cheque_repay_date')
                ->where('cheque_repay_date', '<', now())
                ->where('outstanding_amount', '>', 0)
                ->with('supplier')
                ->get();
            
            foreach ($overdueSupplierChequeRepays as $payment) {
                $bankName = $payment->bank_name ?? 'N/A';
                $chequeNumber = $payment->cheque_number ?? 'N/A';
                $amount = number_format($payment->outstanding_amount, 2);
                $supplierId = $payment->supplier_id;
                $notifications->push([
                    'type' => 'supplier_cheque',
                    'icon' => '📝',
                    'title' => 'Overdue Supplier Cheque Repay',
                    'message' => "{$payment->supplier->name} - Bank: {$bankName}, Cheque #: {$chequeNumber}, Amount: Rs. {$amount} - Repay overdue since " . $payment->cheque_repay_date->format('Y-m-d'),
                    'date' => $payment->cheque_repay_date->format('Y-m-d'),
                    'color' => 'red',
                    'link' => route('admin.reports.supplier-ledger', ['supplier_id' => $supplierId]),
                ]);
            }
            
            foreach ($upcomingSupplierChequeRepays as $payment) {
                $daysUntil = now()->diffInDays($payment->cheque_repay_date);
                $bankName = $payment->bank_name ?? 'N/A';
                $chequeNumber = $payment->cheque_number ?? 'N/A';
                $amount = number_format($payment->outstanding_amount, 2);
                $supplierId = $payment->supplier_id;
                $notifications->push([
                    'type' => 'supplier_cheque',
                    'icon' => '📝',
                    'title' => 'Upcoming Supplier Cheque Repay',
                    'message' => "{$payment->supplier->name} - Bank: {$bankName}, Cheque #: {$chequeNumber}, Amount: Rs. {$amount} - Repay due in {$daysUntil} day(s) on " . $payment->cheque_repay_date->format('Y-m-d'),
                    'date' => $payment->cheque_repay_date->format('Y-m-d'),
                    'color' => 'orange',
                    'link' => route('admin.reports.supplier-ledger', ['supplier_id' => $supplierId]),
                ]);
            }
        } catch (\Exception $e) {
            // Column might not exist yet
        }
        
        // Expense credit repay dates (upcoming and overdue)
        try {
            $upcomingExpenseCredits = Expense::where('payment_method', 'credit')
                ->whereNotNull('credit_repay_date')
                ->where('credit_repay_date', '>=', now())
                ->where('credit_repay_date', '<=', now()->addDays(7))
                ->with('category')
                ->get();
            
            $overdueExpenseCredits = Expense::where('payment_method', 'credit')
                ->whereNotNull('credit_repay_date')
                ->where('credit_repay_date', '<', now())
                ->with('category')
                ->get();
            
            foreach ($overdueExpenseCredits as $expense) {
                $notifications->push([
                    'type' => 'expense_credit',
                    'icon' => '💰',
                    'title' => 'Overdue Expense Credit',
                    'message' => "{$expense->description} - Rs. " . number_format($expense->amount, 2) . " credit repay overdue since " . $expense->credit_repay_date->format('Y-m-d'),
                    'date' => $expense->credit_repay_date->format('Y-m-d'),
                    'color' => 'red',
                ]);
            }
            
            foreach ($upcomingExpenseCredits as $expense) {
                $notifications->push([
                    'type' => 'expense_credit',
                    'icon' => '💰',
                    'title' => 'Upcoming Expense Credit Repay',
                    'message' => "{$expense->description} - Rs. " . number_format($expense->amount, 2) . " credit repay due on " . $expense->credit_repay_date->format('Y-m-d'),
                    'date' => $expense->credit_repay_date->format('Y-m-d'),
                    'color' => 'orange',
                ]);
            }
        } catch (\Exception $e) {
            // Column might not exist yet
        }
        
        // Expense cheque repay dates (upcoming and overdue)
        try {
            $upcomingExpenseChequeRepays = Expense::where('payment_method', 'cheque')
                ->whereNotNull('cheque_repay_date')
                ->where('cheque_repay_date', '>=', now())
                ->where('cheque_repay_date', '<=', now()->addDays(7))
                ->with('category')
                ->get();
            
            $overdueExpenseChequeRepays = Expense::where('payment_method', 'cheque')
                ->whereNotNull('cheque_repay_date')
                ->where('cheque_repay_date', '<', now())
                ->with('category')
                ->get();
            
            foreach ($overdueExpenseChequeRepays as $expense) {
                $notifications->push([
                    'type' => 'expense_cheque',
                    'icon' => '📝',
                    'title' => 'Overdue Expense Cheque Repay',
                    'message' => "{$expense->description} - Cheque #{$expense->cheque_number} repay overdue since " . $expense->cheque_repay_date->format('Y-m-d'),
                    'date' => $expense->cheque_repay_date->format('Y-m-d'),
                    'color' => 'red',
                ]);
            }
            
            foreach ($upcomingExpenseChequeRepays as $expense) {
                $notifications->push([
                    'type' => 'expense_cheque',
                    'icon' => '📝',
                    'title' => 'Upcoming Expense Cheque Repay',
                    'message' => "{$expense->description} - Cheque #{$expense->cheque_number} repay due on " . $expense->cheque_repay_date->format('Y-m-d'),
                    'date' => $expense->cheque_repay_date->format('Y-m-d'),
                    'color' => 'orange',
                ]);
            }
        } catch (\Exception $e) {
            // Column might not exist yet
        }
        
        // Sort notifications by date (overdue first, then upcoming)
        $notifications = $notifications->sortBy(function($notification) {
            $date = Carbon::parse($notification['date']);
            if ($date < now()) {
                return $date->timestamp; // Overdue items first
            }
            return $date->timestamp + 1000000000; // Upcoming items after
        });
        
        // Dashboard widget visibility settings
        $widgetSettings = [
            'show_profit' => SystemSetting::getValue('dashboard_show_profit', true),
            'show_expenses' => SystemSetting::getValue('dashboard_show_expenses', true),
            'show_supplier_payments' => SystemSetting::getValue('dashboard_show_supplier_payments', true),
            'show_expired_items' => SystemSetting::getValue('dashboard_show_expired_items', true),
            'show_low_stock' => SystemSetting::getValue('dashboard_show_low_stock', true),
        ];

       $activeInstallmentAgreements = InstallmentAgreement::where('status', '!=', 'paid_off')->count();
        $totalOutstandingInstallmentBalance = InstallmentAgreement::where('status', '!=', 'paid_off')->sum('balance_amount');
        $overdueInstallmentCustomers = InstallmentAgreement::where('status', 'overdue')->count();
        $overdueInstallmentAmount = InstallmentAgreement::where('status', 'overdue')->sum('balance_amount');
       $upcomingInstallmentPayments = InstallmentAgreement::where('status', '!=', 'paid_off')
            ->where('balance_amount', '>', 0)
            ->get()
            ->filter(function ($agreement) {

                $nextDueDate = $agreement->next_due_date;
                if (!$nextDueDate instanceof Carbon) {
                    if ($nextDueDate === 'Completed' || !$nextDueDate) return false;
                    $nextDueDate = Carbon::parse($nextDueDate);
                }

                return $nextDueDate->between(now(), now()->addDays(7));
            })
            ->sum('monthly_installment_amount');

        
        return view('admin.dashboard', compact(
            'nearExpiryItems',
            'expiredItems',
            'upcomingPayments',
            'overduePayments',
            'upcomingExpenses',
            'todayTotal',
            'todayBills',
            'todayProfit',
            'todayCOGS',
            'paymentBreakdown',
            'cashierBreakdown',
            'upcomingCredits',
            'overdueCredits',
            'upcomingCheques',
            'overdueCheques',
            'upcomingCustomerCheques',
            'overdueCustomerCheques',
            'lowStockItems',
            'widgetSettings',
            'notifications',
            'activeInstallmentAgreements',
            'totalOutstandingInstallmentBalance',
            'overdueInstallmentCustomers',
            'overdueInstallmentAmount',
            'upcomingInstallmentPayments'
        ));
    }
}
