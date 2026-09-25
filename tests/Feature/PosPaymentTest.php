<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Branch $branch;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Test Branch',
            'code' => 'TB01',
            'address' => '123 Test St',
            'phone' => '0712345678',
            'is_active' => true,
        ]);

        $this->cashier = User::create([
            'name' => 'Test Cashier',
            'email' => 'cashier@test.com',
            'password' => bcrypt('password'),
            'role' => 'cashier',
            'permissions' => ['billing', 'dashboard'],
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'General',
            'code' => 'GEN',
        ]);

        $this->item = Item::create([
            'category_id' => $category->id,
            'item_code' => 'ITM001',
            'name' => 'Test Item',
            'selling_price' => 1000.00,
            'cost_price' => 800.00,
            'unit_of_measure' => 'pcs',
            'is_active' => true,
        ]);

        BranchStock::create([
            'branch_id' => $this->branch->id,
            'item_id' => $this->item->id,
            'quantity' => 50,
        ]);
    }

    public function test_cash_payment_succeeds_without_installment_fields(): void
    {
        $response = $this->actingAs($this->cashier)
            ->withSession(['branch_id' => $this->branch->id])
            ->post(route('cashier.sales.create'), [
                'subtotal' => 1000,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 1000,
                'payment_method' => 'cash',
                'first_due_date' => '',
                'due_day_of_month' => '',
                'items' => json_encode([
                    [
                        'item_id' => $this->item->id,
                        'quantity' => 1,
                        'price' => 1000,
                    ]
                ]),
            ]);

        $this->assertDatabaseHas('sales', [
            'total_amount' => 1000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);
    }

    public function test_card_payment_succeeds_without_installment_fields(): void
    {
        $response = $this->actingAs($this->cashier)
            ->withSession(['branch_id' => $this->branch->id])
            ->post(route('cashier.sales.create'), [
                'subtotal' => 1000,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 1000,
                'payment_method' => 'card',
                'first_due_date' => '',
                'items' => json_encode([
                    [
                        'item_id' => $this->item->id,
                        'quantity' => 1,
                        'price' => 1000,
                    ]
                ]),
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sales', [
            'total_amount' => 1000,
            'payment_method' => 'card',
            'status' => 'completed',
        ]);
    }

    public function test_cheque_payment_succeeds_with_cheque_details(): void
    {
        $response = $this->actingAs($this->cashier)
            ->withSession(['branch_id' => $this->branch->id])
            ->post(route('cashier.sales.create'), [
                'subtotal' => 1000,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 1000,
                'payment_method' => 'cheque',
                'cheque_number' => 'CHQ123456',
                'bank_name' => 'Bank of Ceylon',
                'cheque_date' => now()->addDays(7)->toDateString(),
                'first_due_date' => '',
                'items' => json_encode([
                    [
                        'item_id' => $this->item->id,
                        'quantity' => 1,
                        'price' => 1000,
                    ]
                ]),
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sales', [
            'total_amount' => 1000,
            'payment_method' => 'cheque',
            'status' => 'completed',
        ]);
    }

    public function test_installment_payment_requires_installment_fields(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'phone' => '0771234567',
            'nic' => '123456789V',
        ]);

        $response = $this->actingAs($this->cashier)
            ->withSession(['branch_id' => $this->branch->id])
            ->post(route('cashier.sales.create'), [
                'subtotal' => 1000,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 1000,
                'payment_method' => 'installment',
                'customer_id' => $customer->id,
                'first_due_date' => '',
                'items' => json_encode([
                    [
                        'item_id' => $this->item->id,
                        'quantity' => 1,
                        'price' => 1000,
                    ]
                ]),
            ]);

        $response->assertSessionHasErrors(['first_due_date', 'down_payment_amount', 'number_of_installments', 'monthly_installment_amount', 'due_day_of_month']);
    }

    public function test_installment_payment_succeeds_with_valid_installment_fields(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'phone' => '0771234567',
            'nic' => '123456789V',
        ]);

        $response = $this->actingAs($this->cashier)
            ->withSession(['branch_id' => $this->branch->id])
            ->post(route('cashier.sales.create'), [
                'subtotal' => 1000,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 1000,
                'payment_method' => 'installment',
                'customer_id' => $customer->id,
                'down_payment_amount' => 300,
                'number_of_installments' => 6,
                'monthly_installment_amount' => 120,
                'first_due_date' => now()->addMonth()->toDateString(),
                'due_day_of_month' => 15,
                'items' => json_encode([
                    [
                        'item_id' => $this->item->id,
                        'quantity' => 1,
                        'price' => 1000,
                    ]
                ]),
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sales', [
            'total_amount' => 1000,
            'payment_method' => 'installment',
            'customer_id' => $customer->id,
        ]);
        $this->assertDatabaseHas('installment_agreements', [
            'customer_id' => $customer->id,
            'down_payment_amount' => 300,
            'number_of_installments' => 6,
        ]);
    }
}
