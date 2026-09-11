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
        Schema::table('installment_agreements', function (Blueprint $table) {
            $table->string('guarantor_name')->nullable()->after('status');
            $table->string('guarantor_nic')->nullable()->after('guarantor_name');
            $table->text('guarantor_address')->nullable()->after('guarantor_nic');
            $table->string('guarantor_mobile_number')->nullable()->after('guarantor_address');
            $table->integer('loan_tenor')->nullable()->after('guarantor_mobile_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installment_agreements', function (Blueprint $table) {
            $table->dropColumn('guarantor_name');
            $table->dropColumn('guarantor_nic');
            $table->dropColumn('guarantor_address');
            $table->dropColumn('guarantor_mobile_number');
            $table->dropColumn('loan_tenor');
        });
    }
};
