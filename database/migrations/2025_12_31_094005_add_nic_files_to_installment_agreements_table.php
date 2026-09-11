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

            // Customer NIC
            $table->string('customer_nic_front')->nullable()->after('guarantor_address');
            $table->string('customer_nic_back')->nullable()->after('customer_nic_front');

            // Guarantor NIC
            $table->string('guarantor_nic_front')->nullable()->after('customer_nic_back');
            $table->string('guarantor_nic_back')->nullable()->after('guarantor_nic_front');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installment_agreements', function (Blueprint $table) {
            $table->dropColumn([
                'customer_nic_front',
                'customer_nic_back',
                'guarantor_nic_front',
                'guarantor_nic_back',
            ]);
        });
    }
};
