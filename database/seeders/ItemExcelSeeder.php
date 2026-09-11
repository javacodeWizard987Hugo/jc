<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ItemExcelSeeder extends Seeder
{
    public function run(): void
    {
        $rows = Excel::toArray([], storage_path('app/items.xlsx'))[0];

        DB::transaction(function () use ($rows) {

            foreach ($rows as $index => $row) {

                // Skip header row
                if ($index === 0) continue;

                $itemCode  = $row[0] ?? null;
                $itemName  = $row[1] ?? null;
                $cost      = $row[2] ?? 0;
                $selling   = $row[3] ?? 0;
                $lockModel = $row[4] ?? null;
                $emiNumber = $row[5] ?? null;

                // 🔴 SKIP INVALID ROWS
                if (!$itemCode || !$itemName) {
                    continue;
                }

                Item::updateOrCreate(
                    ['item_code' => (int) $itemCode],
                    [
                        'name' => trim($itemName),
                        'category_id' => 2,
                        'unit_of_measure' => 'pcs',

                        // 🔒 FORCE NUMBERS (NO NULLS)
                        'cost_price' => is_numeric($cost) ? $cost : 0,
                        'selling_price' => is_numeric($selling) ? $selling : 0,

                        'is_active' => true,
                        'emi_lock_mode' => $lockModel,
                        'emi_number' => is_numeric($emiNumber) ? $emiNumber : null,
                    ]
                );
            }
        });
    }
}
