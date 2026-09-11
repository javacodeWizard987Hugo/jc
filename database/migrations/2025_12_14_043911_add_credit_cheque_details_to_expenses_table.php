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
        Schema::table('expenses', function (Blueprint $table) {
            $table->date('credit_repay_date')->nullable()->after('payment_method');
            $table->string('cheque_number')->nullable()->after('credit_repay_date');
            $table->string('cheque_bank')->nullable()->after('cheque_number');
            $table->date('cheque_date')->nullable()->after('cheque_bank');
            $table->string('account_holder_name')->nullable()->after('cheque_date');
            $table->string('account_number')->nullable()->after('account_holder_name');
            $table->string('account_bank')->nullable()->after('account_number');
            $table->string('account_branch')->nullable()->after('account_bank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn([
                'credit_repay_date',
                'cheque_number',
                'cheque_bank',
                'cheque_date',
                'account_holder_name',
                'account_number',
                'account_bank',
                'account_branch'
            ]);
        });
    }
};
