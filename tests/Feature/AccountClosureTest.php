<?php

namespace Tests\Feature;

use App\Livewire\Customers\AccountClosure;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountClosureTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveAccount(): array
    {
        $customer = Customer::create(['name' => 'Ahmad Khan', 'mobile' => '0300-111']);
        $rm = Employee::create(['name' => 'Hassan', 'type' => 'recovery_man']);
        $account = Account::create([
            'customer_id' => $customer->id,
            'recovery_man_id' => $rm->id,
            'total_amount' => 500000,
            'remaining_amount' => 200000,
            'installment_type' => 'monthly',
            'installment_amount' => 50000,
            'sale_date' => '2025-01-01',
            'status' => 'active',
        ]);

        return compact('customer', 'rm', 'account');
    }

    public function test_page_loads(): void
    {
        $this->get(route('customers.closure'))
            ->assertStatus(200)
            ->assertSee('Account Closure');
    }

    public function test_can_close_account(): void
    {
        ['account' => $account, 'rm' => $rm, 'customer' => $customer] = $this->createActiveAccount();

        Livewire::test(AccountClosure::class)
            ->set('recovery_man_id', $rm->id)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->set('discount_amount', '1000')
            ->set('discount_slip', 'SLIP-001')
            ->call('closeAccount');

        $account->refresh();
        $this->assertEquals('closed', $account->status);
        $this->assertNotNull($account->closed_at);
        $this->assertEquals('SLIP-001', $account->discount_slip);
    }

    public function test_can_activate_closed_account(): void
    {
        ['account' => $account, 'rm' => $rm, 'customer' => $customer] = $this->createActiveAccount();
        $account->update(['status' => 'closed', 'closed_at' => now()]);

        Livewire::test(AccountClosure::class)
            ->set('mode', 'activate')
            ->set('recovery_man_id', $rm->id)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->call('activateAccount');

        $account->refresh();
        $this->assertEquals('active', $account->status);
        $this->assertNull($account->closed_at);
    }

    public function test_shows_account_info_when_selected(): void
    {
        ['account' => $account, 'rm' => $rm, 'customer' => $customer] = $this->createActiveAccount();

        Livewire::test(AccountClosure::class)
            ->set('recovery_man_id', $rm->id)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->assertSet('accountInfo.name', 'Ahmad Khan')
            ->assertSet('accountInfo.contact', '0300-111');
    }
}
