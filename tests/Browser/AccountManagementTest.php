<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AccountManagementTest extends DuskTestCase
{
    public function test_account_closure_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/customers/closure')
                ->assertSee('Account Closure');
        });
    }

    public function test_account_transfer_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/customers/transfer')
                ->assertSee('Account Transfer');
        });
    }

    public function test_installment_update_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/customers/installment-update')
                ->assertSee('Installment Plan Update');
        });
    }

    public function test_problem_entry_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/customers/problems')
                ->assertSee('Problem Entry');
        });
    }
}
