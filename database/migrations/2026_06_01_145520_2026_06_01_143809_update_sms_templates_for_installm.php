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
        // Update installment_payment_received template
        DB::table('sms_templates')->updateOrInsert(
            ['event_name' => 'installment_payment_received'],
            [
                'template' => 'Hello {CustomerName}, we received your installment payment of Rs {Amount} on {Date}. Remaining balance: Rs {Balance}. Next due date: {NextDueDate}. Thank you.',
                'updated_at' => now(),
            ]
        );

        // Create/Update delay_payment_received template
        DB::table('sms_templates')->updateOrInsert(
            ['event_name' => 'delay_payment_received'],
            [
                'template' => 'Dear {CustomerName}, We received Rs. {Amount} for installment and Rs. {FineAmount} for delay charge on {Date}. Your current balance is Rs. {Balance}. Next due date: {NextDueDate}. Status: {LockStatus}. Thank you!',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore old installment_payment_received template (if needed, but usually we don't go back)
        // Or just leave it as is
    }
};
