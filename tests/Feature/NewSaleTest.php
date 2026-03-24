<?php

namespace Tests\Feature;

use App\Livewire\Sales\NewSale;
use App\Models\AccountItem;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewSaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsOwner();
    }

    private function setupSaleData(): array
    {
        $customer = Customer::create(['name' => 'Ahmad Khan', 'mobile' => '0300-111']);
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);
        $sm = Employee::create(['name' => 'Ali SM', 'type' => 'sale_man']);
        $rm = Employee::create(['name' => 'Hassan RM', 'type' => 'recovery_man', 'area' => 'Saddar']);

        return compact('customer', 'product', 'sm', 'rm');
    }

    public function test_page_loads(): void
    {
        $this->get(route('sales.new'))
            ->assertStatus(200)
            ->assertSee('New Sale');
    }

    public function test_selecting_customer_fills_info(): void
    {
        $customer = Customer::create(['name' => 'Ahmad Khan', 'father_name' => 'Muhammad', 'mobile' => '0300-111']);

        Livewire::test(NewSale::class)
            ->set('customer_id', $customer->id)
            ->assertSet('customer_name', 'Ahmad Khan')
            ->assertSet('customer_father', 'Muhammad')
            ->assertSet('customer_mobile', '0300-111');
    }

    public function test_selecting_product_fills_price(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);

        Livewire::test(NewSale::class)
            ->set('selected_product_id', $product->id)
            ->assertSet('item_price', '50000');
    }

    public function test_can_add_item(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);

        Livewire::test(NewSale::class)
            ->set('selected_product_id', $product->id)
            ->set('item_price', '50000')
            ->set('item_quantity', 1)
            ->call('addItem')
            ->assertCount('items', 1);
    }

    public function test_cannot_add_item_exceeding_stock(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 2]);

        Livewire::test(NewSale::class)
            ->set('selected_product_id', $product->id)
            ->set('item_price', '50000')
            ->set('item_quantity', 5)
            ->call('addItem')
            ->assertHasErrors(['item_quantity']);
    }

    public function test_can_remove_item(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);

        Livewire::test(NewSale::class)
            ->set('selected_product_id', $product->id)
            ->set('item_price', '50000')
            ->set('item_quantity', 1)
            ->call('addItem')
            ->call('removeItem', 0)
            ->assertCount('items', 0);
    }

    public function test_remaining_amount_calculated(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);

        $component = Livewire::test(NewSale::class)
            ->set('selected_product_id', $product->id)
            ->set('item_price', '50000')
            ->set('item_quantity', 1)
            ->call('addItem')
            ->set('advance', '5000')
            ->set('discount', '2000');

        // total=5000000, advance=500000, discount=200000 → remaining=4300000
        $this->assertEquals(4300000, $component->get('remainingAmount'));
    }

    public function test_total_installments_calculated(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);

        $component = Livewire::test(NewSale::class)
            ->set('selected_product_id', $product->id)
            ->set('item_price', '50000')
            ->set('item_quantity', 1)
            ->call('addItem')
            ->set('advance', '0')
            ->set('discount', '0')
            ->set('installment_amount', '5000');

        // remaining=5000000, inst=500000 → 10 installments
        $this->assertEquals(10, $component->get('totalInstallments'));
    }

    public function test_complete_sale_creates_account_and_decrements_stock(): void
    {
        ['customer' => $customer, 'product' => $product, 'sm' => $sm, 'rm' => $rm] = $this->setupSaleData();

        Livewire::test(NewSale::class)
            ->set('customer_id', $customer->id)
            ->set('sale_date', '2025-04-01')
            ->set('selected_product_id', $product->id)
            ->set('item_price', '50000')
            ->set('item_quantity', 1)
            ->call('addItem')
            ->set('advance', '5000')
            ->set('discount', '0')
            ->set('installment_type', 'monthly')
            ->set('installment_day', 15)
            ->set('installment_amount', '5000')
            ->set('sale_man_id', $sm->id)
            ->set('recovery_man_id', $rm->id)
            ->call('proceed');

        $this->assertDatabaseHas('accounts', [
            'customer_id' => $customer->id,
            'sale_man_id' => $sm->id,
            'recovery_man_id' => $rm->id,
            'total_amount' => 5000000,
            'advance_amount' => 500000,
            'remaining_amount' => 4500000,
            'installment_type' => 'monthly',
            'installment_day' => 15,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('account_items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 5000000,
        ]);

        $this->assertDatabaseHas('payments', [
            'amount' => 500000,
            'transaction_type' => 'advance',
        ]);

        $this->assertEquals(9, $product->fresh()->quantity);
    }

    public function test_sale_validates_required_fields(): void
    {
        Livewire::test(NewSale::class)
            ->call('proceed')
            ->assertHasErrors(['customer_id', 'items', 'installment_amount', 'sale_man_id', 'recovery_man_id']);
    }

    public function test_sale_with_multiple_items(): void
    {
        ['customer' => $customer, 'sm' => $sm, 'rm' => $rm] = $this->setupSaleData();
        $product2 = Product::create(['name' => 'Fan', 'sale_price' => 1500000, 'quantity' => 20]);

        Livewire::test(NewSale::class)
            ->set('customer_id', $customer->id)
            ->set('selected_product_id', Product::where('name', 'LED TV')->first()->id)
            ->set('item_price', '50000')
            ->set('item_quantity', 1)
            ->call('addItem')
            ->set('selected_product_id', $product2->id)
            ->set('item_price', '15000')
            ->set('item_quantity', 2)
            ->call('addItem')
            ->set('advance', '0')
            ->set('discount', '0')
            ->set('installment_type', 'daily')
            ->set('installment_amount', '1000')
            ->set('sale_man_id', $sm->id)
            ->set('recovery_man_id', $rm->id)
            ->call('proceed');

        // total = 50000*100 + 15000*100*2 = 5000000 + 3000000 = 8000000
        $this->assertDatabaseHas('accounts', [
            'customer_id' => $customer->id,
            'total_amount' => 8000000,
            'remaining_amount' => 8000000,
        ]);

        $this->assertEquals(2, AccountItem::count());
        $this->assertEquals(18, $product2->fresh()->quantity);
    }
}
