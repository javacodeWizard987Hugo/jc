<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\InstallmentAgreement;
use App\Models\InstallmentPayment;
use App\Models\Branch;
use App\Models\Category;
use Carbon\Carbon;

class TestReportSeeder extends Seeder
{
    public function run()
    {
        // Clear old test data
        \DB::statement('DELETE FROM installment_payments');
        \DB::statement('DELETE FROM installment_agreements');
        \DB::statement('DELETE FROM sale_items');
        \DB::statement('DELETE FROM sales');
        \DB::statement('DELETE FROM items');
        \DB::statement('DELETE FROM categories');
        \DB::statement('DELETE FROM customers');

        $category = Category::create(['name' => 'Electronics', 'is_active' => true]);
        
        $customer = Customer::create([
            'name' => 'John Doe X',
            'phone' => '0771234568',
            'nic' => '123456789X',
            'address' => 'Colombo'
        ]);

        $item1 = Item::create([
            'name' => 'Samsung S23 X',
            'item_code' => 'ITM-001X',
            'category_id' => $category->id,
            'cost_price' => 150000,
            'selling_price' => 180000,
            'is_active' => true,
            'unit_of_measure' => 'pcs'
        ]);

        $item2 = Item::create([
            'name' => 'iPhone 15 X',
            'item_code' => 'ITM-002X',
            'category_id' => $category->id,
            'cost_price' => 250000,
            'selling_price' => 300000,
            'is_active' => true,
            'unit_of_measure' => 'pcs'
        ]);

        // Sale with Installment
        $sale1 = Sale::create([
            'customer_id' => $customer->id,
            'total_amount' => 180000,
            'subtotal' => 180000,
            'payment_method' => 'installment',
            'status' => 'completed',
            'cashier_id' => 1,
            'invoice_number' => 'INV-001X'
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'item_id' => $item1->id,
            'quantity' => 1,
            'unit_price' => 180000,
            'total_price' => 180000
        ]);

        $agreement = InstallmentAgreement::create([
            'sale_id' => $sale1->id,
            'customer_id' => $customer->id,
            'total_invoice_value' => 180000,
            'down_payment_amount' => 30000,
            'down_payment_date' => now()->subDays(45),
            'down_payment_method' => 'Cash',
            'loan_tenor' => 6,
            'interest_service_charge' => 12000,
            'number_of_installments' => 6,
            'monthly_installment_amount' => 27000,
            'balance_amount' => 162000,
            'first_due_date' => now()->subDays(15),
            'due_day_of_month' => 10,
            'status' => 'active',
            'guarantor_name' => 'Jane Doe',
            'guarantor_mobile_number' => '0777654321'
        ]);

        // Online Payment
        InstallmentPayment::create([
            'installment_agreement_id' => $agreement->id,
            'amount' => 27000,
            'payment_date' => now()->subDays(5),
            'payment_method' => 'Online',
            'notes' => 'Test online payment'
        ]);
    }
}
