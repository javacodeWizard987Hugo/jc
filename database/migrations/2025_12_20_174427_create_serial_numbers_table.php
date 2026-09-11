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
        Schema::create('serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('grn_item_id')->nullable();
            $table->unsignedBigInteger('sale_item_id')->nullable();
            $table->unsignedBigInteger('stock_transfer_item_id')->nullable();
            $table->enum('status', ['available', 'sold', 'in_transit', 'returned', 'defective'])->default('available');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('grn_item_id')->references('id')->on('grn_items')->onDelete('set null');
            $table->foreign('sale_item_id')->references('id')->on('sale_items')->onDelete('set null');
            $table->foreign('stock_transfer_item_id')->references('id')->on('stock_transfer_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serial_numbers');
    }
};
