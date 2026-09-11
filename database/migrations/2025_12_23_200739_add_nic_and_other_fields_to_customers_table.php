
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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('nic')->unique()->nullable()->after('name');
            $table->json('mobile_numbers')->nullable()->after('phone');
            $table->foreignId('primary_branch_id')->nullable()->constrained('branches')->after('address');
            $table->boolean('promotional_sms_opt_in')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('nic');
            $table->dropColumn('mobile_numbers');
            $table->dropForeign(['primary_branch_id']);
            $table->dropColumn('primary_branch_id');
            $table->dropColumn('promotional_sms_opt_in');
        });
    }
};
