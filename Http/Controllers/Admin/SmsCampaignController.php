<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use App\Models\Customer;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsCampaignController extends Controller
{
    public function index()
    {
        $campaigns = \App\Models\SmsLog::where('message_type', 'promotional')->latest()->paginate(20);
        return view('admin.sms-campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $branches = \App\Models\Branch::all();
        return view('admin.sms-campaigns.create', compact('branches'));
    }

    public function store(Request $request, SmsService $smsService)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'customers' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id|required_if:customers,by_branch',
            'months' => 'nullable|integer|min:1|required_if:customers,by_purchase_history',
            'category' => 'nullable|string|required_if:customers,by_customer_category',
        ]);

        $customers = Customer::where('promotional_sms_opt_in', true);

        if ($validated['customers'] === 'by_branch') {
            $customers->where('primary_branch_id', $validated['branch_id']);
        } elseif ($validated['customers'] === 'by_purchase_history') {
            $customers->whereHas('sales', function ($query) use ($validated) {
                $query->where('created_at', '>=', now()->subMonths($validated['months']));
            });
        } elseif ($validated['customers'] === 'by_customer_category') {
            // Assuming 'normal' and 'vip' are stored in a 'category' column on the customers table
            $customers->where('category', $validated['category']);
        }

        $customers = $customers->get();

        \App\Jobs\SendSmsCampaign::dispatch($customers, $validated['message']);

        return redirect()->route('admin.sms-campaigns.index')
            ->with('success', 'SMS campaign has been queued for sending.');
    }

    public function summary()
    {
        $logs = \App\Models\SmsLog::selectRaw('message_type, status, COUNT(*) as count')
            ->groupBy('message_type', 'status')
            ->get();
            
        return view('admin.sms-campaigns.summary', compact('logs'));
    }
}
