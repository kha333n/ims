<?php

namespace Tests\Feature;

use App\Livewire\Customers\AddCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AddCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_loads(): void
    {
        $this->get(route('customers.create'))
            ->assertStatus(200)
            ->assertSee('New Customer');
    }

    public function test_can_create_customer_with_required_fields(): void
    {
        Livewire::test(AddCustomer::class)
            ->set('name', 'Ahmad Khan')
            ->call('save');

        $this->assertDatabaseHas('customers', ['name' => 'Ahmad Khan']);
    }

    public function test_can_create_customer_with_all_fields(): void
    {
        Livewire::test(AddCustomer::class)
            ->set('name', 'Ahmad Khan')
            ->set('father_name', 'Muhammad Khan')
            ->set('mobile', '0300-1234567')
            ->set('cnic', '12345-1234567-1')
            ->set('reference', 'Bilal')
            ->set('home_address', '123 Main St')
            ->set('shop_address', '456 Market Rd')
            ->call('save');

        $this->assertDatabaseHas('customers', [
            'name' => 'Ahmad Khan',
            'father_name' => 'Muhammad Khan',
            'mobile' => '0300-1234567',
            'cnic' => '12345-1234567-1',
            'reference' => 'Bilal',
            'home_address' => '123 Main St',
            'shop_address' => '456 Market Rd',
        ]);
    }

    public function test_validates_name_required(): void
    {
        Livewire::test(AddCustomer::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_shows_summary_and_resets_form_after_save(): void
    {
        Livewire::test(AddCustomer::class)
            ->set('name', 'Ahmad Khan')
            ->set('mobile', '0300-111')
            ->call('save')
            ->assertSet('savedSummary.name', 'Ahmad Khan')
            ->assertSet('name', '')
            ->assertSee('Customer Saved');

        $this->assertDatabaseHas('customers', ['name' => 'Ahmad Khan']);
    }
}
