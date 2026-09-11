<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\SystemSetting;
use App\Models\CustomerCredit;
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
        
        // Upcoming recurring expenses
        $upcomingExpenses = Expense::where('is_recurring', true)
            ->where('next_due_date', '>=', now())
            ->where('next_due_date', '<=', now()->addDays(7))
            ->with('category')
            ->get();
        
        // Today's sales summary (cashier's own sales only)
        $todaySales = Sale::whereDate('created_at', today())
            ->where('status', 'completed')
            ->where('cashier_id', auth()->id())
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
        
        // Low stock items
       
        $branchId = auth()->user()->branch_id; // cashier's branch

            $lowStockItems = Item::where('is_active', true)
                ->whereHas('stock', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                    ->whereColumn('quantity', '<=', 'reorder_level');
                })
                ->get();

        // Notifications for bills and customer credits
        $notifications = collect([]);
        
        // Today's bills notifications (cashier's own bills)
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
        
        // Sort notifications by date (overdue first, then upcoming)
        $notifications = $notifications->sortBy(function($notification) {
            $date = Carbon::parse($notification['date']);
            if ($date < now()) {
                return $date->timestamp; // Overdue items first
            }
            return $date->timestamp + 1000000000; // Upcoming items after
        });
        
        // Widget settings
        $widgetSettings = [
            'show_expired_items' => SystemSetting::getValue('dashboard_show_expired_items', true),
            'show_low_stock' => SystemSetting::getValue('dashboard_show_low_stock', true),
            'show_expenses' => SystemSetting::getValue('dashboard_show_expenses', true),
        ];
        
        return view('cashier.dashboard', compact(
            'todayTotal',
            'todayBills',
            'expiredItems',
            'nearExpiryItems',
            'lowStockItems',
            'upcomingExpenses',
            'upcomingCredits',
            'overdueCredits',
            'paymentBreakdown',
            'widgetSettings',
            'notifications'
        ));
    }
}
