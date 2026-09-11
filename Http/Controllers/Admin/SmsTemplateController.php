<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function index()
    {
        $templates = SmsTemplate::all();
        return view('admin.sms-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.sms-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|unique:sms_templates,event_name',
            'template' => 'required|string',
            'placeholders' => 'nullable|string',
        ]);

        $validated['placeholders'] = $validated['placeholders'] ? array_map('trim', explode(',', $validated['placeholders'])) : null;

        SmsTemplate::create($validated);

        return redirect()->route('admin.sms-templates.index')
            ->with('success', 'SMS template created successfully.');
    }

    public function edit(SmsTemplate $smsTemplate)
    {
        return view('admin.sms-templates.edit', ['template' => $smsTemplate]);
    }

    public function update(Request $request, SmsTemplate $smsTemplate)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|unique:sms_templates,event_name,' . $smsTemplate->id,
            'template' => 'required|string',
            'placeholders' => 'nullable|string',
        ]);

        $validated['placeholders'] = $validated['placeholders'] ? array_map('trim', explode(',', $validated['placeholders'])) : null;

        $smsTemplate->update($validated);

        return redirect()->route('admin.sms-templates.index')
            ->with('success', 'SMS template updated successfully.');
    }

    public function destroy(SmsTemplate $smsTemplate)
    {
        $smsTemplate->delete();

        return redirect()->route('admin.sms-templates.index')
            ->with('success', 'SMS template deleted successfully.');
    }
}
