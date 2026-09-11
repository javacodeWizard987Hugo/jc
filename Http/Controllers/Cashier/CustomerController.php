<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function create()
    {
        $branches = \App\Models\Branch::all();
        return view('cashier.customers.create', compact('branches'));
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
            $validated['is_active'] = true;
            $validated['promotional_sms_opt_in'] = $request->has('promotional_sms_opt_in');
            
            $existingCustomer->update($validated);
            AuditLog::log('customer_updated', "Customer '{$existingCustomer->name}' updated via store by cashier (duplicate NIC)", $existingCustomer);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Existing customer with this NIC updated successfully!',
                    'customer' => $existingCustomer
                ]);
            }
            return redirect()->route('cashier.pos')
                ->with('success', 'Existing customer with this NIC updated successfully.');
        }

        if (isset($validated['mobile_numbers'])) {
            $validated['mobile_numbers'] = array_filter(array_map('trim', explode(',', $validated['mobile_numbers'])));
        }

        $validated['outstanding_balance'] = 0;
        $validated['is_active'] = true; // Cashiers can only create active customers
        $validated['promotional_sms_opt_in'] = $request->has('promotional_sms_opt_in');

        $customer = Customer::create($validated);
        
        AuditLog::log('customer_created', "Customer '{$customer->name}' created by cashier", $customer);

        // Return JSON if AJAX request
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'customer' => $customer
            ]);
        }

        return redirect()->route('cashier.pos')
            ->with('success', 'Customer created successfully. You can now select them for credit sales.');
    }
}

