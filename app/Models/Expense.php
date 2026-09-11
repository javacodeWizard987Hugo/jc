<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_category_id',
        'description',
        'amount',
        'payment_method',
        'expense_date',
        'created_by',
        'is_recurring',
        'recurring_days',
        'next_due_date',
        'credit_repay_date',
        'cheque_number',
        'cheque_bank',
        'cheque_date',
        'cheque_repay_date',
        'account_holder_name',
        'account_number',
        'account_bank',
        'account_branch',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'next_due_date' => 'date',
            'credit_repay_date' => 'date',
            'cheque_date' => 'date',
            'cheque_repay_date' => 'date',
            'is_recurring' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
