<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentPayment extends Model
{
    protected $dates = ['payment_date'];
// OR in newer Laravel versions
protected $casts = [
    'payment_date' => 'datetime',
];

    protected $fillable = [
        'installment_agreement_id',
        'amount',
        'fine_amount',
        'payment_date',
        'payment_method',
        'notes',
        'created_by',
        'status',
        'cheque_number',
        'bank_name',
        'cheque_date',
    ];

    public function agreement()
    {
        return $this->belongsTo(InstallmentAgreement::class, 'installment_agreement_id');
    }
     public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
