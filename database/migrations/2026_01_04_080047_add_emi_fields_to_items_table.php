<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('emi_lock_mode')->nullable()->after('is_active');
            $table->string('emi_number')->nullable()->after('emi_lock_mode');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['emi_lock_mode', 'emi_number']);
        });
    }
};
