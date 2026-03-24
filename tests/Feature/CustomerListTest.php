<?php

namespace Tests\Feature;

use App\Livewire\Customers\CustomerList;
use App\Models\Account;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsOwner();
    }

    public function test_page_loads(): void
    {
        $this->get(route('customers.index'))
            ->assertStatus(200)
            ->assertSee('Customers');
    }

    public function test_shows_customers(): void
    {
        Customer::create(['name' => 'Ahmad Khan', 'mobile' => '0300-1111111']);
        Customer::create(['name' => 'Bilal Shah', 'cnic' => '12345-1234567-1']);

        Livewire::test(CustomerList::class)
            ->assertSee('Ahmad Khan')
            ->assertSee('Bilal Shah')
            ->assertSee('0300-1111111');
    }

    public function test_shows_balance_from_active_accounts(): void
    {
        $customer = Customer::create(['name' => 'Ahmad Khan']);
        Account::create([
            'customer_id' => $customer->id,
            'total_amount' => 500000,
            'remaining_amount' => 300000,
            'installment_type' => 'monthly',
            'installment_amount' => 50000,
            'sale_date' => '2025-01-01',
            'status' => 'active',
        ]);
        Account::create([
            'customer_id' => $customer->id,
            'total_amount' => 200000,
            'remaining_amount' => 0,
            'installment_type' => 'monthly',
            'installment_amount' => 20000,
            'sale_date' => '2025-01-01',
            'status' => 'closed',
        ]);

        Livewire::test(CustomerList::class)
            ->assertSee('PKR 3,000');
    }

    public function test_search_filters_by_name(): void
    {
        Customer::create(['name' => 'Ahmad Khan']);
        Customer::create(['name' => 'Bilal Shah']);

        Livewire::test(CustomerList::class)
            ->set('search', 'Ahmad')
            ->assertSee('Ahmad Khan')
            ->assertDontSee('Bilal Shah');
    }

    public function test_shows_empty_state(): void
    {
        Livewire::test(CustomerList::class)
            ->assertSee('No customers have been added yet.');
    }

    public function test_has_add_customer_link(): void
    {
        Livewire::test(CustomerList::class)
            ->assertSee('Add Customer');
    }
}
