<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PurchasePoint extends Component
{
    public string $purchase_date = '';

    public ?int $supplier_id = null;

    public string $notes = '';

    /** @var array<int, array{product_id: int, name: string, unit_cost: string, quantity: int}> */
    public array $items = [];

    // Add-line fields
    public ?int $selected_product_id = null;

    public string $line_rate = '';

    public int $line_quantity = 1;

    // Stock info
    public ?string $stock_product_name = null;

    public ?int $stock_current_qty = null;

    public ?string $stock_current_price = null;

    public function mount(): void
    {
        $this->purchase_date = now()->format('Y-m-d');
    }

    public function updatedSelectedProductId(): void
    {
        if ($this->selected_product_id) {
            $product = Product::find($this->selected_product_id);
            if ($product) {
                $this->stock_product_name = $product->name;
                $this->stock_current_qty = $product->quantity;
                $this->stock_current_price = formatMoney($product->price);
                $this->line_rate = (string) ($product->price / 100);

                return;
            }
        }

        $this->resetStockInfo();
    }

    public function addItem(): void
    {
        $this->validate([
            'selected_product_id' => 'required|exists:products,id',
            'line_rate' => 'required|numeric|min:0',
            'line_quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($this->selected_product_id);

        $this->items[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_cost' => $this->line_rate,
            'quantity' => $this->line_quantity,
        ];

        $this->reset(['selected_product_id', 'line_rate', 'line_quantity']);
        $this->line_quantity = 1;
        $this->resetStockInfo();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function savePurchase(): void
    {
        $this->validate([
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
        ]);

        DB::transaction(function () {
            foreach ($this->items as $item) {
                Purchase::create([
                    'product_id' => $item['product_id'],
                    'supplier_id' => $this->supplier_id ?: null,
                    'quantity' => $item['quantity'],
                    'unit_cost' => parseMoney($item['unit_cost']),
                    'purchase_date' => $this->purchase_date,
                    'notes' => $this->notes ?: null,
                ]);

                Product::where('id', $item['product_id'])
                    ->increment('quantity', $item['quantity']);
            }
        });

        $this->reset(['items', 'notes', 'supplier_id', 'selected_product_id', 'line_rate', 'line_quantity']);
        $this->line_quantity = 1;
        $this->purchase_date = now()->format('Y-m-d');
        $this->resetStockInfo();

        session()->flash('success', 'Purchase saved successfully.');
    }

    public function getTotalAmountProperty(): int
    {
        return (int) collect($this->items)->sum(fn ($item) => parseMoney($item['unit_cost']) * $item['quantity']);
    }

    private function resetStockInfo(): void
    {
        $this->stock_product_name = null;
        $this->stock_current_qty = null;
        $this->stock_current_price = null;
    }

    public function render()
    {
        return view('livewire.inventory.purchase-point', [
            'products' => Product::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }
}
