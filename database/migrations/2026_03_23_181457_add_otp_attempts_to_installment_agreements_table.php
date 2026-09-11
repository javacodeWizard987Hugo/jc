<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('installment_agreements', function (Blueprint $table) {
        $table->integer('otp_attempts')->default(0);
    });
}

public function down(): void
{
    Schema::table('installment_agreements', function (Blueprint $table) {
        $table->dropColumn('otp_attempts');
    });
}

};
