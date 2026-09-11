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
       
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('serial_number_id');
            $table->unsignedBigInteger('sale_item_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();

            $table->date('start_date');
            $table->integer('duration'); // months
            $table->date('expiry_date');

            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('serial_number_id')
                ->references('id')->on('serial_numbers')
                ->onDelete('cascade');

            $table->foreign('sale_item_id')
                ->references('id')->on('sale_items')
                ->onDelete('set null');

            $table->foreign('customer_id')
                ->references('id')->on('customers')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranties');
    }
};
