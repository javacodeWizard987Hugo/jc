<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function summary()
    {
        $today = now();
        
        // Admin can see all sales, cashier only sees their own
        $salesQuery = Sale::whereDate('created_at', $today)
            ->where('status', 'completed');
        
        if (auth()->user()->isCashier()) {
            $salesQuery->where('cashier_id', auth()->id());
        }
        
        $sales = $salesQuery->get();

        $summary = [
            'total_sales' => $sales->sum('total_amount'),
            'total_bills' => $sales->count(),
            'total_items' => $sales->sum(function($sale) {
                return $sale->items->sum('quantity');
            }),
            'by_payment_method' => $sales->groupBy('payment_method')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('total_amount'),
                ];
            }),
        ];

        return view('cashier.shift-summary', compact('summary', 'today'));
    }
}
