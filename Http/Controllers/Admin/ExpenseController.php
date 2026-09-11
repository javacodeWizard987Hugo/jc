<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with(['category', 'creator'])->latest()->paginate(20);
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.expenses.index', compact('expenses', 'routePrefix'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->get();
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.expenses.create', compact('categories', 'routePrefix'));
    }

    public function store(Request $request)
    {
        $rules = [
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank,cheque,credit,other',
            'expense_date' => 'required|date',
            'is_recurring' => 'boolean',
            'recurring_days' => 'nullable|integer|min:1',
        ];

        // Add conditional validation based on payment method
        if ($request->payment_method === 'credit') {
            $rules['credit_repay_date'] = 'required|date|after_or_equal:today';
        } elseif ($request->payment_method === 'cheque') {
            $rules['cheque_number'] = 'required|string|max:255';
            $rules['cheque_bank'] = 'required|string|max:255';
            $rules['cheque_date'] = 'required|date';
            $rules['cheque_repay_date'] = 'required|date|after_or_equal:today';
            $rules['account_holder_name'] = 'required|string|max:255';
            $rules['account_number'] = 'required|string|max:255';
            $rules['account_bank'] = 'required|string|max:255';
            $rules['account_branch'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        $validated['created_by'] = auth()->id();
        
        if ($request->filled('is_recurring') && $request->filled('recurring_days')) {
            $validated['next_due_date'] = now()->addDays($request->recurring_days);
        }

        $expense = Expense::create($validated);
        
        AuditLog::log('expense_created', "Expense created: {$expense->description} - Rs. {$expense->amount}", $expense);

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    public function show(Expense $expense)
    {
        $expense->load(['category', 'creator']);
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.expenses.show', compact('expense', 'routePrefix'));
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::where('is_active', true)->get();
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.expenses.edit', compact('expense', 'categories', 'routePrefix'));
    }

    public function update(Request $request, Expense $expense)
    {
        $rules = [
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank,cheque,credit,other',
            'expense_date' => 'required|date',
            'is_recurring' => 'boolean',
            'recurring_days' => 'nullable|integer|min:1',
        ];

        // Add conditional validation based on payment method
        if ($request->payment_method === 'credit') {
            $rules['credit_repay_date'] = 'required|date|after_or_equal:today';
        } elseif ($request->payment_method === 'cheque') {
            $rules['cheque_number'] = 'required|string|max:255';
            $rules['cheque_bank'] = 'required|string|max:255';
            $rules['cheque_date'] = 'required|date';
            $rules['cheque_repay_date'] = 'required|date|after_or_equal:today';
            $rules['account_holder_name'] = 'required|string|max:255';
            $rules['account_number'] = 'required|string|max:255';
            $rules['account_bank'] = 'required|string|max:255';
            $rules['account_branch'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        if ($request->filled('is_recurring') && $request->filled('recurring_days')) {
            $validated['next_due_date'] = now()->addDays($request->recurring_days);
        } else {
            $validated['next_due_date'] = null;
        }

        // Clear fields not relevant to current payment method
        if ($request->payment_method !== 'credit') {
            $validated['credit_repay_date'] = null;
        }
        if ($request->payment_method !== 'cheque') {
            $validated['cheque_number'] = null;
            $validated['cheque_bank'] = null;
            $validated['cheque_date'] = null;
            $validated['cheque_repay_date'] = null;
            $validated['account_holder_name'] = null;
            $validated['account_number'] = null;
            $validated['account_bank'] = null;
            $validated['account_branch'] = null;
        }

        $oldValues = $expense->toArray();
        $expense->update($validated);
        
        AuditLog::log('expense_updated', "Expense updated: {$expense->description}", $expense, $oldValues, $expense->toArray());

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $description = $expense->description;
        $expense->delete();
        
        AuditLog::log('expense_deleted', "Expense deleted: {$description}", null);

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
