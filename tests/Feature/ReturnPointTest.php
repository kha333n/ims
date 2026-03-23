<?php

namespace Tests\Feature;

use App\Livewire\Sales\ReturnPoint;
use App\Models\Account;
use App\Models\AccountItem;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReturnPointTest extends TestCase
{
    use RefreshDatabase;

    private function createSaleData(): array
    {
        $customer = Customer::create(['name' => 'Ahmad Khan', 'mobile' => '0300-111']);
        $rm = Employee::create(['name' => 'Hassan RM', 'type' => 'recovery_man']);
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 9]);
        $account = Account::create([
            'customer_id' => $customer->id,
            'recovery_man_id' => $rm->id,
            'total_amount' => 5000000,
            'remaining_amount' => 4000000,
            'installment_type' => 'monthly',
            'installment_amount' => 500000,
            'sale_date' => '2025-01-01',
            'status' => 'active',
        ]);
        $item = AccountItem::create([
            'account_id' => $account->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 5000000,
        ]);

        return compact('customer', 'rm', 'product', 'account', 'item');
    }

    public function test_page_loads(): void
    {
        $this->get(route('sales.return'))
            ->assertStatus(200)
            ->assertSee('Return Point');
    }

    public function test_selecting_account_shows_details(): void
    {
        ['rm' => $rm, 'customer' => $customer, 'account' => $account] = $this->createSaleData();

        Livewire::test(ReturnPoint::class)
            ->set('recovery_man_id', $rm->id)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->assertSet('accountInfo.customer_name', 'Ahmad Khan')
            ->assertSet('accountInfo.phone', '0300-111');
    }

    public function test_can_process_return_with_restock(): void
    {
        ['rm' => $rm, 'customer' => $customer, 'account' => $account, 'item' => $item, 'product' => $product] = $this->createSaleData();

        Livewire::test(ReturnPoint::class)
            ->set('recovery_man_id', $rm->id)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->set('account_item_id', $item->id)
            ->set('returning_amount', '40000')
            ->set('return_date', '2025-02-01')
            ->set('reason', 'Defective')
            ->set('inventory_action', 'restock')
            ->call('processReturn');

        $this->assertDatabaseHas('returns', [
            'account_id' => $account->id,
            'account_item_id' => $item->id,
            'returning_amount' => 4000000,
            'inventory_action' => 'restock',
        ]);

        $this->assertTrue($item->fresh()->returned);
        $this->assertEquals(0, $account->fresh()->remaining_amount);
        $this->assertEquals(10, $product->fresh()->quantity);
    }

    public function test_can_process_return_with_scrap(): void
    {
        ['rm' => $rm, 'customer' => $customer, 'account' => $account, 'item' => $item, 'product' => $product] = $this->createSaleData();

        Livewire::test(ReturnPoint::class)
            ->set('recovery_man_id', $rm->id)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->set('account_item_id', $item->id)
            ->set('returning_amount', '30000')
            ->set('return_date', '2025-02-01')
            ->set('inventory_action', 'scrap')
            ->call('processReturn');

        $this->assertEquals(9, $product->fresh()->quantity);
        $this->assertEquals(1000000, $account->fresh()->remaining_amount);
    }

    public function test_validates_required_fields(): void
    {
        Livewire::test(ReturnPoint::class)
            ->call('processReturn')
            ->assertHasErrors(['account_id', 'account_item_id', 'returning_amount']);
    }
}
