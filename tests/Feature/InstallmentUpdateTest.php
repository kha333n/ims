<?php

namespace Tests\Feature;

use App\Livewire\Customers\InstallmentUpdate;
use App\Models\Account;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InstallmentUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function createAccount(array $overrides = []): array
    {
        $customer = Customer::create(['name' => 'Ahmad Khan', 'mobile' => '0300-111']);
        $account = Account::create(array_merge([
            'customer_id' => $customer->id,
            'total_amount' => 500000,
            'remaining_amount' => 300000,
            'installment_type' => 'monthly',
            'installment_day' => 15,
            'installment_amount' => 50000,
            'sale_date' => '2025-01-01',
            'status' => 'active',
        ], $overrides));

        return compact('customer', 'account');
    }

    public function test_page_loads(): void
    {
        $this->get(route('customers.installment-update'))
            ->assertStatus(200)
            ->assertSee('Installment Plan Update');
    }

    public function test_selecting_account_shows_current_plan_and_remaining(): void
    {
        ['customer' => $customer, 'account' => $account] = $this->createAccount();

        Livewire::test(InstallmentUpdate::class)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->assertSet('current_type', 'monthly')
            ->assertSet('current_day', 15)
            ->assertSet('current_amount', 50000)
            ->assertSet('remaining_amount', 300000);
    }

    public function test_can_update_to_weekly_plan(): void
    {
        ['customer' => $customer, 'account' => $account] = $this->createAccount();

        Livewire::test(InstallmentUpdate::class)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->set('new_type', 'weekly')
            ->set('new_day', 5)
            ->set('new_amount', '1000')
            ->call('save');

        $account->refresh();
        $this->assertEquals('weekly', $account->installment_type);
        $this->assertEquals(5, $account->installment_day);
        $this->assertEquals(100000, $account->installment_amount);

        $this->assertDatabaseHas('installment_plan_changes', [
            'account_id' => $account->id,
            'old_type' => 'monthly',
            'new_type' => 'weekly',
        ]);
    }

    public function test_daily_plan_sets_day_to_null(): void
    {
        ['customer' => $customer, 'account' => $account] = $this->createAccount();

        Livewire::test(InstallmentUpdate::class)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->set('new_type', 'daily')
            ->set('new_amount', '500')
            ->call('save');

        $account->refresh();
        $this->assertEquals('daily', $account->installment_type);
        $this->assertNull($account->installment_day);
        $this->assertEquals(50000, $account->installment_amount);
    }

    public function test_periods_to_complete_calculated_correctly(): void
    {
        // remaining = 300000 paisas (PKR 3,000), amount = PKR 500 (50000 paisas) → 6 periods
        ['customer' => $customer, 'account' => $account] = $this->createAccount();

        $component = Livewire::test(InstallmentUpdate::class)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->set('new_type', 'daily')
            ->set('new_amount', '500');

        $this->assertEquals(6, $component->get('periodsToComplete'));
        $this->assertEquals('days', $component->get('periodLabel'));
    }

    public function test_periods_to_complete_rounds_up(): void
    {
        // remaining = 300000 paisas (PKR 3,000), amount = PKR 700 (70000 paisas) → ceil(300000/70000) = 5
        ['customer' => $customer, 'account' => $account] = $this->createAccount();

        $component = Livewire::test(InstallmentUpdate::class)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->set('new_type', 'weekly')
            ->set('new_day', 3)
            ->set('new_amount', '700');

        $this->assertEquals(5, $component->get('periodsToComplete'));
        $this->assertEquals('weeks', $component->get('periodLabel'));
    }

    public function test_weekly_requires_day_of_week(): void
    {
        ['customer' => $customer, 'account' => $account] = $this->createAccount();

        Livewire::test(InstallmentUpdate::class)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->set('new_type', 'weekly')
            ->set('new_day', null)
            ->set('new_amount', '500')
            ->call('save')
            ->assertHasErrors(['new_day']);
    }

    public function test_monthly_requires_day_of_month(): void
    {
        ['customer' => $customer, 'account' => $account] = $this->createAccount();

        Livewire::test(InstallmentUpdate::class)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->set('new_type', 'monthly')
            ->set('new_day', null)
            ->set('new_amount', '500')
            ->call('save')
            ->assertHasErrors(['new_day']);
    }

    public function test_monthly_accepts_day_31(): void
    {
        ['customer' => $customer, 'account' => $account] = $this->createAccount();

        Livewire::test(InstallmentUpdate::class)
            ->set('customer_id', $customer->id)
            ->set('account_id', $account->id)
            ->set('new_type', 'monthly')
            ->set('new_day', 31)
            ->set('new_amount', '1000')
            ->call('save')
            ->assertHasNoErrors();

        $account->refresh();
        $this->assertEquals(31, $account->installment_day);
    }
}
