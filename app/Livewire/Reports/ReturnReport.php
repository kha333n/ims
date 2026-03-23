<?php

namespace App\Livewire\Reports;

use App\Models\ProductReturn;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class ReturnReport extends Component
{
    public string $date_from = '';

    public string $date_to = '';

    public bool $generated = false;

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
    }

    public function generate(): void
    {
        $this->validate(['date_from' => 'required|date', 'date_to' => 'required|date|after_or_equal:date_from']);
        $this->generated = true;
    }

    public function render()
    {
        $returns = collect();
        if ($this->generated) {
            $returns = ProductReturn::with(['account.customer', 'account.saleMan', 'account.recoveryMan', 'accountItem.product'])
                ->whereBetween('return_date', [$this->date_from, $this->date_to])
                ->orderBy('return_date', 'desc')
                ->get();
        }

        return view('livewire.reports.return-report', ['returns' => $returns]);
    }
}
