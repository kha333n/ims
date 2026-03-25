<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class InventoryTest extends DuskTestCase
{
    public function test_product_list_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/inventory/products')
                ->assertSee('Product List');
        });
    }

    public function test_product_search(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/inventory/products')
                ->typeSlowly('input[wire\\:model\\.live\\.debounce\\.300ms="search"]', 'LED', 50)
                ->pause(500)
                ->assertSee('LED');
        });
    }

    public function test_supplier_list_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/inventory/suppliers')
                ->assertSee('Suppliers');
        });
    }

    public function test_purchase_point_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/inventory/purchase')
                ->assertSee('Purchase Point');
        });
    }
}
