<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateSqliteToMysql extends Command
{
    protected $signature = 'app:migrate-sqlite-to-mysql';
    protected $description = 'Migrate all data from SQLite to MySQL';

    public function handle()
    {
        $this->info('Starting SQLite → MySQL data migration...');

        $mysql  = DB::connection('mysql');
        $sqlite = DB::connection('sqlite_old');

        try {
            // Disable FK checks (MySQL only)
            $mysql->statement('SET FOREIGN_KEY_CHECKS=0');

            /**
             * ORDER MATTERS (Parents → Children)
             */
            $tables = [
                'users',
                'categories',
                'suppliers',
                'customers',
                'customer_credits',

                'items',
                'branches',
                'branch_item_settings',
                'branch_stock',

                'expense_categories',
                'expenses',

                'grns',
                'grn_items',

                'sales',
                'sale_items',

                'payments',
                'supplier_payments',

                'stock_movements',
                'stock_transfers',
                'stock_transfer_items',

                'serial_numbers',

                'warranties',
                'warranty_jobs',
                'warranty_job_history',

                'audit_logs',
                'system_settings',
            ];

            foreach ($tables as $table) {
                $this->line("Migrating table: {$table}");

                $rows = $sqlite->table($table)->get();

                if ($rows->isEmpty()) {
                    $this->warn("  → No data found");
                    continue;
                }

                // TRUNCATE (safe because FK checks are OFF)
                $mysql->table($table)->truncate();

                foreach ($rows as $row) {
                    $mysql->table($table)->insert((array) $row);
                }

                // Fix AUTO_INCREMENT
                if ($mysql->getSchemaBuilder()->hasColumn($table, 'id')) {
                    $maxId = $mysql->table($table)->max('id');
                    if ($maxId) {
                        $mysql->statement(
                            "ALTER TABLE {$table} AUTO_INCREMENT = " . ($maxId + 1)
                        );
                    }
                }

                $this->info("  → Migrated {$rows->count()} rows");
            }

            // Re-enable FK checks
            $mysql->statement('SET FOREIGN_KEY_CHECKS=1');

            $this->info('✅ SQLite → MySQL migration completed successfully!');
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $mysql->statement('SET FOREIGN_KEY_CHECKS=1');

            $this->error('❌ Migration failed!');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
