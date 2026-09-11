<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // ✅ THIS LINE IS REQUIRED
use App\Models\StockTransfer;
use Illuminate\Http\Request;


class StockTransferController extends Controller
{
    public function index()
    {
        $stockTransfers = StockTransfer::with(['fromLocation', 'toLocation', 'createdBy'])->latest()->paginate(20);
        return view('admin.stock-transfers.index', compact('stockTransfers'));
    }

    public function create()
    {
        $branches = \App\Models\Branch::all();
        return view('admin.stock-transfers.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_location_id' => 'required|exists:branches,id',
            'to_location_id' => 'required|exists:branches,id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.serial_number' => 'nullable|string',
        ]);

        $stockTransfer = StockTransfer::create([
            'from_location_id' => $validated['from_location_id'],
            'to_location_id' => $validated['to_location_id'],
            'transfer_date' => $validated['transfer_date'],
            'notes' => $validated['notes'],
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['items'] as $itemData) {
            $stockTransfer->items()->create($itemData);
        }

        return redirect()->route('admin.stock-transfers.index')->with('success', 'Stock transfer created successfully.');
    }

    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['fromLocation', 'toLocation', 'createdBy', 'approvedBy', 'items.item']);
        return view('admin.stock-transfers.show', compact('stockTransfer'));
    }

    public function edit(StockTransfer $stockTransfer)
    {
        //
    }

    public function update(Request $request, StockTransfer $stockTransfer)
    {
        //
    }

    public function approve(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return back()->withErrors(['error' => 'This transfer has already been processed.']);
        }

        foreach ($stockTransfer->items as $transferItem) {
            // Decrement stock from source
            $sourceStock = \App\Models\BranchStock::where('branch_id', $stockTransfer->from_location_id)
                ->where('item_id', $transferItem->item_id)->first();
            if (!$sourceStock || $sourceStock->quantity < $transferItem->quantity) {
                return back()->withErrors(['error' => 'Insufficient stock at source location for item: ' . $transferItem->item->name]);
            }
            $sourceStock->decrement('quantity', $transferItem->quantity);

            // Increment stock at destination
            $destinationStock = \App\Models\BranchStock::firstOrNew(['branch_id' => $stockTransfer->to_location_id, 'item_id' => $transferItem->item_id]);
            $destinationStock->increment('quantity', $transferItem->quantity);

            // Update serial number location
            if ($transferItem->serial_number) {
                $serial = \App\Models\SerialNumber::where('serial_number', $transferItem->serial_number)->first();
                if ($serial) {
                    $serial->update(['branch_id' => $stockTransfer->to_location_id]);
                }
            }
        }

        $stockTransfer->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.stock-transfers.index')->with('success', 'Stock transfer approved successfully.');
    }

    public function reject(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return back()->withErrors(['error' => 'This transfer has already been processed.']);
        }

        $stockTransfer->update(['status' => 'rejected']);

        return redirect()->route('admin.stock-transfers.index')->with('success', 'Stock transfer rejected successfully.');
    }
}
