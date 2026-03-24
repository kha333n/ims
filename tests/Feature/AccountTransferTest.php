<?php

namespace Tests\Feature;

use App\Livewire\Customers\AccountTransfer;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountTransferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsOwner();
    }

    public function test_page_loads(): void
    {
        $this->get(route('customers.transfer'))
            ->assertStatus(200)
            ->assertSee('Account Transfer');
    }

    public function test_selecting_customer_shows_info(): void
    {
        $customer = Customer::create(['name' => 'Ahmad Khan']);
        $rm = Employee::create(['name' => 'Hassan', 'type' => 'recovery_man']);
        Account::create([
            'customer_id' => $customer->id,
            'recovery_man_id' => $rm->id,
            'total_amount' => 500000,
            'remaining_amount' => 300000,
            'installment_type' => 'monthly',
            'installment_amount' => 50000,
            'sale_date' => '2025-01-01',
            'status' => 'active',
        ]);

        Livewire::test(AccountTransfer::class)
            ->set('customer_id', $customer->id)
            ->assertSet('customer_name', 'Ahmad Khan')
            ->assertSet('active_account_count', 1)
            ->assertSet('current_rm_name', 'Hassan');
    }

    public function test_can_transfer_accounts(): void
    {
        $customer = Customer::create(['name' => 'Ahmad Khan']);
        $oldRm = Employee::create(['name' => 'Hassan', 'type' => 'recovery_man']);
        $newRm = Employee::create(['name' => 'Bilal', 'type' => 'recovery_man']);
        $account = Account::create([
            'customer_id' => $customer->id,
            'recovery_man_id' => $oldRm->id,
            'total_amount' => 500000,
            'remaining_amount' => 300000,
            'installment_type' => 'monthly',
            'installment_amount' => 50000,
            'sale_date' => '2025-01-01',
            'status' => 'active',
        ]);

        Livewire::test(AccountTransfer::class)
            ->set('customer_id', $customer->id)
            ->set('to_recovery_man_id', $newRm->id)
            ->call('transfer');

        $this->assertEquals($newRm->id, $account->fresh()->recovery_man_id);
        $this->assertDatabaseHas('account_transfers', [
            'account_id' => $account->id,
            'from_recovery_man_id' => $oldRm->id,
            'to_recovery_man_id' => $newRm->id,
        ]);
    }
}
