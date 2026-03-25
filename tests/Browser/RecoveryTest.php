<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RecoveryTest extends DuskTestCase
{
    public function test_recovery_entry_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/recovery/entry')
                ->assertSee('Recovery Entry');
        });
    }
}
