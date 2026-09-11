<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
    {
        DB::table('sms_templates')->updateOrInsert(
            ['event_name' => 'installment_payment_received'],
            [
                'template' => 'Dear {CustomerName}, We received Rs. {Amount} for installment and Rs. {FineAmount} for delay charge on {Date}. Your current balance is Rs. {Balance}. Next due date: {NextDueDate}. Status: {LockStatus}. Thank you!',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('sms_templates')->updateOrInsert(
            ['event_name' => 'device_unlocked_notification'],
            [
                'template' => 'Dear {CustomerName}, your device (EMI: {EmiNumber}) has been successfully UNLOCKED on {UnlockDate}. Thank you for your payment!',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
};
