<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'cashier_id',
'branch_id',
        'customer_id',
        'subtotal',
        'discount_amount',
        'discount_type',
        'tax_amount',
        'total_amount',
        'payment_method',
        'status',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'requires_admin_approval',
        'approved_by',
        'approved_at',
        'approval_reason',
        'discount_approval_request',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
            'requires_admin_approval' => 'boolean',
        ];
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function credit()
    {
        return $this->hasOne(CustomerCredit::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function installmentAgreement()
    {
        return $this->hasOne(InstallmentAgreement::class);
    }
}
