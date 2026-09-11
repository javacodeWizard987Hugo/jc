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

        // ✅ ADD NEW FIELDS
        $table->string('customer_age')->nullable();
        $table->string('customer_occupation')->nullable();
        $table->string('customer_institute_name_address')->nullable();
        $table->string('customer_monthly_salary')->nullable();
        $table->string('customer_bank_branch')->nullable();

        $table->string('guarantor_2_name')->nullable();
        $table->string('guarantor_2_nic')->nullable();
        $table->string('guarantor_2_address')->nullable();
        $table->string('guarantor_2_phone')->nullable();
        $table->string('guarantor_2_occupation')->nullable();
        $table->string('guarantor_2_monthly_income')->nullable();
        $table->string('guarantor_2_bank_branch')->nullable();

        $table->string('guarantor_1_occupation')->nullable();
        $table->string('guarantor_1_monthly_income')->nullable();
        $table->string('guarantor_1_bank_branch')->nullable();

        $table->string('otp_code')->nullable();
        $table->timestamp('otp_verified_at')->nullable();

       
    });
}

 public function down(): void
{
    Schema::table('installment_agreements', function (Blueprint $table) {
        $table->dropColumn([
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
        ]);
    });
}

};
