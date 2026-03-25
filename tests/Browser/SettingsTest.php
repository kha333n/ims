<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SettingsTest extends DuskTestCase
{
    public function test_company_settings_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/settings')
                ->assertSee('Company Settings');
        });
    }

    public function test_backup_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/settings/backup')
                ->assertSee('Backup & Restore');
        });
    }

    public function test_license_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/settings/license')
                ->assertSee('License');
        });
    }

    public function test_dashboard_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/')
                ->assertSee('IMS');
        });
    }
}
