<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarrantyJob;
use App\Models\Warranty;
use App\Models\Branch;
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
        $query = WarrantyJob::with(['warranty.serialNumber.item', 'warranty.saleItem.sale', 'warranty.customer', 'branch', 'creator']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                  ->orWhereHas('warranty.serialNumber', function ($sq) use ($search) {
                      $sq->where('serial_number', 'like', "%{$search}%");
                  })->orWhereHas('warranty.saleItem.sale', function ($sq) use ($search) {
                      $sq->where('invoice_number', 'like', "%{$search}%");
                  })->orWhereHas('warranty.customer', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('nic', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })->orWhereHas('warranty.serialNumber.item', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('claim_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('claim_date', '<=', $request->input('end_date'));
        }

        $jobs = $query->latest()->paginate(20);
        $branches = Branch::all();
        $statuses = WarrantyJob::STATUSES;

        return view('admin.warranty-jobs.index', compact('jobs', 'branches', 'statuses'));
    }

    public function create()
    {
        $warranties = Warranty::with(['serialNumber.item', 'customer', 'saleItem.sale'])->get();
        $branches = Branch::all();
        return view('admin.warranty-jobs.create', compact('warranties', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warranty_id' => 'required|exists:warranties,id',
            'branch_id' => 'required|exists:branches,id',
            'claim_date' => 'required|date',
            'problem_description' => 'required|string',
            'claim_type' => 'required|string|in:repair,replacement,inspection',
            'remarks' => 'nullable|string',
        ]);

        $job_number = 'WJ-' . now()->format('Ymd') . '-' . mt_rand(1000, 9999);

        $warrantyJob = WarrantyJob::create([
            'job_number' => $job_number,
            'warranty_id' => $validated['warranty_id'],
            'branch_id' => $validated['branch_id'],
            'claim_date' => $validated['claim_date'],
            'problem_description' => $validated['problem_description'],
            'claim_type' => $validated['claim_type'],
            'status' => 'Received',
            'remarks' => $validated['remarks'],
            'created_by' => auth()->id(),
        ]);

        \App\Models\WarrantyJobHistory::create([
            'warranty_job_id' => $warrantyJob->id,
            'new_status' => 'Received',
            'remarks' => 'Warranty claim job created.',
            'updated_by' => auth()->id(),
        ]);

        // Trigger SMS to customer
        $warranty = $warrantyJob->warranty;
        $customer = $warranty->customer ?? $warranty->saleItem?->sale?->customer;
        $branch = $warrantyJob->branch;
        $itemModel = $warranty->serialNumber?->item?->name ?? 'Item';

        if ($customer && $customer->phone) {
            $this->smsService->sendSms($customer->phone, 'warranty_claim_creation', [
                'CustomerName' => $customer->name,
                'JobNo' => $warrantyJob->job_number,
                'ItemModel' => $itemModel,
                'BranchName' => $branch ? $branch->name : 'Branch',
                'BranchContact' => $branch ? ($branch->phone ?? 'Branch Contact') : 'Branch Contact',
            ]);
        }

        $redirectRoute = auth()->user()->isAdmin() ? 'admin.warranty-jobs.index' : 'cashier.warranty-jobs.index';
        return redirect()->route($redirectRoute)->with('success', 'Warranty claim job created successfully.');
    }

    public function show($id)
    {
        $warrantyJob = WarrantyJob::with([
            'warranty.serialNumber.item',
            'warranty.saleItem.sale',
            'warranty.customer',
            'branch',
            'creator',
            'history.updater'
        ])->findOrFail($id);
        $statuses = WarrantyJob::STATUSES;
        return view('admin.warranty-jobs.show', compact('warrantyJob', 'statuses'));
    }

    public function edit(WarrantyJob $warrantyJob)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $warrantyJob = WarrantyJob::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', WarrantyJob::STATUSES),
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

        // Send SMS if status is changed to 'Ready for Collection'
        if ($validated['status'] === 'Ready for Collection') {
            $warranty = $warrantyJob->warranty;
            $customer = $warranty->customer ?? $warranty->saleItem?->sale?->customer;
            $branch = $warrantyJob->branch;

            if ($customer && $customer->phone) {
                $this->smsService->sendSms($customer->phone, 'warranty_job_ready', [
                    'CustomerName' => $customer->name,
                    'JobNo' => $warrantyJob->job_number,
                    'BranchName' => $branch ? $branch->name : 'Branch',
                    'BranchContact' => $branch ? ($branch->phone ?? 'Branch Contact') : 'Branch Contact',
                ]);
            }
        }

        $redirectRoute = auth()->user()->isAdmin() ? 'admin.warranty-jobs.show' : 'cashier.warranty-jobs.show';
        return redirect()->route($redirectRoute, $warrantyJob->id)->with('success', 'Warranty job status updated successfully.');
    }

    public function collect(Request $request, $id)
    {
        $warrantyJob = WarrantyJob::findOrFail($id);

        $validated = $request->validate([
            'collected_by_name' => 'required|string',
            'collected_by_id' => 'required|string',
            'collected_at' => 'nullable|date',
        ]);

        $collectedAt = $validated['collected_at'] ? \Carbon\Carbon::parse($validated['collected_at']) : now();

        $oldStatus = $warrantyJob->status;

        $warrantyJob->update([
            'status' => 'Collected',
            'collected_by_name' => $validated['collected_by_name'],
            'collected_by_id' => $validated['collected_by_id'],
            'collected_at' => $collectedAt,
        ]);

        \App\Models\WarrantyJobHistory::create([
            'warranty_job_id' => $warrantyJob->id,
            'old_status' => $oldStatus,
            'new_status' => 'Collected',
            'remarks' => "Collected by: {$validated['collected_by_name']} (ID/NIC: {$validated['collected_by_id']}) on " . $collectedAt->format('Y-m-d H:i'),
            'updated_by' => auth()->id(),
        ]);

        $redirectRoute = auth()->user()->isAdmin() ? 'admin.warranty-jobs.show' : 'cashier.warranty-jobs.show';
        return redirect()->route($redirectRoute, $warrantyJob->id)->with('success', 'Item recorded as collected successfully.');
    }
}
