<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class InventoryReport extends Component
{
    public function render()
    {
        $products = Product::with(['supplier', 'purchases' => function ($q) {
            $q->with('supplier')->where('remaining_qty', '>', 0)->orderBy('purchase_date');
        }])->orderBy('name')->get();

        $grandCostValue = 0;
        $grandSaleValue = 0;

        foreach ($products as $product) {
            $product->batch_cost_value = $product->purchases->sum(fn ($p) => $p->remaining_qty * $p->unit_cost);
            $product->batch_sale_value = $product->purchases->sum(fn ($p) => $p->remaining_qty * $product->sale_price);
            $product->total_stock = $product->purchases->sum('remaining_qty');
            $grandCostValue += $product->batch_cost_value;
            $grandSaleValue += $product->batch_sale_value;
        }

        return view('livewire.reports.inventory-report', [
            'products' => $products,
            'grandCostValue' => $grandCostValue,
            'grandSaleValue' => $grandSaleValue,
        ]);
    }
}
