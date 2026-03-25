<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NavigationTest extends DuskTestCase
{
    public function test_owner_sees_all_menus(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/')
                ->assertSee('Items')
                ->assertSee('Management')
                ->assertSee('Recovery')
                ->assertSee('Reports')
                ->assertSee('Financial')
                ->assertSee('Settings');
        });
    }

    public function test_owner_sees_all_toolbar_buttons(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/')
                ->assertSee('New Purchases')
                ->assertSee('New Sales')
                ->assertSee('Recovery Entry')
                ->assertSee('New Customer');
        });
    }

    public function test_sale_man_limited_nav(): void
    {
        $sm = $this->saleManUser();
        if (! $sm) {
            $this->markTestSkipped('No sale man user in DB');
        }

        $this->browse(function (Browser $browser) use ($sm) {
            $browser->loginAs($sm)
                ->visit('/')
                ->assertSee('Items')
                ->assertSee('Management')
                ->assertDontSee('Financial')
                ->assertDontSee('Settings');
        });
    }

    public function test_recovery_man_minimal_nav(): void
    {
        $rm = $this->recoveryManUser();
        if (! $rm) {
            $this->markTestSkipped('No recovery man user in DB');
        }

        $this->browse(function (Browser $browser) use ($rm) {
            $browser->loginAs($rm)
                ->visit('/')
                ->assertSee('Recovery')
                ->assertDontSee('Financial')
                ->assertDontSee('Settings');
        });
    }

    public function test_ims_logo_links_to_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/inventory/products')
                ->clickLink('IMS')
                ->assertPathIs('/');
        });
    }
}
