<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support MODIFY COLUMN or ENUM constraints
        // Since SQLite stores ENUM as text and doesn't enforce constraints,
        // we can skip this migration for SQLite. The application code handles validation.
        if (DB::getDriverName() !== 'sqlite') {
            // Modify enum to add 'credit' option (for MySQL/MariaDB)
            DB::statement("ALTER TABLE supplier_payments MODIFY COLUMN payment_method ENUM('cash', 'cheque', 'bank_transfer', 'credit', 'other') DEFAULT 'cash'");
        }
        // For SQLite, no action needed - the column already accepts any text value
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite doesn't support MODIFY COLUMN
        if (DB::getDriverName() !== 'sqlite') {
            // Revert enum to remove 'credit' option (for MySQL/MariaDB)
            DB::statement("ALTER TABLE supplier_payments MODIFY COLUMN payment_method ENUM('cash', 'cheque', 'bank_transfer', 'other') DEFAULT 'cash'");
        }
        // For SQLite, no action needed
    }
};
