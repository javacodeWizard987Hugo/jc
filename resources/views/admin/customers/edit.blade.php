@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto">
    <div class="mb-6 text-center">
        <h1 class="text-3xl font-bold text-gray-900">Edit Customer / Client</h1>
    </div>

    <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200">
        <form action="{{ route(($routePrefix ?? 'admin') . '.customers.update', $customer) }}" method="POST" class="p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700">Name *</label>
                    <input type="text" name="name" id="name" required
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 @error('name') border-red-500 @enderror"
                           value="{{ old('name', $customer->name) }}">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="emi_lock_mode" class="block text-sm font-bold text-gray-700">EMI Lock Mode</label>
                        <input type="text" name="emi_lock_mode" id="emi_lock_mode"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                               value="{{ old('emi_lock_mode', $customer->emi_lock_mode) }}">
                    </div>
                    <div>
                        <label for="emi_number" class="block text-sm font-bold text-gray-700">EMI Number</label>
                        <input type="text" name="emi_number" id="emi_number"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                               value="{{ old('emi_number', $customer->emi_number) }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nic" class="block text-sm font-bold text-gray-700">NIC (National ID)</label>
                        <input type="text" name="nic" id="nic"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 @error('nic') border-red-500 @enderror"
                               value="{{ old('nic', $customer->nic) }}">
                        @error('nic')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-bold text-gray-700">Primary Phone</label>
                        <input type="text" name="phone" id="phone"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                               value="{{ old('phone', $customer->phone) }}">
                    </div>
                </div>

                <div>
                    <label for="mobile_numbers" class="block text-sm font-bold text-gray-700">Additional Mobile Numbers</label>
                    <input type="text" name="mobile_numbers" id="mobile_numbers"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                           placeholder="Enter multiple numbers, separated by commas"
                           value="{{ old('mobile_numbers', is_array($customer->mobile_numbers) ? implode(', ', $customer->mobile_numbers) : $customer->mobile_numbers) }}">
                    <p class="mt-1 text-sm text-gray-500">You can add more numbers here.</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700">Email</label>
                    <input type="email" name="email" id="email"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                           value="{{ old('email', $customer->email) }}">
                </div>

                <div>
                    <label for="address" class="block text-sm font-bold text-gray-700">Address</label>
                    <textarea name="address" id="address" rows="3"
                              class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">{{ old('address', $customer->address) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="primary_branch_id" class="block text-sm font-bold text-gray-700">Primary Branch</label>
                        <select name="primary_branch_id" id="primary_branch_id"
                                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            <option value="">None</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('primary_branch_id', $customer->primary_branch_id) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500 font-medium">Assign a primary branch for this customer (optional).</p>
                    </div>
                    <div>
                        <label for="credit_limit" class="block text-sm font-bold text-gray-700">Credit Limit (Rs.)</label>
                        <input type="number" name="credit_limit" id="credit_limit" step="0.01" min="0"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                               value="{{ old('credit_limit', $customer->credit_limit) }}">
                    </div>
                </div>

                <div class="flex items-center space-x-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-red-600 focus:ring-red-500 h-5 w-5">
                        <span class="ml-2 text-sm font-bold text-gray-700">Active</span>
                    </label>

                    <div class="flex-1">
                        <label for="category" class="block text-sm font-bold text-gray-700">Customer Category</label>
                        <select name="category" id="category" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            <option value="normal" {{ old('category', $customer->category) === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="vip" {{ old('category', $customer->category) === 'vip' ? 'selected' : '' }}>VIP</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100">
                    <div>
                        <label for="customer_age" class="block text-sm font-bold text-gray-700">Age</label>
                        <input type="text" name="customer_age" id="customer_age"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                               value="{{ old('customer_age', $customer->customer_age) }}">
                    </div>
                    <div>
                        <label for="customer_occupation" class="block text-sm font-bold text-gray-700">Occupation</label>
                        <input type="text" name="customer_occupation" id="customer_occupation"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                               value="{{ old('customer_occupation', $customer->customer_occupation) }}">
                    </div>
                </div>

                <div>
                    <label for="customer_institute_name_address" class="block text-sm font-bold text-gray-700">Institute Name & Address</label>
                    <textarea name="customer_institute_name_address" id="customer_institute_name_address" rows="2"
                              class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500">{{ old('customer_institute_name_address', $customer->customer_institute_name_address) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="customer_monthly_salary" class="block text-sm font-bold text-gray-700">Monthly Salary</label>
                        <input type="text" name="customer_monthly_salary" id="customer_monthly_salary"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                               value="{{ old('customer_monthly_salary', $customer->customer_monthly_salary) }}">
                    </div>
                    <div>
                        <label for="customer_bank_branch" class="block text-sm font-bold text-gray-700">Bank & Branch</label>
                        <input type="text" name="customer_bank_branch" id="customer_bank_branch"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                               value="{{ old('customer_bank_branch', $customer->customer_bank_branch) }}">
                    </div>
                </div>

                <div class="pt-8 border-t-2 border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">1st Guarantor Information</h3>
                    
                    <div class="space-y-6">
                        <div>
                            <label for="guarantor_name" class="block text-sm font-bold text-gray-700">Guarantor Name</label>
                            <input type="text" name="guarantor_name" id="guarantor_name"
                                   class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                                   value="{{ old('guarantor_name', $customer->guarantor_name) }}">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="guarantor_nic" class="block text-sm font-bold text-gray-700">Guarantor NIC</label>
                                <input type="text" name="guarantor_nic" id="guarantor_nic"
                                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                                       value="{{ old('guarantor_nic', $customer->guarantor_nic) }}">
                            </div>
                            <div>
                                <label for="guarantor_mobile_number" class="block text-sm font-bold text-gray-700">Guarantor Mobile</label>
                                <input type="text" name="guarantor_mobile_number" id="guarantor_mobile_number"
                                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                                       value="{{ old('guarantor_mobile_number', $customer->guarantor_mobile_number) }}">
                            </div>
                        </div>

                        <div>
                            <label for="guarantor_address" class="block text-sm font-bold text-gray-700">Guarantor Address</label>
                            <textarea name="guarantor_address" id="guarantor_address" rows="2"
                                      class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500">{{ old('guarantor_address', $customer->guarantor_address) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="guarantor_1_occupation" class="block text-sm font-bold text-gray-700">Occupation</label>
                                <input type="text" name="guarantor_1_occupation" id="guarantor_1_occupation"
                                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                                       value="{{ old('guarantor_1_occupation', $customer->guarantor_1_occupation) }}">
                            </div>
                            <div>
                                <label for="guarantor_1_monthly_income" class="block text-sm font-bold text-gray-700">Monthly Income</label>
                                <input type="text" name="guarantor_1_monthly_income" id="guarantor_1_monthly_income"
                                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                                       value="{{ old('guarantor_1_monthly_income', $customer->guarantor_1_monthly_income) }}">
                            </div>
                            <div>
                                <label for="guarantor_1_bank_branch" class="block text-sm font-bold text-gray-700">Bank & Branch</label>
                                <input type="text" name="guarantor_1_bank_branch" id="guarantor_1_bank_branch"
                                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                                       value="{{ old('guarantor_1_bank_branch', $customer->guarantor_1_bank_branch) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t-2 border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">2nd Guarantor Information</h3>
                    
                    <div class="space-y-6">
                        <div>
                            <label for="guarantor_2_name" class="block text-sm font-bold text-gray-700">Guarantor Name</label>
                            <input type="text" name="guarantor_2_name" id="guarantor_2_name"
                                   class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                                   value="{{ old('guarantor_2_name', $customer->guarantor_2_name) }}">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="guarantor_2_nic" class="block text-sm font-bold text-gray-700">Guarantor NIC</label>
                                <input type="text" name="guarantor_2_nic" id="guarantor_2_nic"
                                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                                       value="{{ old('guarantor_2_nic', $customer->guarantor_2_nic) }}">
                            </div>
                            <div>
                                <label for="guarantor_2_phone" class="block text-sm font-bold text-gray-700">Guarantor Phone</label>
                                <input type="text" name="guarantor_2_phone" id="guarantor_2_phone"
                                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                                       value="{{ old('guarantor_2_phone', $customer->guarantor_2_phone) }}">
                            </div>
                        </div>

                        <div>
                            <label for="guarantor_2_address" class="block text-sm font-bold text-gray-700">Guarantor Address</label>
                            <textarea name="guarantor_2_address" id="guarantor_2_address" rows="2"
                                      class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500">{{ old('guarantor_2_address', $customer->guarantor_2_address) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="guarantor_2_occupation" class="block text-sm font-bold text-gray-700">Occupation</label>
                                <input type="text" name="guarantor_2_occupation" id="guarantor_2_occupation"
                                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                                       value="{{ old('guarantor_2_occupation', $customer->guarantor_2_occupation) }}">
                            </div>
                            <div>
                                <label for="guarantor_2_monthly_income" class="block text-sm font-bold text-gray-700">Monthly Income</label>
                                <input type="text" name="guarantor_2_monthly_income" id="guarantor_2_monthly_income"
                                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                                       value="{{ old('guarantor_2_monthly_income', $customer->guarantor_2_monthly_income) }}">
                            </div>
                            <div>
                                <label for="guarantor_2_bank_branch" class="block text-sm font-bold text-gray-700">Bank & Branch</label>
                                <input type="text" name="guarantor_2_bank_branch" id="guarantor_2_bank_branch"
                                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-red-500"
                                       value="{{ old('guarantor_2_bank_branch', $customer->guarantor_2_bank_branch) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center pt-4">
                    <input type="checkbox" name="promotional_sms_opt_in" id="promotional_sms_opt_in" value="1" {{ old('promotional_sms_opt_in', $customer->promotional_sms_opt_in) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-red-600 focus:ring-red-500 h-5 w-5">
                    <label for="promotional_sms_opt_in" class="ml-2 text-sm font-bold text-gray-700">Receive Promotional SMS</label>
                </div>

                <div class="flex justify-end gap-4 pt-8 border-t border-gray-100">
                    <a href="{{ route(($routePrefix ?? 'admin') . '.customers.index') }}" 
                       class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 font-bold hover:bg-gray-50 transition duration-150">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-red-600 text-white rounded-md font-bold hover:bg-red-700 shadow-lg transform active:scale-95 transition duration-150">
                        Update Customer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
