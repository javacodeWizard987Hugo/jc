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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['grn', 'sale', 'adjustment', 'return', 'expire', 'loss', 'stock_take'])->default('adjustment');
            $table->string('reference_type')->nullable(); // e.g., 'App\Models\Grn', 'App\Models\Sale'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity', 10, 2); // positive for increase, negative for decrease
            $table->decimal('balance_after', 10, 2); // stock balance after this movement
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
