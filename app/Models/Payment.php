<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'sale_id',
        'payment_method',
        'amount',
        'cheque_number',
        'bank_name',
        'cheque_date',
        'card_reference',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'cheque_date' => 'date',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
