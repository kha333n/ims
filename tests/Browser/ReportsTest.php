<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ReportsTest extends DuskTestCase
{
    public function test_all_operational_reports_load(): void
    {
        $routes = [
            '/reports/item-sales',
            '/reports/item-detail',
            '/reports/daily-recovery',
            '/reports/monthly-recovery',
            '/reports/returns',
            '/reports/salesman',
            '/reports/inventory',
            '/reports/customer',
            '/reports/defaulters',
        ];

        $this->browse(function (Browser $browser) use ($routes) {
            $browser->loginAs($this->ownerUser());

            foreach ($routes as $route) {
                $browser->visit($route)
                    ->assertDontSee('Server Error');
            }
        });
    }
}
