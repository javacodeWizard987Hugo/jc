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
            //
              $table->string('emi_lock_mode')->nullable();
    $table->string('emi_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installment_agreements', function (Blueprint $table) {
            //
        });
    }
};
