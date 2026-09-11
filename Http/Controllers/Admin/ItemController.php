<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\AuditLog;
use App\Models\StockMovement;
use App\Models\BranchStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index()
    {
    $items = Item::with(['category', 'supplier'])->orderBy('name', 'asc')->paginate(20);
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.items.index', compact('items', 'routePrefix'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        
        // Pre-generate next item code
        $lastItem = Item::orderBy('id', 'desc')->first();
        $nextId = $lastItem ? $lastItem->id + 1 : 1;
        $nextItemCode = 'ITM-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        
        // Ensure it's unique
        while (Item::where('item_code', $nextItemCode)->exists()) {
            $nextId++;
            $nextItemCode = 'ITM-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.items.create', compact('categories', 'suppliers', 'routePrefix', 'nextItemCode'));
    }

    public function store(Request $request)
    {
        // Use provided code or auto-generate if empty
        if (!$request->filled('item_code')) {
            $lastItem = Item::orderBy('id', 'desc')->first();
            $nextId = $lastItem ? $lastItem->id + 1 : 1;
            $itemCode = 'ITM-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            
            // Ensure it's unique
            while (Item::where('item_code', $itemCode)->exists()) {
                $nextId++;
                $itemCode = 'ITM-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
            $request->merge(['item_code' => $itemCode]);
        }

        $validated = $request->validate([
            'item_code' => 'required|string|unique:items,item_code',
            
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_of_measure' => 'required|string|in:pcs',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
        
            'supplier_id' => 'nullable|exists:suppliers,id',
            'is_active' => 'required|boolean',
            'emi_lock_mode' => 'nullable|string',
            'emi_number' => 'nullable|integer',
        ]);

        $initialStock = (int) $validated['current_stock'];

        // ❗ remove stock before creating item
        unset($validated['current_stock']);

        $item = Item::create($validated);

      // Handle initial stock
        $branchId = Auth::user()->branch_id ?: (\App\Models\Branch::first()?->id);
        
        
        if ($initialStock > 0 && $branchId) {
            BranchStock::create([
                'branch_id' => $branchId,
                'item_id'   => $item->id,
                'quantity'  => $initialStock,
            ]);

            StockMovement::create([
                'item_id'       => $item->id,
                'branch_id'     => $branchId,
                'type'          => 'adjustment',
                'quantity'      => $initialStock,
                'balance_after' => $initialStock,
                'notes'         => 'Initial stock when creating item',
                'created_by'    => Auth::id(),
            ]);
        }

        AuditLog::log('item_created', "Item '{$item->name}' created", $item);

        // Determine route prefix
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.items.index')
            ->with('success', 'Item created successfully.');
    }


    public function show(Item $item)
    {
        $item->load(['category', 'supplier', 'stockMovements.creator']);
    
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
    
        // Get branch_id from the authenticated user's session safely
        $branchId = auth()->user()?->branch_id ?: \App\Models\Branch::first()?->id;
    
        if ($branchId) {
            // Get current stock for the branch
            $currentStock = $item->getBranchStock($branchId);
            // Check if the item is in low stock for the branch
            $isLowStock = $item->isLowStock($branchId);
        } else {
            // Default values for users without a branch
            $currentStock = 0;
            $isLowStock = false;
        }
    
        return view('admin.items.show', compact('item', 'routePrefix', 'currentStock', 'isLowStock'));
    }

    public function edit(Item $item)
    {
        $categories = Category::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.items.edit', compact('item', 'categories', 'suppliers', 'routePrefix'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|unique:items,item_code,' . $item->id,
         
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_of_measure' => 'required|string|in:pcs',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
          
            'supplier_id' => 'nullable|exists:suppliers,id',
            'is_active' => 'boolean',
            'requires_serial_number' => 'nullable|boolean',
            'warranty_duration_months' => 'nullable|integer|min:0',
            'emi_lock_mode' => 'nullable|string',
            'emi_number' => 'nullable|integer',
        ]);

        $oldValues = $item->toArray();

        // Handle stock update
      $branchId = Auth::user()->branch_id ?: (\App\Models\Branch::first()?->id);
        if ($branchId) {
            $branchStock = BranchStock::firstOrNew(['branch_id' => $branchId, 'item_id' => $item->id]);
            $oldStock = $branchStock->quantity ?? 0;
            $newStock = (int)$validated['current_stock'];

            if ($oldStock != $newStock) {
                $branchStock->quantity = $newStock;
                $branchStock->save();

                $quantityChange = $newStock - $oldStock;

                StockMovement::create([
                    'item_id' => $item->id,
                    'branch_id' => $branchId,
                    'type' => 'adjustment',
                    'quantity' => $quantityChange,
                    'balance_after' => $newStock,
                    'notes' => 'Stock updated from item edit page',
                    'created_by' => Auth::id(),
                ]);
            }
        }
        
        // Log price change if selling price changed
        if ($oldValues['selling_price'] != $validated['selling_price']) {
            AuditLog::log('price_change', "Item '{$item->name}' price changed from Rs. {$oldValues['selling_price']} to Rs. {$validated['selling_price']}", $item, ['selling_price' => $oldValues['selling_price']], ['selling_price' => $validated['selling_price']]);
        }
        
        $validated['requires_serial_number'] = $request->has('requires_serial_number');
        
        // Remove current_stock from validated data before updating item
        unset($validated['current_stock']);
        
        $item->update($validated);
        
        AuditLog::log('item_updated', "Item '{$item->name}' updated", $item, $oldValues, $item->toArray());

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return redirect()->route($routePrefix . '.items.index')
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        if ($item->saleItems()->count() > 0 || $item->grnItems()->count() > 0) {
            $item->update(['is_active' => false]);
            return redirect()->route('admin.items.index')
                ->with('success', 'Item deactivated (cannot delete items with transaction history).');
        }

        $itemName = $item->name;
        $item->delete();
        
        AuditLog::log('item_deleted', "Item '{$itemName}' deleted", null);

        return redirect()->route('admin.items.index')
            ->with('success', 'Item deleted successfully.');
    }

    public function stockHistory(Item $item)
    {
        $movements = StockMovement::where('item_id', $item->id)
            ->with('creator')
            ->latest()
            ->paginate(20);
        
        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';
        
        return view('admin.items.stock-history', compact('item', 'movements', 'routePrefix'));
    }

    public function stockHistoryApi(Item $item)
    {
        // Get the latest movements (last 20)
        $movements = StockMovement::where('item_id', $item->id)
            ->with('creator')
            ->latest()
            ->limit(20)
            ->get();
        
        // Refresh item to get latest stock
        $item->refresh();
        
        return response()->json([
            'current_stock' => $item->current_stock,
            'formatted_stock' => $item->formatted_stock,
            'unit_of_measure' => $item->unit_of_measure,
            'movements' => $movements->map(function($movement) use ($item) {
                return [
                    'id' => $movement->id,
                    'type' => $movement->type,
                    'quantity' => $movement->quantity,
                    'balance_after' => $movement->balance_after,
                    'notes' => $movement->notes,
                    'created_at' => $movement->created_at->format('Y-m-d H:i'),
                    'created_at_timestamp' => $movement->created_at->timestamp,
                    'creator_name' => $movement->creator->name ?? 'System',
                ];
            }),
        ]);
    }

    public function stockAdjustment(Request $request, Item $item)
    {
        $itemUnit = $item->unit_of_measure;
        
        // Validate common fields
        $validated = $request->validate([
            'adjustment_type' => 'required|in:adjustment,expire,loss,stock_take',
            'adjustment_quantity' => 'required|numeric',
            'adjustment_quantity_unit' => 'required|in:kg,g,kg_g',
            'adjustment_notes' => 'required|string|min:10',
            'adjustment_quantity_kg_single' => 'nullable|numeric',
            'adjustment_quantity_g_single' => 'nullable|numeric',
            'adjustment_quantity_kg' => 'nullable|numeric|min:0',
            'adjustment_quantity_g' => 'nullable|numeric|min:0|max:999',
        ]);
        
        $quantityUnit = $validated['adjustment_quantity_unit'];
        $adjustmentQuantity = 0;
        $displayQuantity = '';
        
        // Calculate adjustment quantity based on selected unit
        if ($quantityUnit === 'kg') {
            $kgValue = $request->input('adjustment_quantity_kg_single', 0);
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
            $gValue = $request->input('adjustment_quantity_g_single', 0);
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
            $kg = $request->input('adjustment_quantity_kg', 0);
            $g = $request->input('adjustment_quantity_g', 0);
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
        
        // Validate that quantity is not zero
        if ($adjustmentQuantity == 0) {
            return back()->withErrors(['adjustment_quantity' => 'Please enter a non-zero quantity.'])->withInput();
        }

        $oldStock = $item->current_stock;
        $quantityChange = $adjustmentQuantity;
        
        // Handle adjustment type
        if (in_array($validated['adjustment_type'], ['expire', 'loss', 'stock_take'])) {
            // These types always reduce stock
            $quantityChange = -abs($quantityChange);
        }
        
        $newStock = $oldStock + $quantityChange;
        
        if ($newStock < 0) {
            // Format current stock for error message
            $currentStockDisplay = $this->formatStockDisplay($oldStock, $itemUnit);
            return back()->withErrors(['adjustment_quantity' => 'Stock adjustment would result in negative stock. Current stock: ' . $currentStockDisplay])->withInput();
        }

        // Update item stock
        $item->update(['current_stock' => $newStock]);

        // Record stock movement (store in item's unit)
        $branchId = auth()->user()->branch_id ?: (\App\Models\Branch::first()?->id);
        StockMovement::create([
            'item_id' => $item->id,
            'branch_id' => $branchId,
            'type' => $validated['adjustment_type'],
            'quantity' => $quantityChange,
            'balance_after' => $newStock,
            'notes' => $validated['adjustment_notes'] . ' (Adjusted: ' . $displayQuantity . ')',
            'created_by' => auth()->id(),
        ]);

        AuditLog::log('stock_adjustment', "Stock adjustment for '{$item->name}': {$validated['adjustment_type']}, Quantity: {$displayQuantity} ({$quantityChange} {$itemUnit}), Notes: {$validated['adjustment_notes']}", $item);

        return redirect()->route('admin.items.edit', $item)
            ->with('success', 'Stock adjustment recorded successfully.');
    }
    
    private function formatStockDisplay($stock, $unit)
    {
        // For kg/g, format as "X kg Y g"
        $wholeKg = floor($stock);
        $remainingGrams = round(($stock - $wholeKg) * 1000);
        
        if ($unit === 'g') {
            $wholeKg = floor($stock / 1000);
            $remainingGrams = round($stock % 1000);
        }
        
        if ($wholeKg > 0 && $remainingGrams > 0) {
            return $wholeKg . ' kg ' . $remainingGrams . ' g';
        } elseif ($wholeKg > 0) {
            return $wholeKg . ' kg';
        } elseif ($remainingGrams > 0) {
            return $remainingGrams . ' g';
        } else {
            return '0 g';
        }
    }
}

