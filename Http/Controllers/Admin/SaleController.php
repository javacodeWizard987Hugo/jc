<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function cancel(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        if ($sale->status === 'cancelled') {
            return back()->withErrors(['error' => 'Sale is already cancelled.']);
        }

        // Restore stock
        // The quantity field is already in the item's unit_of_measure format
        foreach ($sale->items as $saleItem) {
            $item = $saleItem->item;
            $stockRestoreQuantity = $saleItem->quantity;
            $weight = $saleItem->weight ?? 0;
            $weightUnit = $saleItem->weight_unit ?? $item->unit_of_measure;
            
           $branchId = $sale->branch_id; // or auth()->user()->branch_id

            $branchStock = $item->stock()
                ->where('branch_id', $branchId)
                ->first();

            if ($branchStock) {
                $branchStock->increment('quantity', $stockRestoreQuantity);
            } else {
                $branchStock = $item->stock()->create([
                    'branch_id' => $branchId,
                    'quantity' => $stockRestoreQuantity,
                ]);
            }

            $newStock = $branchStock->quantity;

            
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
            
            StockMovement::create([
                'item_id' => $item->id,
                 'branch_id' => $branchId,
                'type' => 'return',
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'quantity' => $stockRestoreQuantity,
                'balance_after' => $newStock,
                'notes' => "Sale cancelled by admin: {$sale->invoice_number}. Reason: {$validated['reason']}{$weightDisplay}",
                'created_by' => auth()->id(),
            ]);
        }

        $sale->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['reason'],
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
        ]);

        AuditLog::log('sale_cancelled', "Sale cancelled by admin: {$sale->invoice_number}. Reason: {$validated['reason']}", $sale, null, ['reason' => $validated['reason']]);

        return back()->with('success', 'Sale cancelled successfully. Stock has been restored.');
    }
}
