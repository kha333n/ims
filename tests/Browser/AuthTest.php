<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthTest extends DuskTestCase
{
    public function test_login_success(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('IMS')
                ->typeSlowly('input[wire\\:model="username"]', 'admin', 50)
                ->typeSlowly('input[wire\\:model="password"]', 'admin', 50)
                ->press('Login')
                ->waitForLocation('/')
                ->assertPathIs('/');
        });
    }

    public function test_login_wrong_password(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->typeSlowly('input[wire\\:model="username"]', 'admin', 50)
                ->typeSlowly('input[wire\\:model="password"]', 'wrongpassword', 50)
                ->press('Login')
                ->waitForText('Invalid')
                ->assertSee('Invalid username or password');
        });
    }

    public function test_unauthenticated_redirect(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/')
                ->waitForText('Login')
                ->assertPathIs('/login');
        });
    }

    public function test_profile_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->ownerUser())
                ->visit('/profile')
                ->assertSee('My Profile')
                ->assertSee('Profile Information')
                ->assertSee('Change Password');
        });
    }

    public function test_password_reset_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/password/reset')
                ->assertSee('Reset Admin Password')
                ->assertSee('Recovery Key')
                ->assertSee('Support Code');
        });
    }
}
