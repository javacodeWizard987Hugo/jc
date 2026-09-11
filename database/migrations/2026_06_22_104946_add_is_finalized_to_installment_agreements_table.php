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
            $table->boolean('is_finalized')->default(false)->after('status');
        });
        
        // Finalize existing agreements
        \Illuminate\Support\Facades\DB::table('installment_agreements')->update(['is_finalized' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installment_agreements', function (Blueprint $table) {
            $table->dropColumn('is_finalized');
        });
    }
};
