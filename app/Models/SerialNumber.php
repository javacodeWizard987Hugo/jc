<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerialNumber extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_id',
        'serial_number',
        'status',
        'branch_id',
        'grn_item_id',
        'sale_item_id',
    ];

    /**
     * Get the item that owns the serial number.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the branch where the serial number is currently located.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the GRN item associated with this serial number.
     */
    public function grnItem()
    {
        return $this->belongsTo(GrnItem::class);
    }

    /**
     * Get the sale item associated with this serial number.
     */
    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }
}
