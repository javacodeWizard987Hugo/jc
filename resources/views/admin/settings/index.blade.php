@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 max-w-3xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="space-y-6">
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700">Company Logo</label>
                    @if($settings['logo_path'])
                        <div class="mt-2 mb-2">
                            <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Logo" class="h-20 w-auto">
                        </div>
                    @endif
                    <input type="file" name="logo" id="logo" accept="image/*"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                    <p class="mt-1 text-sm text-gray-500">Upload company logo (max 2MB, jpeg, png, jpg, gif, svg)</p>
                </div>
                <div>
                    <label for="tax_rate" class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                    <input type="number" step="0.01" name="tax_rate" id="tax_rate" required min="0" max="100"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           value="{{ old('tax_rate', $settings['tax_rate']) }}">
                </div>

                <div>
                    <label for="max_cashier_discount" class="block text-sm font-medium text-gray-700">Maximum Cashier Discount (%)</label>
                    <input type="number" step="0.01" name="max_cashier_discount" id="max_cashier_discount" required min="0" max="100"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           value="{{ old('max_cashier_discount', $settings['max_cashier_discount']) }}">
                    <p class="mt-1 text-sm text-gray-500">Cashiers can apply discounts up to this percentage without admin approval</p>
                </div>

                <div>
                    <label for="expiry_alert_days" class="block text-sm font-medium text-gray-700">Expiry Alert Days</label>
                    <input type="number" name="expiry_alert_days" id="expiry_alert_days" required min="1"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           value="{{ old('expiry_alert_days', $settings['expiry_alert_days']) }}">
                    <p class="mt-1 text-sm text-gray-500">Show alert for items expiring within this many days</p>
                </div>

                <div>
                    <label for="invoice_format" class="block text-sm font-medium text-gray-700">Invoice Number Format</label>
                    <input type="text" name="invoice_format" id="invoice_format" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           value="{{ old('invoice_format', $settings['invoice_format']) }}">
                    <p class="mt-1 text-sm text-gray-500">Use {YYYY}, {MM}, {DD}, {NNNN} for year, month, day, and sequence number</p>
                </div>

                <div>
                    <label for="default_payment_method" class="block text-sm font-medium text-gray-700">Default Payment Method</label>
                    <select name="default_payment_method" id="default_payment_method" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                        <option value="cash" {{ old('default_payment_method', $settings['default_payment_method']) == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ old('default_payment_method', $settings['default_payment_method']) == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="cheque" {{ old('default_payment_method', $settings['default_payment_method']) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="credit" {{ old('default_payment_method', $settings['default_payment_method']) == 'credit' ? 'selected' : '' }}>Credit</option>
                    </select>
                </div>

                <div>
                    <label for="rounding_rules" class="block text-sm font-medium text-gray-700">Rounding Rules</label>
                    <select name="rounding_rules" id="rounding_rules" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                        <option value="none" {{ old('rounding_rules', $settings['rounding_rules']) == 'none' ? 'selected' : '' }}>No Rounding</option>
                        <option value="up" {{ old('rounding_rules', $settings['rounding_rules']) == 'up' ? 'selected' : '' }}>Round Up</option>
                        <option value="down" {{ old('rounding_rules', $settings['rounding_rules']) == 'down' ? 'selected' : '' }}>Round Down</option>
                        <option value="nearest" {{ old('rounding_rules', $settings['rounding_rules']) == 'nearest' ? 'selected' : '' }}>Round to Nearest</option>
                    </select>
                </div>

                <div>
                    <label for="max_cash_refund" class="block text-sm font-medium text-gray-700">Maximum Cash Refund (Rs.)</label>
                    <input type="number" step="0.01" name="max_cash_refund" id="max_cash_refund" min="0"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           value="{{ old('max_cash_refund', $settings['max_cash_refund'] ?? 10000) }}">
                    <p class="mt-1 text-sm text-gray-500">Maximum cash refund amount allowed for cashiers without admin approval</p>
                </div>

                <div>
                    <label for="session_timeout" class="block text-sm font-medium text-gray-700">Session Timeout (minutes)</label>
                    <input type="number" name="session_timeout" id="session_timeout" min="1" max="1440"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                           value="{{ old('session_timeout', $settings['session_timeout'] ?? 120) }}">
                    <p class="mt-1 text-sm text-gray-500">Automatic logout after inactivity (1-1440 minutes)</p>
                </div>

                <div class="border-t pt-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Dashboard Widget Configuration</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="dashboard_show_profit" value="1" 
                                   {{ old('dashboard_show_profit', $settings['dashboard_show_profit'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Show Profit Widget</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="dashboard_show_expenses" value="1"
                                   {{ old('dashboard_show_expenses', $settings['dashboard_show_expenses'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Show Expenses Widget</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="dashboard_show_supplier_payments" value="1"
                                   {{ old('dashboard_show_supplier_payments', $settings['dashboard_show_supplier_payments'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Show Supplier Payments Widget</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="dashboard_show_expired_items" value="1"
                                   {{ old('dashboard_show_expired_items', $settings['dashboard_show_expired_items'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Show Expired Items Widget</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="dashboard_show_low_stock" value="1"
                                   {{ old('dashboard_show_low_stock', $settings['dashboard_show_low_stock'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Show Low Stock Widget</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

