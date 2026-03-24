<?php

namespace Tests\Feature;

use App\Livewire\Inventory\PurchasePoint;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchasePointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsOwner();
    }

    public function test_purchase_point_page_loads(): void
    {
        $response = $this->get(route('inventory.purchase'));

        $response->assertStatus(200);
        $response->assertSee('Purchase Point');
    }

    public function test_shows_product_dropdown(): void
    {
        Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);

        Livewire::test(PurchasePoint::class)
            ->assertSee('LED TV');
    }

    public function test_selecting_product_shows_stock_info(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'purchase_price' => 4000000, 'quantity' => 10]);

        Livewire::test(PurchasePoint::class)
            ->set('selected_product_id', $product->id)
            ->assertSet('stock_product_name', 'LED TV')
            ->assertSet('stock_current_qty', 10)
            ->assertSet('stock_current_price', 'PKR 50,000')
            ->assertSet('line_rate', '40000');
    }

    public function test_can_add_item_to_list(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);

        Livewire::test(PurchasePoint::class)
            ->set('selected_product_id', $product->id)
            ->set('line_rate', '45000')
            ->set('line_quantity', 5)
            ->call('addItem')
            ->assertCount('items', 1)
            ->assertSee('LED TV');
    }

    public function test_add_item_validates_required_fields(): void
    {
        Livewire::test(PurchasePoint::class)
            ->call('addItem')
            ->assertHasErrors(['selected_product_id']);
    }

    public function test_can_remove_item_from_list(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);

        Livewire::test(PurchasePoint::class)
            ->set('selected_product_id', $product->id)
            ->set('line_rate', '45000')
            ->set('line_quantity', 5)
            ->call('addItem')
            ->assertCount('items', 1)
            ->call('removeItem', 0)
            ->assertCount('items', 0);
    }

    public function test_save_purchase_creates_records_and_increments_stock(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);
        $supplier = Supplier::create(['name' => 'Test Supplier']);

        Livewire::test(PurchasePoint::class)
            ->set('purchase_date', '2025-04-01')
            ->set('supplier_id', $supplier->id)
            ->set('selected_product_id', $product->id)
            ->set('line_rate', '45000')
            ->set('line_quantity', 5)
            ->call('addItem')
            ->call('savePurchase');

        $this->assertDatabaseHas('purchases', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'quantity' => 5,
            'unit_cost' => 4500000,
        ]);

        $this->assertEquals(15, $product->fresh()->quantity);
    }

    public function test_save_purchase_validates_items_required(): void
    {
        Livewire::test(PurchasePoint::class)
            ->set('purchase_date', '2025-04-01')
            ->call('savePurchase')
            ->assertHasErrors(['items']);
    }

    public function test_save_purchase_resets_form(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);

        Livewire::test(PurchasePoint::class)
            ->set('selected_product_id', $product->id)
            ->set('line_rate', '45000')
            ->set('line_quantity', 5)
            ->call('addItem')
            ->call('savePurchase')
            ->assertCount('items', 0)
            ->assertSet('notes', '');
    }

    public function test_total_amount_computed_correctly(): void
    {
        $p1 = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);
        $p2 = Product::create(['name' => 'Fan', 'sale_price' => 1500000, 'quantity' => 20]);

        $component = Livewire::test(PurchasePoint::class)
            ->set('selected_product_id', $p1->id)
            ->set('line_rate', '50000')
            ->set('line_quantity', 2)
            ->call('addItem')
            ->set('selected_product_id', $p2->id)
            ->set('line_rate', '15000')
            ->set('line_quantity', 3)
            ->call('addItem');

        // 50000*100*2 + 15000*100*3 = 10,000,000 + 4,500,000 = 14,500,000 paisas
        $this->assertEquals(14500000, $component->get('totalAmount'));
    }
}
