<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'nic',
        'phone',
        'mobile_numbers',
        'address',
        'primary_branch_id',
        'credit_limit',
        'outstanding_balance',
        'is_active',
        'promotional_sms_opt_in',
        'emi_lock_mode',
        'emi_number',
        'guarantor_name',
        'guarantor_nic',
        'guarantor_mobile_number',
        'guarantor_address',
         // New extra fields
        'customer_age',
        'customer_occupation',
        'customer_institute_name_address',
        'customer_monthly_salary',
        'customer_bank_branch',

        'guarantor_1_occupation',
        'guarantor_1_monthly_income',
        'guarantor_1_bank_branch',

        'guarantor_2_name',
        'guarantor_2_nic',
        'guarantor_2_address',
        'guarantor_2_phone',
        'guarantor_2_occupation',
        'guarantor_2_monthly_income',
        'guarantor_2_bank_branch',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'is_active' => 'boolean',
            'promotional_sms_opt_in' => 'boolean',
            'mobile_numbers' => 'array',
        ];
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function credits()
    {
        return $this->hasMany(CustomerCredit::class);
    }

    public function installmentAgreements()
    {
        return $this->hasMany(InstallmentAgreement::class);
    }

    public function getIsOverdueAttribute()
    {
        return $this->installmentAgreements()->overdue()->exists();
    }
}
