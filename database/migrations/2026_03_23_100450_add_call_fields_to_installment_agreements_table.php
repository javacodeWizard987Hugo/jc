<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('installment_agreements', function (Blueprint $table) {
            $table->timestamp('disconnected_at')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            
            // Call 1
            $table->string('call_1_status')->nullable();
            $table->text('call_1_feedback')->nullable();
            
            // Call 2
            $table->string('call_2_status')->nullable();
            $table->text('call_2_feedback')->nullable();
            
            // Call 3
            $table->string('call_3_status')->nullable();
            $table->text('call_3_feedback')->nullable();
            
            // Call 4
            $table->string('call_4_status')->nullable();
            $table->text('call_4_feedback')->nullable();
            
            // Call 5
            $table->string('call_5_status')->nullable();
            $table->text('call_5_feedback')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installment_agreements', function (Blueprint $table) {
            $table->dropColumn([
                'disconnected_at',
                'unlocked_at',
                'call_1_status', 'call_1_feedback',
                'call_2_status', 'call_2_feedback',
                'call_3_status', 'call_3_feedback',
                'call_4_status', 'call_4_feedback',
                'call_5_status', 'call_5_feedback',
            ]);
        });
    }
};
