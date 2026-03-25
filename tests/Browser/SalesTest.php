<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SalesTest extends DuskTestCase
{
    public function test_new_sale_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/sales/new')
                ->assertSee('New Sale')
                ->assertSee('Customer')
                ->assertSee('Items')
                ->assertSee('Installment Plan')
                ->assertSee('Staff Assignment');
        });
    }

    public function test_installment_type_daily(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/sales/new')
                ->select('select[wire\\:model\\.live="installment_type"]', 'daily')
                ->pause(500)
                ->assertSee('Per Day Amount');
        });
    }

    public function test_installment_type_monthly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/sales/new')
                ->select('select[wire\\:model\\.live="installment_type"]', 'monthly')
                ->pause(500)
                ->assertSee('Day of Month')
                ->assertSee('Per Month Amount');
        });
    }

    public function test_return_point_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/sales/return')
                ->assertSee('Return Point');
        });
    }
}
