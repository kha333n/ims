<?php

namespace Tests\Feature;

use App\Livewire\Customers\CustomerDetail;
use App\Models\Account;
use App\Models\AccountItem;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomerWithAccount(): array
    {
        $customer = Customer::create(['name' => 'Ahmad Khan', 'mobile' => '0300-1111111']);
        $rm = Employee::create(['name' => 'Hassan RM', 'type' => 'recovery_man']);
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);
        $account = Account::create([
            'customer_id' => $customer->id,
            'recovery_man_id' => $rm->id,
            'total_amount' => 5000000,
            'advance_amount' => 500000,
            'remaining_amount' => 4500000,
            'installment_type' => 'monthly',
            'installment_amount' => 500000,
            'sale_date' => '2025-01-01',
            'status' => 'active',
        ]);
        AccountItem::create([
            'account_id' => $account->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 5000000,
        ]);

        return compact('customer', 'account', 'rm', 'product');
    }

    public function test_page_loads(): void
    {
        $customer = Customer::create(['name' => 'Ahmad Khan']);

        $this->get(route('customers.show', $customer->id))
            ->assertStatus(200)
            ->assertSee('Ahmad Khan');
    }

    public function test_shows_customer_info(): void
    {
        $customer = Customer::create([
            'name' => 'Ahmad Khan',
            'father_name' => 'Muhammad Khan',
            'mobile' => '0300-1111111',
            'cnic' => '12345-1234567-1',
        ]);

        Livewire::test(CustomerDetail::class, ['id' => $customer->id])
            ->assertSee('Ahmad Khan')
            ->assertSee('Muhammad Khan')
            ->assertSee('0300-1111111')
            ->assertSee('12345-1234567-1');
    }

    public function test_shows_accounts_table(): void
    {
        ['customer' => $customer] = $this->createCustomerWithAccount();

        Livewire::test(CustomerDetail::class, ['id' => $customer->id])
            ->assertSee('LED TV')
            ->assertSee('PKR 50,000')
            ->assertSee('Active')
            ->assertSee('Hassan RM');
    }

    public function test_can_edit_customer(): void
    {
        $customer = Customer::create(['name' => 'Ahmad Khan']);

        Livewire::test(CustomerDetail::class, ['id' => $customer->id])
            ->call('startEdit')
            ->assertSet('editing', true)
            ->set('name', 'Updated Name')
            ->set('mobile', '0300-9999999')
            ->call('saveCustomer')
            ->assertSet('editing', false);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
            'mobile' => '0300-9999999',
        ]);
    }

    public function test_edit_validates_name_required(): void
    {
        $customer = Customer::create(['name' => 'Ahmad Khan']);

        Livewire::test(CustomerDetail::class, ['id' => $customer->id])
            ->call('startEdit')
            ->set('name', '')
            ->call('saveCustomer')
            ->assertHasErrors(['name']);
    }

    public function test_can_record_payment(): void
    {
        ['customer' => $customer, 'account' => $account] = $this->createCustomerWithAccount();

        Livewire::test(CustomerDetail::class, ['id' => $customer->id])
            ->set('payment_account_id', $account->id)
            ->set('payment_amount', '5000')
            ->set('transaction_type', 'installment')
            ->set('payment_date', '2025-02-01')
            ->call('savePayment');

        $this->assertDatabaseHas('payments', [
            'account_id' => $account->id,
            'amount' => 500000,
            'transaction_type' => 'installment',
        ]);

        $this->assertEquals(4000000, $account->fresh()->remaining_amount);
    }

    public function test_payment_validates_required_fields(): void
    {
        $customer = Customer::create(['name' => 'Ahmad Khan']);

        Livewire::test(CustomerDetail::class, ['id' => $customer->id])
            ->call('savePayment')
            ->assertHasErrors(['payment_account_id', 'payment_amount']);
    }

    public function test_shows_balance_summary(): void
    {
        ['customer' => $customer] = $this->createCustomerWithAccount();

        Livewire::test(CustomerDetail::class, ['id' => $customer->id])
            ->assertSee('PKR 45,000');
    }

    public function test_cancel_edit_returns_to_read_only(): void
    {
        $customer = Customer::create(['name' => 'Ahmad Khan']);

        Livewire::test(CustomerDetail::class, ['id' => $customer->id])
            ->call('startEdit')
            ->assertSet('editing', true)
            ->call('cancelEdit')
            ->assertSet('editing', false);
    }
}
