<?php

namespace App\Livewire\Reports;

use App\Models\AccountItem;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class ItemDetailReport extends Component
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
        $items = collect();
        if ($this->generated) {
            $items = AccountItem::with(['product', 'account'])
                ->whereHas('account', fn ($q) => $q->whereBetween('sale_date', [$this->date_from, $this->date_to]))
                ->get()
                ->groupBy(fn ($item) => $item->product?->name ?? 'Unknown')
                ->map(fn ($group, $name) => [
                    'name' => $name,
                    'quantity' => $group->sum('quantity'),
                    'total' => $group->sum(fn ($i) => $i->unit_price * $i->quantity),
                    'rows' => $group->map(fn ($i) => [
                        'date' => $i->account->sale_date,
                        'price' => $i->unit_price,
                        'quantity' => $i->quantity,
                    ]),
                ]);
        }

        return view('livewire.reports.item-detail-report', ['items' => $items]);
    }
}
