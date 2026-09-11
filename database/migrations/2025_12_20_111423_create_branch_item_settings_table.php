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
       Schema::create('branch_item_settings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
        $table->foreignId('item_id')->constrained()->cascadeOnDelete();
        $table->decimal('reorder_level', 10, 2);
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_item_settings');
    }
};
