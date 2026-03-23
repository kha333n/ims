<?php

namespace App\Livewire\Inventory;

use App\Models\FinancialLedger;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierProduct;
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

    public ?int $selected_product_id = null;

    public string $line_rate = '';

    public int $line_quantity = 1;

    public ?array $purchaseSummary = null;

    // Stock info
    public ?string $stock_product_name = null;

    public ?int $stock_current_qty = null;

    public ?string $stock_current_price = null;

    /** @var array<int, array{supplier_id: int, name: string, price: int, last_qty: ?int, last_date: ?string, is_lowest: bool}> */
    public array $supplierPrices = [];

    public ?string $selectedSupplierLastInfo = null;

    public function mount(): void
    {
        $this->purchase_date = now()->format('Y-m-d');
    }

    public function updatedSelectedProductId(): void
    {
        $this->supplierPrices = [];
        $this->selectedSupplierLastInfo = null;

        if ($this->selected_product_id) {
            $product = Product::find($this->selected_product_id);
            if ($product) {
                $this->stock_product_name = $product->name;
                $this->stock_current_qty = $product->quantity;
                $this->stock_current_price = formatMoney($product->sale_price);
                $this->line_rate = (string) ($product->purchase_price / 100);

                // Load supplier pricing for this product
                $spRecords = SupplierProduct::with('supplier')
                    ->where('product_id', $product->id)
                    ->orderBy('unit_price')
                    ->get();

                if ($spRecords->isNotEmpty()) {
                    $lowestPrice = $spRecords->first()->unit_price;
                    $this->supplierPrices = $spRecords->map(fn ($sp) => [
                        'supplier_id' => $sp->supplier_id,
                        'name' => $sp->supplier->name,
                        'price' => $sp->unit_price,
                        'last_qty' => $sp->last_quantity,
                        'last_date' => $sp->last_supplied_at ? formatDate($sp->last_supplied_at) : null,
                        'is_lowest' => $sp->unit_price === $lowestPrice,
                    ])->toArray();
                }

                return;
            }
        }

        $this->resetStockInfo();
    }

    public function updatedSupplierId(): void
    {
        $this->selectedSupplierLastInfo = null;

        if ($this->supplier_id && $this->selected_product_id) {
            $sp = SupplierProduct::where('supplier_id', $this->supplier_id)
                ->where('product_id', $this->selected_product_id)
                ->first();

            if ($sp) {
                $this->line_rate = (string) ($sp->unit_price / 100);
                $info = 'Last price: '.formatMoney($sp->unit_price);
                if ($sp->last_quantity) {
                    $info .= " | Qty: {$sp->last_quantity}";
                }
                if ($sp->last_supplied_at) {
                    $info .= ' | Date: '.formatDate($sp->last_supplied_at);
                }
                $this->selectedSupplierLastInfo = $info;
            }
        }
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
        $this->supplierPrices = [];
        $this->selectedSupplierLastInfo = null;
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

                // Update supplier_products if supplier selected
                if ($this->supplier_id) {
                    SupplierProduct::updateOrCreate(
                        ['supplier_id' => $this->supplier_id, 'product_id' => $item['product_id']],
                        [
                            'unit_price' => parseMoney($item['unit_cost']),
                            'last_supplied_at' => $this->purchase_date,
                            'last_quantity' => $item['quantity'],
                        ]
                    );
                }
            }

            $totalCost = collect($this->items)->sum(fn ($i) => parseMoney($i['unit_cost']) * $i['quantity']);
            $itemList = collect($this->items)->map(fn ($i) => $i['name'].' x'.$i['quantity'])->join(', ');

            FinancialLedger::record('purchase', [
                'credit' => $totalCost,
                'description' => "Stock purchase — {$itemList}",
                'meta' => ['supplier_id' => $this->supplier_id],
            ]);
        });

        $supplierName = $this->supplier_id ? Supplier::find($this->supplier_id)?->name : '—';
        $itemSummary = collect($this->items)->map(fn ($i) => $i['name'].' x'.$i['quantity'])->join(', ');
        $total = $this->totalAmount;

        $this->reset(['items', 'notes', 'supplier_id', 'selected_product_id', 'line_rate', 'line_quantity']);
        $this->line_quantity = 1;
        $this->purchase_date = now()->format('Y-m-d');
        $this->resetStockInfo();
        $this->supplierPrices = [];
        $this->selectedSupplierLastInfo = null;

        $this->purchaseSummary = [
            'supplier' => $supplierName,
            'items' => $itemSummary,
            'total' => $total,
        ];
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
        $productOpts = Product::orderBy('name')->get()->map(fn ($p) => ['id' => $p->id, 'label' => $p->name]);
        $supplierOpts = Supplier::orderBy('name')->get()->map(fn ($s) => ['id' => $s->id, 'label' => $s->name]);

        return view('livewire.inventory.purchase-point', [
            'productOpts' => $productOpts,
            'supplierOpts' => $supplierOpts,
        ]);
    }
}
