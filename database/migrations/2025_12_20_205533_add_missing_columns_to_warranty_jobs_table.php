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
        Schema::table('warranty_jobs', function (Blueprint $table) {

            if (!Schema::hasColumn('warranty_jobs', 'job_number')) {
                $table->string('job_number')->unique()->after('id');
            }

            if (!Schema::hasColumn('warranty_jobs', 'warranty_id')) {
                $table->unsignedBigInteger('warranty_id')->after('job_number');
                $table->foreign('warranty_id')
                      ->references('id')
                      ->on('warranties')
                      ->onDelete('cascade');
            }

            if (!Schema::hasColumn('warranty_jobs', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->after('warranty_id');
                $table->foreign('branch_id')
                      ->references('id')
                      ->on('branches')
                      ->onDelete('cascade');
            }

            if (!Schema::hasColumn('warranty_jobs', 'problem_description')) {
                $table->text('problem_description')->after('branch_id');
            }

            if (!Schema::hasColumn('warranty_jobs', 'claim_type')) {
                $table->string('claim_type')->after('problem_description');
            }

            if (!Schema::hasColumn('warranty_jobs', 'status')) {
                $table->string('status')->default('Received')->after('claim_type');
            }

            if (!Schema::hasColumn('warranty_jobs', 'remarks')) {
                $table->text('remarks')->nullable()->after('status');
            }

            if (!Schema::hasColumn('warranty_jobs', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('remarks');
                $table->foreign('created_by')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        // optional: keep empty to avoid accidental data loss
    }
};
