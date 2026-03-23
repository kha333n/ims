<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountItem;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_be_created(): void
    {
        $customer = Customer::create([
            'name' => 'Ahmed Ali',
            'father_name' => 'Muhammad Ali',
            'mobile' => '03001234567',
            'cnic' => '37405-1234567-1',
            'home_address' => 'House 5, Street 3, Rawalpindi',
        ]);

        $this->assertDatabaseHas('customers', ['name' => 'Ahmed Ali']);
        $this->assertEquals('Ahmed Ali', $customer->name);
    }

    public function test_product_formatted_price_attribute(): void
    {
        $product = Product::create([
            'name' => 'AC DC',
            'price' => 5000000, // 50,000 PKR in paisas
            'quantity' => 10,
        ]);

        $this->assertEquals('PKR 50,000', $product->formatted_price);
    }

    public function test_employee_sale_man_scope(): void
    {
        Employee::create(['name' => 'Umar', 'type' => 'sale_man']);
        Employee::create(['name' => 'Bilal', 'type' => 'recovery_man']);

        $this->assertEquals(1, Employee::saleMen()->count());
        $this->assertEquals(1, Employee::recoveryMen()->count());
    }

    public function test_account_relationships(): void
    {
        $customer = Customer::create(['name' => 'Test Customer']);
        $supplier = Supplier::create(['name' => 'Test Supplier']);
        $product = Product::create(['name' => 'LED TV', 'price' => 10000000, 'quantity' => 5]);

        $account = Account::create([
            'customer_id' => $customer->id,
            'sale_date' => now()->toDateString(),
            'total_amount' => 10000000,
            'advance_amount' => 1000000,
            'discount_amount' => 0,
            'remaining_amount' => 9000000,
            'installment_type' => 'Monthly',
            'installment_amount' => 1000000,
        ]);

        AccountItem::create([
            'account_id' => $account->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 10000000,
        ]);

        Payment::create([
            'account_id' => $account->id,
            'amount' => 1000000,
            'transaction_type' => 'advance',
            'payment_date' => now()->toDateString(),
        ]);

        $this->assertEquals(1, $account->items()->count());
        $this->assertEquals(1, $account->payments()->count());
        $this->assertEquals(1000000, $account->total_paid);
        $this->assertEquals($customer->id, $account->customer->id);
    }

    public function test_customer_total_remaining_attribute(): void
    {
        $customer = Customer::create(['name' => 'Balance Test']);

        Account::create([
            'customer_id' => $customer->id,
            'sale_date' => now()->toDateString(),
            'total_amount' => 5000000,
            'remaining_amount' => 3000000,
            'installment_type' => 'Daily',
            'installment_amount' => 100000,
            'status' => 'active',
        ]);

        $this->assertEquals(3000000, $customer->fresh()->total_remaining);
    }

    public function test_setting_get_and_set(): void
    {
        Setting::set('company_name', 'Techmiddle Technologies');

        $this->assertEquals('Techmiddle Technologies', Setting::get('company_name'));
        $this->assertNull(Setting::get('nonexistent_key'));
        $this->assertEquals('default', Setting::get('nonexistent_key', 'default'));
    }

    public function test_purchase_increments_can_be_tracked(): void
    {
        $supplier = Supplier::create(['name' => 'ABC Corp']);
        $product = Product::create(['name' => 'Buffer', 'price' => 200000, 'quantity' => 0]);

        $purchase = Purchase::create([
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'quantity' => 10,
            'unit_cost' => 150000,
            'purchase_date' => now()->toDateString(),
        ]);

        $this->assertEquals(10, $purchase->quantity);
        $this->assertEquals($product->id, $purchase->product->id);
        $this->assertEquals($supplier->id, $purchase->supplier->id);
    }
}
