<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockMovement::whereIn('type', ['adjustment', 'expire', 'loss', 'stock_take'])
            ->with(['item', 'creator'])
            ->latest()
            ->paginate(20);
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.stock-adjustments.index', compact('adjustments', 'routePrefix'));
    }

    public function create()
    {
        $items = Item::where('is_active', true)->orderBy('name')->get();
        $branches = \App\Models\Branch::all();
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.stock-adjustments.create', compact('items', 'branches', 'routePrefix'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'branch_id' => 'required|exists:branches,id',
            'type' => 'required|in:adjustment,expire,loss,stock_take',
            'quantity' => 'required|numeric',
            'quantity_unit' => 'required|in:kg,g,kg_g',
            'notes' => 'required|string|min:10',
            'quantity_kg_single' => 'nullable|numeric',
            'quantity_g_single' => 'nullable|numeric',
            'quantity_kg' => 'nullable|numeric|min:0',
            'quantity_g' => 'nullable|numeric|min:0|max:999',
        ]);

        $item = Item::findOrFail($validated['item_id']);
        $itemUnit = $item->unit_of_measure;
        $quantityUnit = $validated['quantity_unit'];
        $adjustmentQuantity = 0;
        $displayQuantity = '';
        
        // Calculate adjustment quantity based on selected unit
        if ($quantityUnit === 'kg') {
            $kgValue = $request->input('quantity_kg_single', 0);
            // Convert to item's unit
            if ($itemUnit === 'kg') {
                $adjustmentQuantity = $kgValue;
            } elseif ($itemUnit === 'g') {
                $adjustmentQuantity = $kgValue * 1000;
            } else {
                $adjustmentQuantity = $kgValue;
            }
            $displayQuantity = $kgValue . ' kg';
        } elseif ($quantityUnit === 'g') {
            $gValue = $request->input('quantity_g_single', 0);
            // Convert to item's unit
            if ($itemUnit === 'kg') {
                $adjustmentQuantity = $gValue / 1000;
            } elseif ($itemUnit === 'g') {
                $adjustmentQuantity = $gValue;
            } else {
                $adjustmentQuantity = $gValue / 1000;
            }
            $displayQuantity = $gValue . ' g';
        } elseif ($quantityUnit === 'kg_g') {
            $kg = $request->input('quantity_kg', 0);
            $g = $request->input('quantity_g', 0);
            $totalKg = $kg + ($g / 1000);
            
            // Convert to item's unit
            if ($itemUnit === 'kg') {
                $adjustmentQuantity = $totalKg;
            } elseif ($itemUnit === 'g') {
                $adjustmentQuantity = $totalKg * 1000;
            } else {
                $adjustmentQuantity = $totalKg;
            }
            
            // Format display string
            if ($kg > 0 && $g > 0) {
                $displayQuantity = $kg . ' kg ' . $g . ' g';
            } elseif ($kg > 0) {
                $displayQuantity = $kg . ' kg';
            } elseif ($g > 0) {
                $displayQuantity = $g . ' g';
            } else {
                $displayQuantity = '0';
            }
        }
        
        // Fallback to direct quantity if conversion didn't work
        if ($adjustmentQuantity == 0 && isset($validated['quantity']) && $validated['quantity'] > 0) {
            $adjustmentQuantity = $validated['quantity'];
        }
        
        if ($adjustmentQuantity == 0) {
            return back()->withErrors(['quantity' => 'Please enter a non-zero quantity.'])->withInput();
        }
        
        $branchStock = \App\Models\BranchStock::firstOrNew(['branch_id' => $validated['branch_id'], 'item_id' => $item->id]);
        $oldStock = $branchStock->quantity;
        
        // Calculate new stock
        // For expire, loss, stock_take - quantity should be negative (reduction)
        // For adjustment - quantity can be positive or negative
        $quantityChange = $adjustmentQuantity;
        if (in_array($validated['type'], ['expire', 'loss', 'stock_take'])) {
            // These types always reduce stock
            $quantityChange = -abs($quantityChange);
        }
        
        $newStock = $oldStock + $quantityChange;
        
        if ($newStock < 0) {
            return back()->withErrors(['quantity' => 'Stock adjustment would result in negative stock. Current stock: ' . number_format($oldStock, 2) . ' ' . $itemUnit])->withInput();
        }

        // Update branch stock
        $branchStock->quantity = $newStock;
        $branchStock->save();

        // Record stock movement
        $movement = StockMovement::create([
            'item_id' => $item->id,
         'branch_id' => $validated['branch_id'],
            'type' => $validated['type'],
            'quantity' => $quantityChange,
            'balance_after' => $newStock,
            'notes' => $validated['notes'] . ' (Adjusted: ' . $displayQuantity . ')',
            'created_by' => auth()->id(),
        ]);

        AuditLog::log('stock_adjustment', "Stock adjustment for '{$item->name}': {$validated['type']}, Quantity: {$displayQuantity} ({$quantityChange} {$itemUnit}), Notes: {$validated['notes']}", $item);

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.stock-adjustments.index')
            ->with('success', 'Stock adjustment recorded successfully.');
    }

    public function show(StockMovement $stockAdjustment)
    {
        $stockAdjustment->load(['item', 'creator']);
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.stock-adjustments.show', compact('stockAdjustment', 'routePrefix'));
    }

    public function indexApi()
    {
        $adjustments = StockMovement::whereIn('type', ['adjustment', 'expire', 'loss', 'stock_take'])
            ->with(['item', 'creator'])
            ->latest()
            ->limit(20)
            ->get();
        
        return response()->json([
            'adjustments' => $adjustments->map(function($adjustment) {
                return [
                    'id' => $adjustment->id,
                    'item_name' => $adjustment->item->name,
                    'type' => $adjustment->type,
                    'quantity' => $adjustment->quantity,
                    'balance_after' => $adjustment->balance_after,
                    'item_unit' => $adjustment->item->unit_of_measure,
                    'created_at' => $adjustment->created_at->format('Y-m-d H:i'),
                    'created_at_timestamp' => $adjustment->created_at->timestamp,
                    'creator_name' => $adjustment->creator->name ?? 'N/A',
                ];
            }),
        ]);
    }
}

