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
        Schema::table('users', function (Blueprint $blueprint) {
            if (!Schema::hasColumn('users', 'permissions')) {
                $blueprint->json('permissions')->nullable();
            }
        });

        Schema::table('audit_logs', function (Blueprint $blueprint) {
            if (!Schema::hasColumn('audit_logs', 'user_name')) {
                $blueprint->string('user_name')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'user_role')) {
                $blueprint->string('user_role')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->dropColumn('permissions');
        });

        Schema::table('audit_logs', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['user_name', 'user_role']);
        });
    }
};
