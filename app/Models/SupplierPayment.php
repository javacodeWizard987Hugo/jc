<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $fillable = [
        'supplier_id',
        'grn_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'invoice_amount',
        'paid_amount',
        'outstanding_amount',
        'payment_method',
        'credit_repay_date',
        'cheque_number',
        'bank_name',
        'cheque_date',
        'cheque_repay_date',
        'account_holder_name',
        'account_number',
        'account_bank',
        'account_branch',
        'payment_date',
        'paid_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'credit_repay_date' => 'date',
            'cheque_date' => 'date',
            'cheque_repay_date' => 'date',
            'payment_date' => 'date',
            'invoice_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'outstanding_amount' => 'decimal:2',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function grn()
    {
        return $this->belongsTo(Grn::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
