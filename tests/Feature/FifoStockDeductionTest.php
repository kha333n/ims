<?php

namespace Tests\Feature;

use App\Livewire\Inventory\PurchasePoint;
use App\Livewire\Sales\NewSale;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FifoStockDeductionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::create([
            'name' => 'Admin', 'username' => 'admin', 'password' => 'password', 'role' => 'owner', 'is_active' => true,
        ]));
    }

    public function test_purchase_creates_batch_with_remaining_qty(): void
    {
        $product = Product::create(['name' => 'Test Item', 'sale_price' => 100000, 'purchase_price' => 80000, 'quantity' => 0]);

        Livewire::test(PurchasePoint::class)
            ->set('selected_product_id', $product->id)
            ->set('line_rate', '800')
            ->set('line_quantity', 10)
            ->call('addItem')
            ->call('savePurchase')
            ->assertHasNoErrors();

        $purchase = Purchase::where('product_id', $product->id)->first();
        $this->assertNotNull($purchase);
        $this->assertEquals(10, $purchase->quantity);
        $this->assertEquals(10, $purchase->remaining_qty);
        $this->assertNotNull($purchase->batch_number);
        $this->assertStringStartsWith('B-', $purchase->batch_number);

        $product->refresh();
        $this->assertEquals(10, $product->quantity);
    }

    public function test_sale_deducts_from_oldest_batch_first(): void
    {
        $product = Product::create(['name' => 'FIFO Item', 'sale_price' => 150000, 'purchase_price' => 100000, 'quantity' => 15]);
        $customer = Customer::create(['name' => 'Test Buyer']);
        $sm = Employee::create(['name' => 'SM', 'type' => 'sale_man', 'commission_percent' => 5]);
        $rm = Employee::create(['name' => 'RM', 'type' => 'recovery_man', 'salary' => 2000000]);

        // Create two batches: old (5 units) and new (10 units)
        Purchase::create([
            'product_id' => $product->id, 'quantity' => 5, 'remaining_qty' => 5,
            'unit_cost' => 100000, 'purchase_date' => '2026-01-01', 'batch_number' => 'B-0001',
        ]);
        Purchase::create([
            'product_id' => $product->id, 'quantity' => 10, 'remaining_qty' => 10,
            'unit_cost' => 120000, 'purchase_date' => '2026-02-01', 'batch_number' => 'B-0002',
        ]);

        // Sell 7 units — should take 5 from batch1 + 2 from batch2
        Livewire::test(NewSale::class)
            ->set('customer_id', $customer->id)
            ->set('selected_product_id', $product->id)
            ->set('item_price', '1500')
            ->set('item_quantity', 7)
            ->call('addItem')
            ->set('advance', '0')
            ->set('discount', '0')
            ->set('installment_type', 'monthly')
            ->set('installment_day', 1)
            ->set('installment_amount', '500')
            ->set('sale_man_id', $sm->id)
            ->set('recovery_man_id', $rm->id)
            ->call('proceed')
            ->assertHasNoErrors();

        $batch1 = Purchase::where('batch_number', 'B-0001')->first();
        $batch2 = Purchase::where('batch_number', 'B-0002')->first();

        $this->assertEquals(0, $batch1->remaining_qty, 'Oldest batch should be fully depleted');
        $this->assertEquals(8, $batch2->remaining_qty, 'Newer batch should have 2 units taken');

        $product->refresh();
        $this->assertEquals(8, $product->quantity, 'Product total should be 15 - 7 = 8');
    }
}
