<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::withCount('expenses')->latest()->paginate(15);
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.expense-categories.index', compact('categories', 'routePrefix'));
    }

    public function create()
    {
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.expense-categories.create', compact('routePrefix'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = ExpenseCategory::create($validated);
        
        AuditLog::log('expense_category_created', "Expense category '{$category->name}' created", $category);

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.expense-categories.index')
            ->with('success', 'Expense category created successfully.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.expense-categories.edit', compact('expenseCategory', 'routePrefix'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldValues = $expenseCategory->toArray();
        $expenseCategory->update($validated);
        
        AuditLog::log('expense_category_updated', "Expense category '{$expenseCategory->name}' updated", $expenseCategory, $oldValues, $expenseCategory->toArray());

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.expense-categories.index')
            ->with('success', 'Expense category updated successfully.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete category with existing expenses. Please deactivate it instead.']);
        }

        $categoryName = $expenseCategory->name;
        $expenseCategory->delete();
        
        AuditLog::log('expense_category_deleted', "Expense category '{$categoryName}' deleted", null);

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.expense-categories.index')
            ->with('success', 'Expense category deleted successfully.');
    }
}
