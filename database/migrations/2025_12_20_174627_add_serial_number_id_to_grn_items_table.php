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
        Schema::table('grn_items', function (Blueprint $table) {
            $table->unsignedBigInteger('serial_number_id')->nullable()->after('item_id');
            $table->foreign('serial_number_id')->references('id')->on('serial_numbers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grn_items', function (Blueprint $table) {
            $table->dropForeign(['serial_number_id']);
            $table->dropColumn('serial_number_id');
        });
    }
};
