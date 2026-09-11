<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    protected $fillable = [
        'grn_number',
        'supplier_id',
        'grn_date',
        'reference_document',
        'total_amount',
        'created_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'grn_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(GrnItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
