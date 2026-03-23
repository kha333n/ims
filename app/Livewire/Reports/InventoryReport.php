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
        $products = Product::with('supplier')->orderBy('name')->get();

        return view('livewire.reports.inventory-report', ['products' => $products]);
    }
}
