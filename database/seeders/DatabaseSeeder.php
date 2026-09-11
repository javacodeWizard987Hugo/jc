<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\User;
use App\Models\SystemSetting;
use App\Models\Item;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create a default branch
        $branch = Branch::updateOrCreate(
            ['name' => 'Main Branch'],
            ['location' => '123 Main St']
        );

        // Create a default category
        $category = Category::updateOrCreate(['name' => 'Default Category']);

        // Create or Update Default Admin User
        $admin = User::updateOrCreate(
            ['email' => 'newadmin@gcsolution.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('newpassword123'),
                'password_viewable' => 'newpassword123',
                'role' => 'admin',
                'is_active' => true,
                'branch_id' => $branch->id,
            ]
        );

        // Create or Update Default Cashier User
        $cashier = User::updateOrCreate(
            ['email' => 'newcashier@gcsolution.com'],
            [
                'name' => 'Cashier',
                'password' => Hash::make('cashier456'),
                'password_viewable' => 'cashier456',
                'role' => 'cashier',
                'is_active' => true,
                'branch_id' => $branch->id,
            ]
        );

        // System Settings
        SystemSetting::setValue('tax_rate', 0, 'number', 'Tax rate percentage');
        SystemSetting::setValue('max_cashier_discount', 10, 'number', 'Maximum discount percentage cashier can apply');
        SystemSetting::setValue('expiry_alert_days', 30, 'number', 'Days before expiry to show alert');
        SystemSetting::setValue('invoice_format', 'INV-{YYYY}-{MM}-{DD}-{NNNN}', 'string', 'Invoice number format');
        SystemSetting::setValue('default_payment_method', 'cash', 'string', 'Default payment method');
        SystemSetting::setValue('rounding_rules', 'none', 'string', 'Rounding rules (none, up, down, nearest)');

        // Create a default item
        $item = Item::updateOrCreate(
            ['item_code' => 'F-001'],
            [
                'name' => 'Chicken',
                'selling_price' => 150.00,
                'cost_price' => 100.00,
                'unit_of_measure' => 'pcs',
                'category_id' => $category->id,
            ]
        );

        // Add stock for the item at the main branch
        $item->stock()->updateOrCreate(
            ['branch_id' => $branch->id],
            ['quantity' => 100]
        );


        // Create a default customer
        Customer::updateOrCreate(
            ['email' => 'johndoe@example.com'],
            [
                'name' => 'John Doe',
                'phone' => '1234567890',
            ]
        );
              // ✅ VERY IMPORTANT ORDER
      //  $this->call([
        //    ItemExcelSeeder::class,                // MUST RUN FIRST
          //  InstallmentAgreementImportSeeder::class,
        //]);
    }
}
