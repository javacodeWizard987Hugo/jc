<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }
        
        // Filter by active/inactive
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        $customers = $query->latest()->paginate(20);
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.customers.index', compact('customers', 'routePrefix'));
    }

    public function create()
    {
        $branches = \App\Models\Branch::all();
        return view('admin.customers.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $existingCustomer = null;
        if ($request->filled('nic')) {
            $existingCustomer = Customer::where('nic', $request->nic)->first();
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nic' => 'nullable|string|max:20',
            'phone' => 'required|string|max:20',
            'mobile_numbers' => 'nullable|string',
            'email' => 'nullable|email|max:255|unique:customers,email' . ($existingCustomer ? ',' . $existingCustomer->id : ''),
            'address' => 'nullable|string',
            'primary_branch_id' => 'nullable|exists:branches,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'promotional_sms_opt_in' => 'boolean',
            'emi_lock_mode' => 'nullable|string|max:255',
            'emi_number' => 'nullable|string|max:255',
              'category' => 'nullable|string|max:255',
            'guarantor_name' => 'nullable|string|max:255',
            'guarantor_nic' => 'nullable|string|max:255',
            'guarantor_mobile_number' => 'nullable|string|max:255',
            'guarantor_address' => 'nullable|string',

            'customer_age' => 'nullable|string|max:255',
            'customer_occupation' => 'nullable|string|max:255',
            'customer_institute_name_address' => 'nullable|string|max:255',
            'customer_monthly_salary' => 'nullable|string|max:255',
            'customer_bank_branch' => 'nullable|string|max:255',

            'guarantor_1_occupation' => 'nullable|string|max:255',
            'guarantor_1_monthly_income' => 'nullable|string|max:255',
            'guarantor_1_bank_branch' => 'nullable|string|max:255',

            'guarantor_2_name' => 'nullable|string|max:255',
            'guarantor_2_nic' => 'nullable|string|max:255',
            'guarantor_2_address' => 'nullable|string|max:255',
            'guarantor_2_phone' => 'nullable|string|max:255',
            'guarantor_2_occupation' => 'nullable|string|max:255',
            'guarantor_2_monthly_income' => 'nullable|string|max:255',
            'guarantor_2_bank_branch' => 'nullable|string|max:255',
        ]);

        if ($existingCustomer) {
            // Update existing customer with new information if provided
            if (isset($validated['mobile_numbers'])) {
                $validated['mobile_numbers'] = array_filter(array_map('trim', explode(',', $validated['mobile_numbers'])));
            }
            $validated['is_active'] = $request->wantsJson() ? true : $request->has('is_active');
            $validated['promotional_sms_opt_in'] = $request->has('promotional_sms_opt_in');
            
            $existingCustomer->update($validated);
            AuditLog::log('customer_updated', "Customer '{$existingCustomer->name}' updated via store (duplicate NIC)", $existingCustomer);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Existing customer with this NIC updated successfully!',
                    'customer' => [
                        'id' => $existingCustomer->id,
                        'name' => $existingCustomer->name,
                        'outstanding_balance' => $existingCustomer->outstanding_balance,
                        'nic' => $existingCustomer->nic,
                        'phone' => $existingCustomer->phone,
                        'address' => $existingCustomer->address,
                    ]
                ]);
            }
            return redirect()->route('admin.customers.index')
                ->with('success', 'Existing customer with this NIC updated successfully.');
        }

        if (isset($validated['mobile_numbers'])) {
            $validated['mobile_numbers'] = array_filter(array_map('trim', explode(',', $validated['mobile_numbers'])));
        }

        $validated['outstanding_balance'] = 0;
        $validated['is_active'] = $request->wantsJson() ? true : $request->has('is_active');
        $validated['promotional_sms_opt_in'] = $request->has('promotional_sms_opt_in');

        $customer = Customer::create($validated);
        
        AuditLog::log('customer_created', "Customer '{$customer->name}' created", $customer);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer added successfully!',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'outstanding_balance' => $customer->outstanding_balance,
                    'nic' => $customer->nic,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                ]
            ]);
        }

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales.items.item', 'credits']);
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.customers.show', compact('customer', 'routePrefix'));
    }

    public function edit(Customer $customer)
    {
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        $branches = \App\Models\Branch::all();
        return view('admin.customers.edit', compact('customer', 'routePrefix', 'branches'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nic' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'mobile_numbers' => 'nullable|string',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'address' => 'nullable|string',
            'primary_branch_id' => 'nullable|exists:branches,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'promotional_sms_opt_in' => 'boolean',
             'category' => 'nullable|string|max:255',
            'emi_lock_mode' => 'nullable|string|max:255',
            'emi_number' => 'nullable|string|max:255',
            'guarantor_name' => 'nullable|string|max:255',
            'guarantor_nic' => 'nullable|string|max:255',
            'guarantor_mobile_number' => 'nullable|string|max:255',
            'guarantor_address' => 'nullable|string',

            'customer_age' => 'nullable|string|max:255',
            'customer_occupation' => 'nullable|string|max:255',
            'customer_institute_name_address' => 'nullable|string|max:255',
            'customer_monthly_salary' => 'nullable|string|max:255',
            'customer_bank_branch' => 'nullable|string|max:255',

            'guarantor_1_occupation' => 'nullable|string|max:255',
            'guarantor_1_monthly_income' => 'nullable|string|max:255',
            'guarantor_1_bank_branch' => 'nullable|string|max:255',

            'guarantor_2_name' => 'nullable|string|max:255',
            'guarantor_2_nic' => 'nullable|string|max:255',
            'guarantor_2_address' => 'nullable|string|max:255',
            'guarantor_2_phone' => 'nullable|string|max:255',
            'guarantor_2_occupation' => 'nullable|string|max:255',
            'guarantor_2_monthly_income' => 'nullable|string|max:255',
            'guarantor_2_bank_branch' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['promotional_sms_opt_in'] = $request->has('promotional_sms_opt_in');

        if ($request->filled('nic')) {
            $existingCustomer = Customer::where('nic', $request->nic)->where('id', '!=', $customer->id)->first();
            if ($existingCustomer) {
                return back()->withInput()->with('existingCustomer', $existingCustomer)->withErrors(['nic' => 'Another customer with this NIC already exists.']);
            }
        }

        if (isset($validated['mobile_numbers'])) {
            $validated['mobile_numbers'] = array_filter(array_map('trim', explode(',', $validated['mobile_numbers'])));
        }

        $oldValues = $customer->toArray();
        $customer->update($validated);
        
        AuditLog::log('customer_updated', "Customer '{$customer->name}' updated", $customer, $oldValues, $customer->toArray());

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        // Check if customer has any sales
        if ($customer->sales()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete customer with existing sales. Deactivate instead.']);
        }

        $customerName = $customer->name;
        $customer->delete();
        
        AuditLog::log('customer_deleted', "Customer '{$customerName}' deleted", null);

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function indexApi(Request $request)
    {
        $query = Customer::query();
        
        // Apply same filters as index method
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        $customers = $query->latest()->limit(20)->get();
        
        return response()->json([
            'customers' => $customers->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone ?? 'N/A',
                    'address' => $customer->address ?? 'N/A',
                    'credit_limit' => (float) $customer->credit_limit,
                    'outstanding_balance' => (float) $customer->outstanding_balance,
                    'is_active' => (bool) $customer->is_active,
                    'updated_at' => $customer->updated_at->timestamp,
                ];
            }),
        ]);
    }
}

