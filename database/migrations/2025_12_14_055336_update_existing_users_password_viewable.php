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
        // Update existing users with known passwords
        DB::table('users')
            ->where('email', 'admin@chickenshop.com')
            ->update(['password_viewable' => 'admin123']);
        
        DB::table('users')
            ->where('email', 'cashier@chickenshop.com')
            ->update(['password_viewable' => 'cashier123']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear password_viewable for these users
        DB::table('users')
            ->whereIn('email', ['admin@chickenshop.com', 'cashier@chickenshop.com'])
            ->update(['password_viewable' => null]);
    }
};
