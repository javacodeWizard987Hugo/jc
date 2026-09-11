<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withSum('supplierPayments', 'outstanding_amount')->latest()->paginate(15);
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.suppliers.index', compact('suppliers', 'routePrefix'));
    }

    public function create()
    {
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.suppliers.create', compact('routePrefix'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'payment_terms' => 'nullable|string',
        ]);

        $supplier = Supplier::create($validated);
        
        AuditLog::log('supplier_created', "Supplier '{$supplier->name}' created", $supplier);

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['grns', 'supplierPayments']);
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.suppliers.show', compact('supplier', 'routePrefix'));
    }

    public function edit(Supplier $supplier)
    {
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.suppliers.edit', compact('supplier', 'routePrefix'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'payment_terms' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldValues = $supplier->toArray();
        $supplier->update($validated);
        
        AuditLog::log('supplier_updated', "Supplier '{$supplier->name}' updated", $supplier, $oldValues, $supplier->toArray());

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        if ($supplier->grns()->count() > 0) {
            $supplier->update(['is_active' => false]);
            return redirect()->route($routePrefix . '.suppliers.index')
                ->with('success', 'Supplier deactivated (cannot delete suppliers with GRNs).');
        }

        $supplierName = $supplier->name;
        $supplier->delete();
        
        AuditLog::log('supplier_deleted', "Supplier '{$supplierName}' deleted", null);

        return redirect()->route($routePrefix . '.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    public function recordPayment(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'grn_id' => 'nullable|exists:grns,id',
            'invoice_number' => 'nullable|string',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'invoice_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,credit,other',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'credit_repay_date' => 'nullable|date|after_or_equal:today',
            'cheque_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'cheque_date' => 'nullable|date',
            'cheque_repay_date' => 'nullable|date|after_or_equal:today',
            'account_holder_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'account_bank' => 'nullable|string',
            'account_branch' => 'nullable|string',
        ]);

        // Add conditional validation based on payment method
        if ($request->payment_method === 'credit') {
            $request->validate([
                'credit_repay_date' => 'required|date|after_or_equal:today',
                'account_holder_name' => 'required|string',
                'account_number' => 'required|string',
                'account_bank' => 'required|string',
                'account_branch' => 'required|string',
            ]);
        } elseif ($request->payment_method === 'cheque') {
            $request->validate([
                'cheque_number' => 'required|string',
                'bank_name' => 'required|string',
                'cheque_date' => 'required|date',
                'cheque_repay_date' => 'required|date|after_or_equal:today',
                'account_holder_name' => 'required|string',
                'account_number' => 'required|string',
                'account_bank' => 'required|string',
                'account_branch' => 'required|string',
            ]);
        } elseif ($request->payment_method === 'bank_transfer') {
            $request->validate([
                'account_holder_name' => 'required|string',
                'account_number' => 'required|string',
                'account_bank' => 'required|string',
                'account_branch' => 'required|string',
            ]);
        }

        $outstanding = $validated['invoice_amount'] - $validated['paid_amount'];
        
        $paymentData = [
            'supplier_id' => $supplier->id,
            'grn_id' => $validated['grn_id'] ?? null,
            'invoice_number' => $validated['invoice_number'] ?? null,
            'invoice_date' => $validated['invoice_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'invoice_amount' => $validated['invoice_amount'],
            'paid_amount' => $validated['paid_amount'],
            'outstanding_amount' => $outstanding,
            'payment_method' => $validated['payment_method'],
            'payment_date' => $validated['payment_date'] ?? now(),
            'paid_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ];

        // Add credit repay date if payment method is credit
        if ($request->payment_method === 'credit') {
            $paymentData['credit_repay_date'] = $validated['credit_repay_date'] ?? null;
        } else {
            $paymentData['credit_repay_date'] = null;
        }

        // Add cheque details if payment method is cheque
        if ($request->payment_method === 'cheque') {
            $paymentData['cheque_number'] = $validated['cheque_number'] ?? null;
            $paymentData['bank_name'] = $validated['bank_name'] ?? null;
            $paymentData['cheque_date'] = $validated['cheque_date'] ?? null;
            $paymentData['cheque_repay_date'] = $validated['cheque_repay_date'] ?? null;
        } else {
            $paymentData['cheque_number'] = null;
            $paymentData['bank_name'] = null;
            $paymentData['cheque_date'] = null;
            $paymentData['cheque_repay_date'] = null;
        }

        // Account details can be saved for all payment methods (cheque, credit, bank_transfer)
        $paymentData['account_holder_name'] = $validated['account_holder_name'] ?? null;
        $paymentData['account_number'] = $validated['account_number'] ?? null;
        $paymentData['account_bank'] = $validated['account_bank'] ?? null;
        $paymentData['account_branch'] = $validated['account_branch'] ?? null;
        
        $payment = SupplierPayment::create($paymentData);

        // Update supplier outstanding balance
        $supplier->increment('outstanding_balance', $outstanding);
        
        AuditLog::log('supplier_payment', "Payment recorded for supplier '{$supplier->name}': Rs. {$validated['paid_amount']}", $payment);

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function chequeDetails(SupplierPayment $payment)
    {
        if ($payment->payment_method !== 'cheque') {
            return response()->json(['error' => 'Not a cheque payment'], 400);
        }

        return response()->json([
            'bank_name' => $payment->bank_name,
            'cheque_number' => $payment->cheque_number,
            'amount' => number_format($payment->paid_amount, 2),
            'cheque_date' => $payment->cheque_date ? $payment->cheque_date->format('Y-m-d') : null,
            'cheque_repay_date' => $payment->cheque_repay_date ? $payment->cheque_repay_date->format('Y-m-d') : null,
            'account_holder_name' => $payment->account_holder_name,
            'account_number' => $payment->account_number,
            'account_bank' => $payment->account_bank,
            'account_branch' => $payment->account_branch,
        ]);
    }
}
