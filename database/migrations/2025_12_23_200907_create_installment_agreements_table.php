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
        Schema::create('installment_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales');
            $table->foreignId('customer_id')->constrained('customers');
            $table->decimal('total_invoice_value', 10, 2);
            $table->decimal('down_payment_amount', 10, 2);
            $table->date('down_payment_date');
            $table->decimal('balance_amount', 10, 2);
            $table->integer('number_of_installments');
            $table->decimal('monthly_installment_amount', 10, 2);
            $table->date('first_due_date');
            $table->integer('due_day_of_month');
            $table->decimal('interest_service_charge', 10, 2)->nullable();
            $table->string('status')->default('active'); // e.g., active, paid_off, overdue
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_agreements');
    }
};
