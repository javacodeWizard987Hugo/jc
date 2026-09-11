<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InstallmentAgreement extends Model
{
    protected $fillable = [
        'sale_id',
        'agreement_number',
        'customer_id',
        'total_invoice_value',
        'down_payment_amount',
        'down_payment_date',
        'balance_amount',
        'number_of_installments',
        'monthly_installment_amount',

        // âœ… existing fields â€“ DO NOT CHANGE DB
        'first_due_date',
        'due_day_of_month',

        'interest_service_charge',
        'status',
        'is_finalized',

        'guarantor_name',
        'guarantor_nic',
        'guarantor_address',
        'guarantor_mobile_number',
        'loan_tenor',

        // New Proposal Fields
        'customer_age',
        'customer_occupation',
        'customer_institute_name_address',
        'customer_monthly_salary',
        'customer_bank_branch',

        'guarantor_2_name',
        'guarantor_2_nic',
        'guarantor_2_address',
        'guarantor_2_phone',
        'guarantor_2_occupation',
        'guarantor_2_monthly_income',
        'guarantor_2_bank_branch',

        'guarantor_1_occupation',
        'guarantor_1_monthly_income',
        'guarantor_1_bank_branch',

        'otp_code',
        'otp_verified_at',
          'otp_attempts', // ðŸ‘ˆ ADD THIS
          'otp_sent_at',
        'emi_lock_mode',
        'emi_number',

        // NIC files
        'customer_nic_front',
        'customer_nic_back',
        'guarantor_nic_front',
        'guarantor_nic_back',

        'disconnected_at',
        'unlocked_at',
        'call_1_status', 'call_1_feedback',
        'call_2_status', 'call_2_feedback',
        'call_3_status', 'call_3_feedback',
        'call_4_status', 'call_4_feedback',
        'call_5_status', 'call_5_feedback',
    ];
  protected function casts(): array
    {
        return [
            'disconnected_at' => 'datetime',
            'unlocked_at' => 'datetime',
             'otp_sent_at' => 'datetime',
            'otp_verified_at' => 'datetime',
        ];
    }

    /* =========================
     |  RELATIONSHIPS
     ========================= */

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(InstallmentPayment::class);
    }

    public function getDelayDaysAttribute()
    {
        // Total delay days across all installments (FIFO logic)
        $totalAccrued = $this->total_accrued_delay_charge;
        return (int) ($totalAccrued / 100);
    }

    public function getTotalAccruedDelayChargeAttribute()
    {
        // Delay Days = Payment Date - Due Date (if paid late)
        // Delay Days = Today - Due Date (if unpaid and overdue)
        $totalCharge = 0;
        $monthlyAmount = round((float)$this->monthly_installment_amount, 2);
        if ($monthlyAmount <= 0) return 0;

        $payments = $this->payments->sortBy('payment_date')->values();
        $totalInstallments = $this->total_installments;
        
        $paymentIndex = 0;
        $paymentOffset = 0;

        for ($i = 0; $i < $totalInstallments; $i++) {
            $dueDate = Carbon::parse($this->first_due_date)->addMonths($i);
            $dueDate = $this->applyDueDayOfMonth($dueDate);
            
            $dueForThisMonth = $monthlyAmount;
            $lastPaymentDate = null;
            $fullyPaidThisInstallment = false;

            while ($dueForThisMonth > 0.009 && $paymentIndex < count($payments)) {
                $p = $payments[$paymentIndex];
                $availableInPayment = round($p->amount - $paymentOffset, 2);
                $toTake = min($dueForThisMonth, $availableInPayment);
                
                $dueForThisMonth = round($dueForThisMonth - $toTake, 2);
                $paymentOffset = round($paymentOffset + $toTake, 2);
                $lastPaymentDate = $p->payment_date;

                if ($paymentOffset >= round($p->amount, 2) - 0.009) {
                    $paymentIndex++;
                    $paymentOffset = 0;
                }
                
                if ($dueForThisMonth <= 0.009) {
                    $fullyPaidThisInstallment = true;
                }
            }

            if ($fullyPaidThisInstallment && $lastPaymentDate) {
                // Paid late?
                if ($lastPaymentDate->startOfDay()->gt($dueDate->startOfDay())) {
                    $days = (int) $dueDate->startOfDay()->diffInDays($lastPaymentDate->startOfDay());
                    $totalCharge += $days * 100;
                }
            } elseif (!$fullyPaidThisInstallment) {
                // Unpaid and overdue?
                if (now()->startOfDay()->gt($dueDate->startOfDay())) {
                    $days = (int) $dueDate->startOfDay()->diffInDays(now()->startOfDay());
                    $totalCharge += $days * 100;
                }
            }
        }

        return (float) $totalCharge;
    }

    public function getCalculatedFineAttribute()
    {
        return $this->total_accrued_delay_charge;
    }

    public function getTotalFinePaidAttribute()
    {
        return (float) $this->payments->sum('fine_amount');
    }

    public function getRemainingFineAttribute()
    {
        return max(0, round($this->total_accrued_delay_charge - $this->total_fine_paid, 2));
    }

    /* =========================
     |  CORE EMI LOGIC
     ========================= */

    /**
     * âœ… Get NEXT installment due date
     *
     * Rules:
     * - Down payment date is ignored
     * - First installment = first_due_date
     * - After payment â†’ +1 month from LAST DUE DATE
     * - Uses due_day_of_month for consistency
     */

    
public function getNextDueDate($asOfDate = null)
{
    // If no asOfDate provided, use all payments (current logic)
    if (!$asOfDate) {
        $totalPaid = $this->payments->sum('amount');
    } else {
        // For historical calculation, we only consider payments made strictly BEFORE or ON that date
        // But for a receipt, we want to know what the next due date was AFTER that specific payment was recorded.
        // So we include payments up to that specific payment's created_at or payment_date.
        $asOfDate = Carbon::parse($asOfDate);
        $totalPaid = $this->payments()
            ->where('payment_date', '<=', $asOfDate->toDateString())
            ->where('id', '<=', function($query) use ($asOfDate) {
                // This is a bit tricky if multiple payments on same day. 
                // In context of a receipt, we might pass the payment object itself or its ID.
            })
            ->sum('amount');
            
        // Simpler for Receipt logic: let's allow passing total paid directly if needed,
        // or just calculate based on the date.
    }

    if ($this->monthly_installment_amount <= 0) {
        return \Carbon\Carbon::parse($this->first_due_date);
    }
    
    $paidCount = floor(round($totalPaid / $this->monthly_installment_amount, 5));

    $nextDate = \Carbon\Carbon::parse($this->first_due_date)->addMonths((int)$paidCount);
    
    return $this->applyDueDayOfMonth($nextDate);
}

public function getNextDueDateAsOfPayment($payment)
{
    if (!$payment) return $this->getNextDueDate();
    
    // Sum all payments made up to and including this one
    $totalPaid = $this->payments()
        ->where(function($q) use ($payment) {
            $q->where('payment_date', '<', $payment->payment_date)
              ->orWhere(function($sq) use ($payment) {
                  $sq->where('payment_date', '=', $payment->payment_date)
                    ->where('id', '<=', $payment->id);
              });
        })
        ->sum('amount');

    if ($this->monthly_installment_amount <= 0) {
        return \Carbon\Carbon::parse($this->first_due_date);
    }
    
    $paidCount = floor(round($totalPaid / $this->monthly_installment_amount, 5));

    $nextDate = \Carbon\Carbon::parse($this->first_due_date)->addMonths((int)$paidCount);
    
    return $this->applyDueDayOfMonth($nextDate);
}

    /**
     * âœ… Get total number of installments (initial)
     */
    public function getTotalInstallmentsAttribute()
    {
        $monthly = round((float)$this->monthly_installment_amount, 2);
        if ($monthly <= 0) {
            return (int)($this->loan_tenor ?: $this->number_of_installments);
        }

        $initialLoan = round((float)$this->total_invoice_value - (float)$this->down_payment_amount, 2);
        if ($initialLoan <= 0) return 0;

        $calculatedCount = (int) ceil(round($initialLoan / $monthly, 5));
        
        return max($calculatedCount, (int)($this->loan_tenor ?? 0));
    }

    /**
     * âœ… Apply due_day_of_month safely
     * Handles Feb / 30 / 31 correctly
     */

public function applyDueDayOfMonth(Carbon $date): Carbon
{
    $dueDay = (int) $this->due_day_of_month;
    $maxDay = $date->daysInMonth;

    if ($dueDay > $maxDay) {
        $dueDay = $maxDay;
    }

    return $date->copy()->setDay($dueDay);
}


    /* =========================
     |  ACCESSORS
     ========================= */

    /**
     * Optional helper for UI / SMS
     */
    public function getNextDueDateAttribute()
    {
        if ($this->status === 'paid_off' || $this->balance_amount <= 0) {
            return 'Completed';
        }
        return $this->getNextDueDate();
    }

    /* =========================
     |  SCOPES
     ========================= */

    public function scopeOverdue($query)
    {
        // For performance we use a simple date check here, 
        // but ideally it should match the logic in InstallmentController
        return $query
            ->where('status', '!=', 'paid_off')
            ->where('balance_amount', '>', 0)
            ->whereDate('first_due_date', '<', now());
    }
    
    public function scopeDueTomorrow($query)
{
    // next_due_date is an accessor, cannot use whereDate on it.
    return $query->where('status', 'active')
        ->where('balance_amount', '>', 0);
}


    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('emi_number', 'like', "%{$search}%")
                ->orWhere('guarantor_name', 'like', "%{$search}%")
                ->orWhere('guarantor_nic', 'like', "%{$search}%")
                ->orWhere('guarantor_mobile_number', 'like', "%{$search}%")
                ->orWhere('guarantor_address', 'like', "%{$search}%")
                ->orWhere('guarantor_2_name', 'like', "%{$search}%")
                ->orWhere('guarantor_2_nic', 'like', "%{$search}%")
                ->orWhere('guarantor_2_phone', 'like', "%{$search}%")
                ->orWhere('guarantor_2_address', 'like', "%{$search}%")
                ->orWhereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('nic', 'like', "%{$search}%")
                        ->orWhere('emi_number', 'like', "%{$search}%");
                })
                ->orWhereHas('sale', function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('items.item', function ($iq) use ($search) {
                            $iq->where('name', 'like', "%{$search}%")
                                ->orWhere('emi_number', 'like', "%{$search}%");
                        });
                });
        });
    }

    
  public function getCombinedEmiNumbersAttribute(): string
    {
        $emiNumbers = [];
        if ($this->emi_number) {
            $emiNumbers[] = $this->emi_number;
        }
        
        if ($this->sale && $this->sale->items) {
            foreach ($this->sale->items as $saleItem) {
                if ($saleItem->item && $saleItem->item->emi_number && !in_array($saleItem->item->emi_number, $emiNumbers)) {
                    $emiNumbers[] = $saleItem->item->emi_number;
                }
            }
        }
        
        return empty($emiNumbers) ? 'N/A' : implode(', ', $emiNumbers);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'paid_off' || $this->balance_amount <= 0.009) {
            return 'Paid Off';
        }

        if ($this->unlocked_at) {
            return 'Unlocked';
        }

        if ($this->disconnected_at || $this->delay_days > 0) {
            return 'Disconnected';
        }

        return 'Normal';
    }
    public function getOverdueInstallmentAmountAttribute()
    {
        $monthlyAmount = round((float)($this->monthly_installment_amount ?? 0), 2);
        if ($monthlyAmount <= 0) return 0;
        if (empty($this->first_due_date)) return 0;

        $totalPaid = round((float)$this->payments->sum('amount'), 2);
        
        $now = now()->startOfDay();
        $firstDueDate = Carbon::parse($this->first_due_date)->startOfDay();
        
        if ($now->lt($firstDueDate)) {
            return 0;
        }

        // Calculate how many installments should have been paid by today
        $installmentsDueCount = 0;
        $totalPotentialInstallments = (int)$this->total_installments;
        if ($totalPotentialInstallments <= 0) $totalPotentialInstallments = 120; // Safe fallback

        for ($i = 0; $i < $totalPotentialInstallments; $i++) {
            $dueDate = $firstDueDate->copy()->addMonths($i);
            $dueDate = $this->applyDueDayOfMonth($dueDate)->startOfDay();
            
            if ($dueDate->lte($now)) {
                $installmentsDueCount++;
            } else {
                break;
            }
        }

        $totalExpected = round($installmentsDueCount * $monthlyAmount, 2);
        $overdue = max(0.0, round($totalExpected - $totalPaid, 2));
        
        return (float) min($overdue, $this->balance_amount);
    }
}
