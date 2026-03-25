<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManualTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsOwner();
    }

    public function test_manual_page_loads(): void
    {
        $this->get(route('manual'))
            ->assertStatus(200)
            ->assertSee('User Manual');
    }

    public function test_manual_has_all_sections(): void
    {
        $response = $this->get(route('manual'));

        $response->assertSee('Getting Started');
        $response->assertSee('Inventory');
        $response->assertSee('Customers');
        $response->assertSee('Making a Sale');
        $response->assertSee('Recovery');
        $response->assertSee('Returns');
        $response->assertSee('Account Management');
        $response->assertSee('Employees');
        $response->assertSee('Expenses');
        $response->assertSee('Reports');
        $response->assertSee('Settings');
        $response->assertSee('Keyboard Shortcuts');
    }

    public function test_manual_has_back_link(): void
    {
        $this->get(route('manual'))
            ->assertSee('Back to App');
    }

    public function test_dashboard_has_manual_link(): void
    {
        $this->get(route('dashboard'))
            ->assertSee('Manual');
    }
}
