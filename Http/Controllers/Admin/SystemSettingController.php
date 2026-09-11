<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = SystemSetting::whereIn('key', ['overdue_grace_period'])->get()->keyBy('key');
        $gracePeriod = $settings->get('overdue_grace_period')->value ?? 7; // Default to 7 if not set

        return view('admin.system-settings.index', compact('gracePeriod'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'overdue_grace_period' => 'required|integer|min:0',
        ]);

        SystemSetting::setValue(
            'overdue_grace_period',
            $validated['overdue_grace_period'],
            'number',
            'The number of days after a due date before an installment is marked as overdue.'
        );

        return redirect()->route('admin.system-settings.index')
            ->with('success', 'System settings updated successfully.');
    }
}
