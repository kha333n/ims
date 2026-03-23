<?php

namespace App\Livewire\Financial;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class InventoryValuationReport extends Component
{
    public function render()
    {
        $products = Product::orderBy('name')->get()->map(fn ($p) => [
            'name' => $p->name, 'quantity' => $p->quantity,
            'purchase_price' => $p->purchase_price, 'sale_price' => $p->sale_price,
            'cost_value' => $p->quantity * $p->purchase_price,
            'sale_value' => $p->quantity * $p->sale_price,
            'profit' => $p->quantity * ($p->sale_price - $p->purchase_price),
        ]);

        return view('livewire.financial.inventory-valuation-report', ['products' => $products]);
    }
}
