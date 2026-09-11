<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'address',
        'contact_person',
        'phone',
        'email',
        'payment_terms',
        'outstanding_balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'outstanding_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function grns()
    {
        return $this->hasMany(Grn::class);
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
