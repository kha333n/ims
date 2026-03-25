<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FinancialReportsTest extends DuskTestCase
{
    public function test_all_financial_reports_load(): void
    {
        $routes = [
            '/financial/cash-book',
            '/financial/ledger',
            '/financial/profit-loss',
            '/financial/receivables',
            '/financial/collections',
            '/financial/supplier-expenses',
            '/financial/commissions',
            '/financial/inventory-valuation',
            '/financial/losses',
            '/financial/credit-debit',
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
