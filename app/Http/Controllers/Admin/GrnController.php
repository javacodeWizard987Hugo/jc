<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\Item;
use App\Models\SerialNumber;
use App\Models\Supplier;
use App\Models\StockMovement;
use App\Models\AuditLog;
use App\Models\BranchStock;
use Illuminate\Http\Request;

class GrnController extends Controller
{
    public function index()
    {
        $grns = Grn::with(['supplier', 'creator', 'items'])->latest()->paginate(20);

        $routePrefix = request()->route()->getName();
        $routePrefix = str_starts_with($routePrefix, 'cashier.') ? 'cashier' : 'admin';

        return view('admin.grns.index', compact('grns', 'routePrefix'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $items = Item::where('is_active', true)->get();
        $branches = \App\Models\Branch::all();

        $routePrefix = request()->route()->getName();
        $routePrefix = str_starts_with($routePrefix, 'cashier.') ? 'cashier' : 'admin';

        return view('admin.grns.create', compact('suppliers', 'items', 'branches', 'routePrefix'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'grn_date' => 'required|date',
            'branch_id' => 'required|exists:branches,id',
            'reference_document' => 'nullable|string',
            'notes' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.serial_numbers' => 'nullable|string',
        ]);

        $grnNumber = 'GRN-' . now()->format('Ymd') . '-' .
            str_pad(Grn::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $totalAmount += $item['quantity'] * $item['unit_cost'];
        }

        $grn = Grn::create([
            'grn_number' => $grnNumber,
            'supplier_id' => $validated['supplier_id'],
            'grn_date' => $validated['grn_date'],
            'reference_document' => $validated['reference_document'] ?? null,
            'total_amount' => $totalAmount,
            'created_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $itemData) {

            $item = Item::findOrFail($itemData['item_id']);
            $quantity = $itemData['quantity'];

            if ($quantity <= 0) {
                return back()->withErrors([
                    'error' => "Invalid quantity for item: {$item->name}"
                ])->withInput();
            }

            $unitCost = $itemData['unit_cost'];
            $totalCost = $quantity * $unitCost;

            $grnItem = GrnItem::create([
                'grn_id' => $grn->id,
                'item_id' => $item->id,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'expiry_date' => $itemData['expiry_date'] ?? null,
            ]);

            // SERIAL NUMBERS (Phones)
            if ($item->requires_serial_number && !empty($itemData['serial_numbers'])) {
                $serials = array_filter(array_map('trim', explode(',', $itemData['serial_numbers'])));

                foreach ($serials as $serial) {
                    SerialNumber::create([
                        'serial_number' => $serial,
                        'item_id' => $item->id,
                        'branch_id' => $validated['branch_id'],
                        'grn_item_id' => $grnItem->id,
                        'status' => 'available',
                    ]);
                }
            }

            // BRANCH STOCK UPDATE
            $branchStock = BranchStock::firstOrNew([
                'branch_id' => $validated['branch_id'],
                'item_id' => $item->id
            ]);

            $oldStock = $branchStock->quantity ?? 0;
            $newStock = $oldStock + $quantity;

            $branchStock->quantity = $newStock;
            $branchStock->save();

            // AVERAGE COST CALCULATION (PCS)
            $totalStock = $item->stock()->sum('quantity');
            $oldCost = $item->cost_price;

            $newCost = (($oldCost * ($totalStock - $quantity)) + ($unitCost * $quantity))
                / max($totalStock, 1);

            $item->update(['cost_price' => $newCost]);

            // STOCK MOVEMENT
            StockMovement::create([
                'item_id' => $item->id,
                'branch_id' => $validated['branch_id'],
                'type' => 'grn',
                'reference_type' => Grn::class,
                'reference_id' => $grn->id,
                'quantity' => $quantity,
                'balance_after' => $newStock,
                'notes' => "GRN: {$grnNumber}",
                'created_by' => auth()->id(),
            ]);
        }

        $supplier = Supplier::find($validated['supplier_id']);
        $supplier->increment('outstanding_balance', $totalAmount);

        AuditLog::log('grn_created', "GRN created: {$grnNumber} - Rs. {$totalAmount}", $grn);

        $routePrefix = str_starts_with(request()->route()->getName(), 'cashier.')
            ? 'cashier'
            : 'admin';

        return redirect()->route($routePrefix . '.grns.index')
            ->with('success', "GRN created successfully: {$grnNumber}");
    }

    public function show(Grn $grn)
    {
        $grn->load(['supplier', 'creator', 'items.item']);

        $routePrefix = str_starts_with(request()->route()->getName(), 'cashier.')
            ? 'cashier'
            : 'admin';

        return view('admin.grns.show', compact('grn', 'routePrefix'));
    }

    public function destroy(Grn $grn)
    {
        foreach ($grn->items as $grnItem) {
            $item = $grnItem->item;

            $branchStock = BranchStock::where('branch_id', $grn->branch_id)
                ->where('item_id', $item->id)
                ->first();

            if ($branchStock) {
                $branchStock->decrement('quantity', $grnItem->quantity);
            }

            StockMovement::create([
                'item_id' => $item->id,
                      'branch_id' => $grn->branch_id,
                'type' => 'adjustment',
                'reference_type' => Grn::class,
                'reference_id' => $grn->id,
                'quantity' => -$grnItem->quantity,
                'balance_after' => $item->getBranchStock($grn->branch_id),
                'notes' => "GRN deleted: {$grn->grn_number}",
                'created_by' => auth()->id(),
            ]);
        }

        $grn->supplier->decrement('outstanding_balance', $grn->total_amount);
        $grnNumber = $grn->grn_number;
        $grn->delete();

        AuditLog::log('grn_deleted', "GRN deleted: {$grnNumber}");

        $routePrefix = str_starts_with(request()->route()->getName(), 'cashier.')
            ? 'cashier'
            : 'admin';

        return redirect()->route($routePrefix . '.grns.index')
            ->with('success', 'GRN deleted successfully.');
    }
}
