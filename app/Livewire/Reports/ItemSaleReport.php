<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Models\Employee;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class ItemSaleReport extends Component
{
    public string $date_from = '';

    public string $date_to = '';

    public ?int $recovery_man_id = null;

    public bool $generated = false;

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
    }

    public function generate(): void
    {
        $this->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $this->generated = true;
    }

    public function render()
    {
        $accounts = collect();
        $totals = ['total' => 0, 'advance' => 0, 'paid' => 0, 'balance' => 0];

        if ($this->generated) {
            $accounts = Account::with(['customer', 'saleMan', 'recoveryMan', 'items.product', 'payments'])
                ->whereBetween('sale_date', [$this->date_from, $this->date_to])
                ->when($this->recovery_man_id, fn ($q) => $q->where('recovery_man_id', $this->recovery_man_id))
                ->orderBy('sale_date', 'desc')
                ->get()
                ->map(function ($account) {
                    $paid = $account->payments->sum('amount');

                    return [
                        'id' => $account->id,
                        'date' => $account->sale_date,
                        'sale_man' => $account->saleMan?->name ?? '—',
                        'recovery_man' => $account->recoveryMan?->name ?? '—',
                        'customer_id' => $account->customer_id,
                        'customer' => $account->customer->name,
                        'address' => $account->customer->home_address ?? '—',
                        'phone' => $account->customer->mobile ?? '—',
                        'items' => $account->items->pluck('product.name')->filter()->join(', '),
                        'total' => $account->total_amount,
                        'advance' => $account->advance_amount,
                        'paid' => $paid,
                        'balance' => $account->remaining_amount,
                    ];
                });

            $totals = [
                'total' => $accounts->sum('total'),
                'advance' => $accounts->sum('advance'),
                'paid' => $accounts->sum('paid'),
                'balance' => $accounts->sum('balance'),
            ];
        }

        $rmOpts = Employee::recoveryMen()->orderBy('name')->get()
            ->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);

        return view('livewire.reports.item-sale-report', [
            'accounts' => $accounts,
            'totals' => $totals,
            'rmOpts' => $rmOpts,
        ]);
    }
}
