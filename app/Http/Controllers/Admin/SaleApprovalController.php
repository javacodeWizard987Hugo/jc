<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SaleApprovalController extends Controller
{
    public function index()
    {
        $pendingSales = Sale::where('requires_admin_approval', true)
            ->where('status', 'pending')
            ->with(['cashier', 'items.item'])
            ->latest()
            ->paginate(20);
        
        return view('admin.sale-approvals.index', compact('pendingSales'));
    }

    public function approve(Request $request, Sale $sale)
    {
        if (!$sale->requires_admin_approval || $sale->status !== 'pending') {
            return back()->withErrors(['error' => 'This sale does not require approval or is not pending.']);
        }

        $validated = $request->validate([
            'approval_reason' => 'nullable|string|max:500',
        ]);

        // Update stock (was not updated during creation)
        // The quantity field is already in the item's unit_of_measure format
        foreach ($sale->items as $saleItem) {
            $item = $saleItem->item;
            $stockReductionQuantity = $saleItem->quantity;
            $weight = $saleItem->weight ?? 0;
            $weightUnit = $saleItem->weight_unit ?? $item->unit_of_measure;
            
            // Check stock availability before reducing
            $branchId = $sale->cashier->branch_id; // Assuming cashier is associated with a branch
            $branchStock = \App\Models\BranchStock::where('branch_id', $branchId)->where('item_id', $item->id)->first();

            if (!$branchStock || $branchStock->quantity < $stockReductionQuantity) {
                $availableStock = \App\Models\Item::formatStock($branchStock ? $branchStock->quantity : 0, $item->unit_of_measure);
                $requestedStock = \App\Models\Item::formatStock($stockReductionQuantity, $item->unit_of_measure);
                return back()->withErrors(['error' => "Insufficient stock for {$item->name}. Available: {$availableStock}, Requested: {$requestedStock}"]);
            }
            
            $branchStock->decrement('quantity', $stockReductionQuantity);
            $newStock = $branchStock->fresh()->quantity;
            
            // Format weight display for notes
            $weightDisplay = '';
            if ($weight > 0) {
                if ($weightUnit === 'kg' || $weightUnit === 'kg_g') {
                    $wholeKg = floor($weight);
                    $remainingGrams = round(($weight - $wholeKg) * 1000);
                    $gFormatted = str_pad($remainingGrams, 3, '0', STR_PAD_LEFT);
                    if ($wholeKg > 0 || $remainingGrams > 0) {
                        $weightDisplay = " (Weight: {$wholeKg}kg {$gFormatted}g)";
                    }
                } elseif ($weightUnit === 'g') {
                    $wholeKg = floor($weight / 1000);
                    $remainingGrams = round($weight % 1000);
                    $gFormatted = str_pad($remainingGrams, 3, '0', STR_PAD_LEFT);
                    if ($wholeKg > 0 || $remainingGrams > 0) {
                        $weightDisplay = " (Weight: {$wholeKg}kg {$gFormatted}g)";
                    }
                } else {
                    $weightDisplay = " (Weight: {$weight} {$weightUnit})";
                }
            }
            
            // Record stock movement
            StockMovement::create([
                'item_id' => $item->id,
                'branch_id' => $branchId,
                'type' => 'sale',
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'quantity' => -$stockReductionQuantity,
                'balance_after' => $newStock,
                'notes' => "Sale approved: {$sale->invoice_number}{$weightDisplay}",
                'created_by' => auth()->id(),
            ]);
        }

        $sale->update([
            'status' => 'completed',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_reason' => $validated['approval_reason'] ?? 'Approved by admin',
        ]);

        AuditLog::log('sale_approved', "Sale approved: {$sale->invoice_number}. Reason: " . ($validated['approval_reason'] ?? 'Approved'), $sale);

        return redirect()->route('admin.sale-approvals.index')
            ->with('success', "Sale {$sale->invoice_number} approved successfully.");
    }

    public function reject(Request $request, Sale $sale)
    {
        if (!$sale->requires_admin_approval || $sale->status !== 'pending') {
            return back()->withErrors(['error' => 'This sale does not require approval or is not pending.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        // Delete payments
        $sale->payments()->delete();
        
        // Delete sale items
        $sale->items()->delete();
        
        // Delete sale
        $invoiceNumber = $sale->invoice_number;
        $sale->delete();

        AuditLog::log('sale_rejected', "Sale rejected: {$invoiceNumber}. Reason: {$validated['rejection_reason']}", null);

        return redirect()->route('admin.sale-approvals.index')
            ->with('success', "Sale {$invoiceNumber} rejected and deleted.");
    }
}
