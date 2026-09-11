<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'tax_rate' => SystemSetting::getValue('tax_rate', 0),
            'max_cashier_discount' => SystemSetting::getValue('max_cashier_discount', 10),
            'expiry_alert_days' => SystemSetting::getValue('expiry_alert_days', 30),
            'invoice_format' => SystemSetting::getValue('invoice_format', 'INV-{YYYY}-{MM}-{DD}-{NNNN}'),
            'default_payment_method' => SystemSetting::getValue('default_payment_method', 'cash'),
            'rounding_rules' => SystemSetting::getValue('rounding_rules', 'none'),
            'logo_path' => SystemSetting::getValue('logo_path', null),
            'max_cash_refund' => SystemSetting::getValue('max_cash_refund', 10000),
            'session_timeout' => SystemSetting::getValue('session_timeout', 120),
            // Dashboard widget visibility
            'dashboard_show_profit' => SystemSetting::getValue('dashboard_show_profit', true),
            'dashboard_show_expenses' => SystemSetting::getValue('dashboard_show_expenses', true),
            'dashboard_show_supplier_payments' => SystemSetting::getValue('dashboard_show_supplier_payments', true),
            'dashboard_show_expired_items' => SystemSetting::getValue('dashboard_show_expired_items', true),
            'dashboard_show_low_stock' => SystemSetting::getValue('dashboard_show_low_stock', true),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
            'max_cashier_discount' => 'required|numeric|min:0|max:100',
            'expiry_alert_days' => 'required|integer|min:1',
            'invoice_format' => 'required|string',
            'default_payment_method' => 'required|in:cash,cheque,credit,card',
            'rounding_rules' => 'required|in:none,up,down,nearest',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'max_cash_refund' => 'nullable|numeric|min:0',
            'session_timeout' => 'nullable|integer|min:1|max:1440',
            // Dashboard widgets
            'dashboard_show_profit' => 'nullable|boolean',
            'dashboard_show_expenses' => 'nullable|boolean',
            'dashboard_show_supplier_payments' => 'nullable|boolean',
            'dashboard_show_expired_items' => 'nullable|boolean',
            'dashboard_show_low_stock' => 'nullable|boolean',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            $oldLogo = SystemSetting::getValue('logo_path');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Store new logo
            $logoPath = $request->file('logo')->store('logos', 'public');
            SystemSetting::setValue('logo_path', $logoPath, 'string', 'Company logo path');
        }

        // Update other settings
        foreach ($validated as $key => $value) {
            if ($key !== 'logo') {
                // Handle boolean values for dashboard widgets
                if (str_starts_with($key, 'dashboard_show_')) {
                    SystemSetting::setValue($key, $request->has($key) ? '1' : '0', 'boolean');
                } else {
                    SystemSetting::setValue($key, $value, is_numeric($value) ? 'number' : 'string');
                }
            }
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
