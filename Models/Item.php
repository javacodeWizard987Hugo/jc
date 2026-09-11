<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
   protected $fillable = [
    'item_code',
    'barcode',
    'name',
    'category_id',
    'unit_of_measure',
    'cost_price',
    'selling_price',
    'supplier_id',
    'is_active',
    'emi_lock_mode',
    'emi_number',
];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'expiry_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function grnItems()
    {
        return $this->hasMany(GrnItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    public function isNearExpiry(int $days = 30): bool
    {
        return $this->expiry_date && 
               $this->expiry_date >= now() && 
               $this->expiry_date <= now()->addDays($days);
    }

    public function stock()
    {
        return $this->hasMany(BranchStock::class);
    }

    public function getBranchStock($branchId)
    {
        $stock = $this->stock()->where('branch_id', $branchId)->first();
        return $stock ? $stock->quantity : 0;
    }

    public function isLowStock($branchId): bool
    {
        $stock = $this->getBranchStock($branchId);
        return $stock <= $this->reorder_level;
    }

    /**
     * Format stock display based on unit of measure
     * Returns formatted string like "1.5 kg", "500 g", or "1 kg 300 g"
     */
    /**
 * Get formatted stock for a branch (PCS)
 */
public function getFormattedStockAttribute($branchId): string
{
    $stock = $this->getBranchStock($branchId);
    return (int) $stock . ' PCS';
}

/**
 * Static formatter (PCS only)
 */
public static function formatStock(?float $stock, string $unit = 'pcs'): string
{
    return (int) ($stock ?? 0) . ' PCS';
}


    public function branchSettings()
{
    return $this->hasMany(BranchItemSetting::class);
}

}
