<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarrantyJob;
use App\Services\SmsService;
use Illuminate\Http\Request;

class WarrantyJobController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    public function index(Request $request)
    {
        $query = WarrantyJob::with(['warranty.serialNumber.item', 'branch', 'creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $jobs = $query->latest()->paginate(20);
        return view('admin.warranty-jobs.index', compact('jobs'));
    }

    public function create()
    {
        $warranties = \App\Models\Warranty::with('serialNumber.item')->get();
        $branches = \App\Models\Branch::all();
        return view('admin.warranty-jobs.create', compact('warranties', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warranty_id' => 'required|exists:warranties,id',
            'branch_id' => 'required|exists:branches,id',
            'problem_description' => 'required|string',
            'claim_type' => 'required|string|in:repair,replacement,inspection',
            'remarks' => 'nullable|string',
        ]);

        $job_number = 'WJ-' . now()->format('Ymd') . '-' . mt_rand(1000, 9999);

        $warrantyJob = WarrantyJob::create([
            'job_number' => $job_number,
            'warranty_id' => $validated['warranty_id'],
            'branch_id' => $validated['branch_id'],
            'problem_description' => $validated['problem_description'],
            'claim_type' => $validated['claim_type'],
            'status' => 'Received',
            'remarks' => $validated['remarks'],
            'created_by' => auth()->id(),
        ]);

        \App\Models\WarrantyJobHistory::create([
            'warranty_job_id' => $warrantyJob->id,
            'new_status' => 'Received',
            'remarks' => 'Job created.',
            'updated_by' => auth()->id(),
        ]);

        // Send SMS to customer
        $customer = $warrantyJob->warranty->sale->customer;
        if ($customer && $customer->phone) {
            $this->smsService->sendSms($customer->phone, 'warranty_claim_creation', [
                'CustomerName' => $customer->name,
                'JobNo' => $warrantyJob->job_number,
            ]);
        }

        return redirect()->route('admin.warranty-jobs.index')->with('success', 'Warranty job created successfully.');
    }

    public function show($id)
    {
        $warrantyJob = WarrantyJob::with(['warranty.serialNumber.item', 'branch', 'creator', 'history.updater'])->findOrFail($id);
        $statuses = WarrantyJob::STATUSES;
        return view('admin.warranty-jobs.show', compact('warrantyJob', 'statuses'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WarrantyJob $warrantyJob)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $warrantyJob = WarrantyJob::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $old_status = $warrantyJob->status;

        $warrantyJob->update([
            'status' => $validated['status'],
            'remarks' => $validated['remarks'],
        ]);

        \App\Models\WarrantyJobHistory::create([
            'warranty_job_id' => $warrantyJob->id,
            'old_status' => $old_status,
            'new_status' => $validated['status'],
            'remarks' => $validated['remarks'],
            'updated_by' => auth()->id(),
        ]);

        // Send SMS if status is 'Ready for Collection'
        if ($validated['status'] === 'Ready for Collection') {
            $customer = $warrantyJob->warranty->sale->customer;
            if ($customer && $customer->phone) {
                $this->smsService->sendSms($customer->phone, 'warranty_job_ready', [
                    'CustomerName' => $customer->name,
                    'JobNo' => $warrantyJob->job_number,
                ]);
            }
        }

        return redirect()->route('admin.warranty-jobs.show', $warrantyJob->id)->with('success', 'Warranty job status updated successfully.');
    }

    public function collect(Request $request, $id)
    {
        $warrantyJob = WarrantyJob::findOrFail($id);

        $validated = $request->validate([
            'collected_by_name' => 'required|string',
            'collected_by_id' => 'required|string',
        ]);

        $warrantyJob->update([
            'status' => 'Collected',
            'collected_by_name' => $validated['collected_by_name'],
            'collected_by_id' => $validated['collected_by_id'],
            'collected_at' => now(),
        ]);

        \App\Models\WarrantyJobHistory::create([
            'warranty_job_id' => $warrantyJob->id,
            'old_status' => 'Ready for Collection',
            'new_status' => 'Collected',
            'remarks' => "Collected by: {$validated['collected_by_name']} ({$validated['collected_by_id']})",
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.warranty-jobs.show', $warrantyJob->id)->with('success', 'Item marked as collected.');
    }
}
