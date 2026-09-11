<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeldBill extends Model
{
    protected $fillable = [
        'cashier_id',
        'bill_data',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'bill_data' => 'array',
            'total_amount' => 'decimal:2',
        ];
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
