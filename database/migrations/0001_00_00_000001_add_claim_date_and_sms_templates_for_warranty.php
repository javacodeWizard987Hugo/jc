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
        Schema::table('warranty_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('warranty_jobs', 'claim_date')) {
                $table->date('claim_date')->nullable()->after('warranty_id');
            }
        });

        // Insert/update SMS templates for Warranty Claims
        DB::table('sms_templates')->updateOrInsert(
            ['event_name' => 'warranty_claim_creation'],
            [
                'template' => 'Dear {CustomerName}, your warranty claim job #{JobNo} for {ItemModel} has been received at {BranchName} ({BranchContact}). Thank you for reaching out.',
                'placeholders' => json_encode(['CustomerName', 'JobNo', 'ItemModel', 'BranchName', 'BranchContact']),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('sms_templates')->updateOrInsert(
            ['event_name' => 'warranty_job_ready'],
            [
                'template' => 'Dear {CustomerName}, your warranty claim job #{JobNo} is ready for collection at {BranchName} ({BranchContact}).',
                'placeholders' => json_encode(['CustomerName', 'JobNo', 'BranchName', 'BranchContact']),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warranty_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('warranty_jobs', 'claim_date')) {
                $table->dropColumn('claim_date');
            }
        });
    }
};
