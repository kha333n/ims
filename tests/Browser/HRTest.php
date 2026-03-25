<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HRTest extends DuskTestCase
{
    public function test_sale_man_list_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/hr/sale-men')
                ->assertSee('Sale Men');
        });
    }

    public function test_recovery_man_list_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/hr/recovery-men')
                ->assertSee('Recovery Men');
        });
    }

    public function test_user_management_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/settings/users')
                ->assertSee('User Management')
                ->assertDontSee('admin'); // admin account hidden
        });
    }
}
