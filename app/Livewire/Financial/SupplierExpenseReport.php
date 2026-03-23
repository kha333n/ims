<?php

namespace App\Livewire\Financial;

use App\Models\Purchase;
use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class SupplierExpenseReport extends Component
{
    public string $date_from = '';

    public string $date_to = '';

    public ?int $supplier_id = null;

    public bool $generated = false;

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
    }

    public function generate(): void
    {
        $this->generated = true;
    }

    public function render()
    {
        $groups = collect();
        if ($this->generated) {
            $groups = Purchase::with(['product', 'supplier'])
                ->whereBetween('purchase_date', [$this->date_from, $this->date_to])
                ->when($this->supplier_id, fn ($q) => $q->where('supplier_id', $this->supplier_id))
                ->orderBy('supplier_id')
                ->get()
                ->groupBy(fn ($p) => $p->supplier?->name ?? 'No Supplier');
        }

        $supplierOpts = Supplier::orderBy('name')->get()->map(fn ($s) => ['id' => $s->id, 'label' => $s->name]);

        return view('livewire.financial.supplier-expense-report', ['groups' => $groups, 'supplierOpts' => $supplierOpts]);
    }
}
