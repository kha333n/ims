<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsOwner();
    }

    public function test_dashboard_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('IMS');
        $response->assertSee('New Purchases');
        $response->assertSee('New Sales');
        $response->assertSee('Recovery Entry');
        $response->assertSee('New Customer');
    }

    public function test_navigation_menus_are_present(): void
    {
        $response = $this->get('/');

        $response->assertSee('Items');
        $response->assertSee('Management');
        $response->assertSee('Recovery');
        $response->assertSee('Reports');
        $response->assertSee('Settings');
    }

    public function test_all_top_level_routes_load(): void
    {
        $routes = [
            '/inventory/products',
            '/inventory/purchase',
            '/hr/sale-men',
            '/hr/recovery-men',
            '/customers',
            '/sales/new',
            '/sales/return',
            '/recovery/entry',
            '/reports',
            '/settings',
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertStatus(200, "Route {$route} did not return 200");
        }
    }

    public function test_placeholder_pages_show_correct_title(): void
    {
        $this->get('/inventory/products')->assertSee('Product List');
        $this->get('/hr/sale-men')->assertSee('Sale Men');
        $this->get('/recovery/entry')->assertSee('Recovery Entry');
        $this->get('/reports')->assertSee('Reports');
        $this->get('/settings')->assertSee('Company Settings');
    }
}
