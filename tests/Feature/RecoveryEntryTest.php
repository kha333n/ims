<?php

namespace Tests\Feature;

use App\Livewire\Recovery\RecoveryEntry;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecoveryEntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsOwner();
    }

    private function createRecoveryData(string $type = 'daily'): array
    {
        $customer = Customer::create(['name' => 'Ahmad Khan', 'mobile' => '0300-111', 'cnic' => '12345-1234567-1']);
        $rm = Employee::create(['name' => 'Hassan RM', 'type' => 'recovery_man', 'area' => 'Saddar']);
        $account = Account::create([
            'customer_id' => $customer->id,
            'recovery_man_id' => $rm->id,
            'total_amount' => 500000,
            'remaining_amount' => 300000,
            'installment_type' => $type,
            'installment_amount' => 50000,
            'sale_date' => '2025-01-01',
            'status' => 'active',
        ]);

        return compact('customer', 'rm', 'account');
    }

    public function test_page_loads(): void
    {
        $this->get(route('recovery.entry'))
            ->assertStatus(200)
            ->assertSee('Recovery Entry');
    }

    public function test_load_shows_accounts_for_rm_and_category(): void
    {
        ['rm' => $rm, 'account' => $account] = $this->createRecoveryData('daily');

        Livewire::test(RecoveryEntry::class)
            ->set('recovery_man_id', $rm->id)
            ->set('category', 'daily')
            ->call('load')
            ->assertSet('loaded', true)
            ->assertSee('Ahmad Khan');
    }

    public function test_load_filters_by_category(): void
    {
        ['rm' => $rm] = $this->createRecoveryData('daily');
        // Create a weekly account for same RM
        $customer2 = Customer::create(['name' => 'Bilal']);
        Account::create([
            'customer_id' => $customer2->id,
            'recovery_man_id' => $rm->id,
            'total_amount' => 300000,
            'remaining_amount' => 200000,
            'installment_type' => 'weekly',
            'installment_amount' => 30000,
            'sale_date' => '2025-01-01',
            'status' => 'active',
        ]);

        Livewire::test(RecoveryEntry::class)
            ->set('recovery_man_id', $rm->id)
            ->set('category', 'daily')
            ->call('load')
            ->assertSee('Ahmad Khan')
            ->assertDontSee('Bilal');
    }

    public function test_can_mark_payments(): void
    {
        ['rm' => $rm, 'account' => $account] = $this->createRecoveryData('daily');

        Livewire::test(RecoveryEntry::class)
            ->set('recovery_man_id', $rm->id)
            ->set('category', 'daily')
            ->call('load')
            ->set("checked.{$account->id}", true)
            ->call('updateStatus')
            ->assertSet('actionSummary.count', 1)
            ->assertSet('actionSummary.total', 50000);

        $this->assertDatabaseHas('payments', [
            'account_id' => $account->id,
            'amount' => 50000,
            'transaction_type' => 'installment',
        ]);

        $this->assertEquals(250000, $account->fresh()->remaining_amount);
    }

    public function test_detects_duplicate_same_day_payment(): void
    {
        ['rm' => $rm, 'account' => $account] = $this->createRecoveryData('daily');

        Payment::create([
            'account_id' => $account->id,
            'amount' => 50000,
            'transaction_type' => 'installment',
            'payment_date' => today(),
            'collected_by' => $rm->id,
        ]);

        Livewire::test(RecoveryEntry::class)
            ->set('recovery_man_id', $rm->id)
            ->set('category', 'daily')
            ->call('load')
            ->set("checked.{$account->id}", true)
            ->call('updateStatus')
            ->assertSet('showDuplicateWarning', true)
            ->assertSet('duplicateCount', 1)
            ->call('confirmDuplicateUpdate')
            ->assertSet('actionSummary.duplicates', 1);
    }

    public function test_validates_selection_required(): void
    {
        ['rm' => $rm] = $this->createRecoveryData('daily');

        Livewire::test(RecoveryEntry::class)
            ->set('recovery_man_id', $rm->id)
            ->set('category', 'daily')
            ->call('load')
            ->call('updateStatus')
            ->assertHasErrors(['checked']);
    }
}
