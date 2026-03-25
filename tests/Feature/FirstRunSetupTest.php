<?php

namespace Tests\Feature;

use App\Livewire\Auth\FirstRunSetup;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FirstRunSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_page_loads_when_no_users(): void
    {
        $this->get(route('setup'))
            ->assertStatus(200)
            ->assertSee('Company Details');
    }

    public function test_step_1_saves_company_details(): void
    {
        Livewire::test(FirstRunSetup::class)
            ->set('company_name', 'Test Company')
            ->set('company_address', '123 Test St')
            ->set('company_phone', '051-1234567')
            ->call('saveCompany')
            ->assertSet('step', 2);

        $this->assertEquals('Test Company', Setting::get('company_name'));
        $this->assertEquals('123 Test St', Setting::get('company_address'));
    }

    public function test_step_1_requires_company_name(): void
    {
        Livewire::test(FirstRunSetup::class)
            ->set('company_name', '')
            ->call('saveCompany')
            ->assertHasErrors(['company_name'])
            ->assertSet('step', 1);
    }

    public function test_step_2_creates_owner_account(): void
    {
        Livewire::test(FirstRunSetup::class)
            ->set('step', 2)
            ->set('name', 'Test Owner')
            ->set('username', 'owner')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('createOwner')
            ->assertSet('step', 3);

        $this->assertDatabaseHas('users', [
            'name' => 'Test Owner',
            'username' => 'owner',
            'role' => 'owner',
        ]);
    }

    public function test_step_2_validates_password_confirmation(): void
    {
        Livewire::test(FirstRunSetup::class)
            ->set('step', 2)
            ->set('name', 'Test Owner')
            ->set('username', 'owner')
            ->set('password', 'password123')
            ->set('password_confirmation', 'wrong')
            ->call('createOwner')
            ->assertHasErrors(['password']);
    }

    public function test_step_3_proceeds_to_license(): void
    {
        Livewire::test(FirstRunSetup::class)
            ->set('step', 3)
            ->set('recoveryKey', 'TEST-KEY')
            ->call('proceedToLicense')
            ->assertSet('step', 4);
    }

    public function test_redirects_away_when_users_exist(): void
    {
        User::create([
            'name' => 'Existing',
            'username' => 'existing',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        // When users exist, setup page should redirect (to dashboard or license)
        $this->get(route('setup'))
            ->assertRedirect();
    }
}
