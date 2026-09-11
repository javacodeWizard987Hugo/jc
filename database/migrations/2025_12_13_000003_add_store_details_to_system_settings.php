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
        // Insert store details into system_settings
        $settings = [
            [
                'key' => 'store_address',
                'value' => 'Kegalu Stores, Kusumpokuna, Diulankadawala',
                'type' => 'string',
                'description' => 'Store address to display on invoices',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'store_contact',
                'value' => '071 405 6490',
                'type' => 'string',
                'description' => 'Store contact number to display on invoices',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'store_footer_text',
                'value' => 'AryaLabs POS',
                'type' => 'string',
                'description' => 'Footer text to display at bottom of invoices',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', [
            'store_address',
            'store_contact',
            'store_footer_text'
        ])->delete();
    }
};
