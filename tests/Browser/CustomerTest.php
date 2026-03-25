<?php

namespace Tests\Browser;

use App\Models\Customer;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CustomerTest extends DuskTestCase
{
    public function test_customer_list_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/customers')
                ->assertSee('Customers');
        });
    }

    public function test_customer_search(): void
    {
        $first = Customer::first();
        if (! $first) {
            $this->markTestSkipped('No customers');
        }

        $this->browse(function (Browser $browser) use ($first) {
            $browser->loginAs($this->ownerUser())
                ->visit('/customers')
                ->typeSlowly('input[wire\\:model\\.live\\.debounce\\.300ms="search"]', substr($first->name, 0, 4), 50)
                ->pause(500)
                ->assertSee($first->name);
        });
    }

    public function test_add_customer(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/customers/new')
                ->assertSee('New Customer')
                ->typeSlowly('input[wire\\:model="name"]', 'Dusk Test Customer', 30)
                ->typeSlowly('input[wire\\:model="mobile"]', '03001112233', 30)
                ->press('Save')
                ->waitForText('Customer Saved')
                ->assertSee('Dusk Test Customer');
        });
    }

    public function test_customer_detail_loads(): void
    {
        $customer = Customer::first();
        if (! $customer) {
            $this->markTestSkipped('No customers');
        }

        $this->browse(function (Browser $browser) use ($customer) {
            $browser->loginAs($this->ownerUser())
                ->visit('/customers/'.$customer->id)
                ->assertSee($customer->name)
                ->assertSee('Accounts');
        });
    }

    public function test_customer_detail_edit(): void
    {
        $customer = Customer::first();
        if (! $customer) {
            $this->markTestSkipped('No customers');
        }

        $this->browse(function (Browser $browser) use ($customer) {
            $browser->loginAs($this->ownerUser())
                ->visit('/customers/'.$customer->id)
                ->click('button[wire\\:click="startEdit"]')
                ->waitFor('input[wire\\:model="name"]')
                ->press('Save')
                ->waitForText('Customer Updated')
                ->assertSee('Customer Updated');
        });
    }
}
