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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained()->onDelete('restrict');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'bank', 'cheque', 'other'])->default('cash');
            $table->date('expense_date');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->boolean('is_recurring')->default(false);
            $table->integer('recurring_days')->nullable(); // e.g., 30 for monthly
            $table->date('next_due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
