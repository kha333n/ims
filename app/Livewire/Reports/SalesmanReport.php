<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Models\Employee;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class SalesmanReport extends Component
{
    public ?int $sale_man_id = null;

    public string $date_from = '';

    public string $date_to = '';

    public string $status = 'all';

    public bool $generated = false;

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
    }

    public function generate(): void
    {
        $this->validate([
            'sale_man_id' => 'required|exists:employees,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);
        $this->generated = true;
    }

    public function render()
    {
        $accounts = collect();
        if ($this->generated) {
            $accounts = Account::with(['customer', 'saleMan', 'items.product'])
                ->where('sale_man_id', $this->sale_man_id)
                ->whereBetween('sale_date', [$this->date_from, $this->date_to])
                ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
                ->orderBy('sale_date', 'desc')
                ->get();
        }

        $smOpts = Employee::saleMen()->orderBy('name')->get()
            ->map(fn ($e) => ['id' => $e->id, 'label' => $e->name]);

        return view('livewire.reports.salesman-report', ['accounts' => $accounts, 'smOpts' => $smOpts]);
    }
}
