<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerHistoryController extends Controller
{
    public function index()
    {
        return view('admin.customer_history.index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|max:20',
        ]);

        $keyword = trim($request->keyword);

        $customer = Customer::where('nic', $keyword)
            ->orWhere('phone', $keyword)
            ->first();

        if (!$customer) {
            return back()->with('error', 'Customer not found');
        }

        $agreements = $customer->installmentAgreements()
            ->with(['sale.items.item', 'payments'])
            ->latest()
               ->paginate(10); // ✅ paginator

        return view('admin.customer_history.show', compact('customer', 'agreements'));
    }
}
