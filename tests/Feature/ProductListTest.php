<?php

namespace Tests\Feature;

use App\Livewire\Inventory\ProductList;
use App\Models\Account;
use App\Models\AccountItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsOwner();
    }

    // ── List Tests ─────────────────────────────────────────────────────────────

    public function test_product_list_page_loads(): void
    {
        $response = $this->get(route('inventory.products'));

        $response->assertStatus(200);
        $response->assertSee('Product List');
    }

    public function test_product_list_shows_products(): void
    {
        $supplier = Supplier::create(['name' => 'Test Supplier']);
        Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10, 'supplier_id' => $supplier->id]);
        Product::create(['name' => 'AC DC Fan', 'sale_price' => 1500000, 'quantity' => 25]);

        Livewire::test(ProductList::class)
            ->assertSee('LED TV')
            ->assertSee('AC DC Fan')
            ->assertSee('Test Supplier');
    }

    public function test_product_list_shows_empty_state(): void
    {
        Livewire::test(ProductList::class)
            ->assertSee('No products have been added yet.');
    }

    public function test_product_list_search_filters_by_name(): void
    {
        Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);
        Product::create(['name' => 'AC DC Fan', 'sale_price' => 1500000, 'quantity' => 25]);

        Livewire::test(ProductList::class)
            ->set('search', 'LED')
            ->assertSee('LED TV')
            ->assertDontSee('AC DC Fan');
    }

    public function test_product_list_search_shows_no_results_message(): void
    {
        Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);

        Livewire::test(ProductList::class)
            ->set('search', 'NonExistent')
            ->assertSee('No products found matching');
    }

    public function test_product_list_displays_formatted_price(): void
    {
        Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);

        Livewire::test(ProductList::class)
            ->assertSee('PKR 50,000');
    }

    public function test_product_list_paginates_at_50(): void
    {
        for ($i = 1; $i <= 55; $i++) {
            Product::create(['name' => "Product $i", 'sale_price' => 100000, 'quantity' => 1]);
        }

        Livewire::test(ProductList::class)
            ->assertSee('Product 1')
            ->assertDontSee('Product 55');
    }

    // ── Add Product Tests ──────────────────────────────────────────────────────

    public function test_add_product_modal_opens(): void
    {
        Livewire::test(ProductList::class)
            ->call('openAddModal')
            ->assertSet('showModal', true)
            ->assertSet('editingProductId', null)
            ->assertSee('Add Product');
    }

    public function test_can_create_product_with_required_fields(): void
    {
        Livewire::test(ProductList::class)
            ->call('openAddModal')
            ->set('name', 'New Product')
            ->set('sale_price', '15000')
            ->set('purchase_price', '12000')
            ->set('quantity', 5)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'sale_price' => 1500000,
            'quantity' => 5,
        ]);
    }

    public function test_can_create_product_with_all_fields(): void
    {
        $supplier = Supplier::create(['name' => 'Test Supplier']);

        Livewire::test(ProductList::class)
            ->call('openAddModal')
            ->set('name', 'Full Product')
            ->set('sale_price', '25000')
            ->set('purchase_price', '20000')
            ->set('quantity', 10)
            ->set('supplier_id', $supplier->id)
            ->set('brand', 'Samsung')
            ->set('model_number', 'SM-100')
            ->set('color', 'Black')
            ->set('category', 'Electronics')
            ->set('notes', 'Test notes')
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('products', [
            'name' => 'Full Product',
            'sale_price' => 2500000,
            'supplier_id' => $supplier->id,
            'brand' => 'Samsung',
            'model_number' => 'SM-100',
            'color' => 'Black',
            'category' => 'Electronics',
            'notes' => 'Test notes',
        ]);
    }

    public function test_create_product_validates_required_fields(): void
    {
        Livewire::test(ProductList::class)
            ->call('openAddModal')
            ->set('name', '')
            ->set('sale_price', '')
            ->call('save')
            ->assertHasErrors(['name', 'sale_price']);
    }

    public function test_create_product_validates_price_is_numeric(): void
    {
        Livewire::test(ProductList::class)
            ->call('openAddModal')
            ->set('name', 'Test')
            ->set('sale_price', 'abc')
            ->set('quantity', 1)
            ->call('save')
            ->assertHasErrors(['sale_price']);
    }

    public function test_can_create_product_with_image(): void
    {
        Storage::fake('local');

        Livewire::test(ProductList::class)
            ->call('openAddModal')
            ->set('name', 'Product With Image')
            ->set('sale_price', '5000')
            ->set('purchase_price', '4000')
            ->set('quantity', 1)
            ->set('image', UploadedFile::fake()->image('product.jpg', 200, 200))
            ->call('save')
            ->assertSet('showModal', false);

        $product = Product::where('name', 'Product With Image')->first();
        $this->assertNotNull($product->image_path);
        Storage::disk('local')->assertExists($product->image_path);
    }

    // ── Edit Product Tests ─────────────────────────────────────────────────────

    public function test_edit_modal_opens_with_product_data(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10, 'brand' => 'LG']);

        Livewire::test(ProductList::class)
            ->call('openEditModal', $product->id)
            ->assertSet('showModal', true)
            ->assertSet('editingProductId', $product->id)
            ->assertSet('name', 'LED TV')
            ->assertSet('sale_price', '50000')
            ->assertSet('quantity', 10)
            ->assertSet('brand', 'LG')
            ->assertSee('Edit Product');
    }

    public function test_can_update_product(): void
    {
        $product = Product::create(['name' => 'LED TV', 'sale_price' => 5000000, 'quantity' => 10]);

        Livewire::test(ProductList::class)
            ->call('openEditModal', $product->id)
            ->set('name', 'Updated LED TV')
            ->set('sale_price', '60000')
            ->set('purchase_price', '50000')
            ->set('quantity', 15)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated LED TV',
            'sale_price' => 6000000,
            'quantity' => 15,
        ]);
    }

    public function test_close_modal_resets_form(): void
    {
        Livewire::test(ProductList::class)
            ->call('openAddModal')
            ->set('name', 'Test')
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertSet('name', '');
    }

    // ── Delete Product Tests ───────────────────────────────────────────────────

    public function test_can_soft_delete_product(): void
    {
        $product = Product::create(['name' => 'To Delete', 'sale_price' => 100000, 'quantity' => 5]);

        Livewire::test(ProductList::class)
            ->call('confirmDelete', $product->id)
            ->assertSet('confirmingDeleteId', $product->id)
            ->call('deleteProduct')
            ->assertSet('confirmingDeleteId', null);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_cannot_delete_product_with_active_account_items(): void
    {
        $product = Product::create(['name' => 'Active Item', 'sale_price' => 100000, 'quantity' => 5]);
        $customer = Customer::create(['name' => 'Test Customer']);
        $account = Account::create([
            'customer_id' => $customer->id,
            'total_amount' => 100000,
            'remaining_amount' => 100000,
            'installment_type' => 'monthly',
            'installment_amount' => 10000,
            'sale_date' => '2025-01-01',
            'status' => 'active',
        ]);
        AccountItem::create([
            'account_id' => $account->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100000,
        ]);

        Livewire::test(ProductList::class)
            ->call('confirmDelete', $product->id)
            ->call('deleteProduct')
            ->assertSet('deleteError', 'Cannot delete: this product has active account items.');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
    }

    public function test_can_delete_product_with_closed_account_items(): void
    {
        $product = Product::create(['name' => 'Closed Item', 'sale_price' => 100000, 'quantity' => 5]);
        $customer = Customer::create(['name' => 'Test Customer']);
        $account = Account::create([
            'customer_id' => $customer->id,
            'total_amount' => 100000,
            'remaining_amount' => 0,
            'installment_type' => 'monthly',
            'installment_amount' => 10000,
            'sale_date' => '2025-01-01',
            'status' => 'closed',
        ]);
        AccountItem::create([
            'account_id' => $account->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100000,
        ]);

        Livewire::test(ProductList::class)
            ->call('confirmDelete', $product->id)
            ->call('deleteProduct');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_cancel_delete_resets_state(): void
    {
        $product = Product::create(['name' => 'Test', 'sale_price' => 100000, 'quantity' => 1]);

        Livewire::test(ProductList::class)
            ->call('confirmDelete', $product->id)
            ->assertSet('confirmingDeleteId', $product->id)
            ->call('cancelDelete')
            ->assertSet('confirmingDeleteId', null);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
    }
}
