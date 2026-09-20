<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\Customer;
use App\Models\InstallmentAgreement;
use App\Models\Item;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosAndInstallmentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $branch;
    protected $category;
    protected $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Main Branch',
            'code' => 'BR001',
            'address' => 'Test Address',
            'phone' => '0112345678',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Test Cashier',
            'email' => 'cashier@test.com',
            'password' => bcrypt('password'),
            'role' => 'cashier',
            'branch_id' => $this->branch->id,
            'is_active' => true,
            'permissions' => ['billing', 'agreements'],
        ]);

        $this->category = Category::create([
            'name' => 'Electronics',
            'is_active' => true,
        ]);

        $this->item = Item::create([
            'category_id' => $this->category->id,
            'item_code' => 'ITM001',
            'name' => 'Test Phone',
            'selling_price' => 50000,
            'unit_of_measure' => 'pcs',
            'is_active' => true,
        ]);

        BranchStock::create([
            'branch_id' => $this->branch->id,
            'item_id' => $this->item->id,
            'quantity' => 10,
        ]);
    }

    public function test_card_payment_sale_can_be_completed_without_due_day_of_month_error()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['branch_id' => $this->branch->id])
            ->post(route('cashier.sales.create'), [
                'subtotal' => 50000,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 50000,
                'payment_method' => 'card',
                'items' => json_encode([
                    [
                        'item_id' => $this->item->id,
                        'quantity' => 1,
                        'price' => 50000,
                    ]
                ]),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales', [
            'payment_method' => 'card',
            'total_amount' => 50000,
            'status' => 'completed',
        ]);
    }

    public function test_installment_agreement_can_be_updated_without_otp()
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'phone' => '0771234567',
            'nic' => '123456789V',
            'address' => '123 Main St',
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-TEST-001',
            'cashier_id' => $this->user->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'subtotal' => 50000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 50000,
            'payment_method' => 'installment',
            'status' => 'completed',
        ]);

        $agreement = InstallmentAgreement::create([
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'total_invoice_value' => 50000,
            'down_payment_amount' => 10000,
            'down_payment_date' => now()->toDateString(),
            'balance_amount' => 40000,
            'number_of_installments' => 5,
            'monthly_installment_amount' => 8000,
            'first_due_date' => now()->addMonth()->format('Y-m-d'),
            'due_day_of_month' => 15,
            'otp_verified_at' => null, // NOT verified
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('cashier.installment-agreement.update', $sale->id), [
                'customer_name' => 'John Doe Updated',
                'customer_phone' => '0771234567',
                'customer_nic' => '123456789V',
                'customer_address' => '123 Main St Updated',
                'guarantor_name' => 'Jane Doe',
                'guarantor_nic' => '987654321V',
                'guarantor_address' => '456 Side St',
                'guarantor_mobile_number' => '0777654321',
                'total_invoice_value' => 50000,
                'down_payment_amount' => 10000,
                'monthly_installment_amount' => 8000,
                'number_of_installments' => 5,
                'first_due_date' => now()->addMonth()->format('Y-m-d'),
                'due_day_of_month' => 15,
            ]);

        $response->assertRedirect(route('cashier.installment-agreement.show', $sale->id));
        $this->assertDatabaseHas('installment_agreements', [
            'id' => $agreement->id,
            'guarantor_name' => 'Jane Doe',
            'is_finalized' => 1,
        ]);
    }
}
