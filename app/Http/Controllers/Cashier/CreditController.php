<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CustomerCredit;
use App\Models\Customer;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerCredit::with(['customer', 'sale']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter overdue
        if ($request->filled('overdue')) {
            if ($request->overdue === 'yes') {
                $query->where('due_date', '<', now())
                      ->where('outstanding_amount', '>', 0);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $credits = $query->latest()->paginate(20);
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('cashier.credits.index', compact('credits', 'customers'));
    }

    public function show(CustomerCredit $credit)
    {
        $credit->load(['customer', 'sale.items.item']);
        return view('cashier.credits.show', compact('credit'));
    }

    public function indexApi(Request $request)
    {
        $query = CustomerCredit::with(['customer', 'sale']);

        // Apply same filters as index method
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('overdue')) {
            if ($request->overdue === 'yes') {
                $query->where('due_date', '<', now())
                      ->where('outstanding_amount', '>', 0);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $credits = $query->latest()->limit(20)->get();

        return response()->json([
            'credits' => $credits->map(function($credit) {
                $isOverdue = $credit->due_date < now() && $credit->outstanding_amount > 0;
                return [
                    'id' => $credit->id,
                    'customer_name' => $credit->customer->name,
                    'invoice_number' => $credit->sale->invoice_number ?? 'N/A',
                    'amount' => (float) $credit->amount,
                    'paid_amount' => (float) $credit->paid_amount,
                    'outstanding_amount' => (float) $credit->outstanding_amount,
                    'due_date' => $credit->due_date->format('Y-m-d'),
                    'due_date_timestamp' => $credit->due_date->timestamp,
                    'status' => $credit->status,
                    'is_overdue' => $isOverdue,
                    'updated_at' => $credit->updated_at->timestamp,
                ];
            }),
        ]);
    }
}

