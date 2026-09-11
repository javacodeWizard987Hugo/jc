<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    protected $fillable = [
        'grn_id',
        'item_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'expiry_date' => 'date',
        ];
    }

    public function grn()
    {
        return $this->belongsTo(Grn::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
