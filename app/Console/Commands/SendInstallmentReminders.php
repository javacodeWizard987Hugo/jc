<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InstallmentAgreement;
use App\Services\SmsService;
use Carbon\Carbon;

class SendInstallmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-installment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS reminders to customers with upcoming installment payments.';

    /**
     * Execute the console command.
     */
    public function handle(SmsService $smsService)
    {
        $this->info('Sending installment reminders...');

        $reminderDate = Carbon::today()->addDays(3)->toDateString();

      $agreements = InstallmentAgreement::where('status', '!=', 'paid_off')
            ->where('balance_amount', '>', 0)
            ->with('customer')
            ->get()
            ->filter(function ($agreement) use ($reminderDate) {
                $nextDueDate = $agreement->next_due_date;
                return ($nextDueDate instanceof Carbon) 
                    ? $nextDueDate->toDateString() === $reminderDate 
                    : $nextDueDate === $reminderDate;
            });

        if ($agreements->isEmpty()) {
            $this->info('No installments due for reminder today.');
            return;
        }

        foreach ($agreements as $agreement) {
            $dueDate = $agreement->next_due_date;
            $dueDateStr = ($dueDate instanceof Carbon) ? $dueDate->toDateString() : $dueDate;

            $smsService->sendSms(
                $agreement->customer->phone,
                'due_date_reminder',
                [
                    'CustomerName' => $agreement->customer->name,
                    'DueDate' => $dueDateStr,
                    'RemainingBalance' => number_format($agreement->balance_amount, 2),
                    'MonthlyPayment' => number_format($agreement->monthly_installment_amount, 2),
                ]
            );
        }

        $this->info("Sent {$agreements->count()} reminder(s).");
    }
}
