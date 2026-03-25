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

    public ?int $sale_man_id = null;

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
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'status' => 'required|in:all,active,closed',
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
                ->when($this->sale_man_id, fn ($q) => $q->where('sale_man_id', $this->sale_man_id))
                ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
                ->orderBy('sale_date', 'desc')
                ->get()
                ->map(function ($account) {
                    $paid = $account->payments->sum('amount');

                    return [
                        'id' => $account->id,
                        'date' => $account->sale_date,
                        'sale_man' => $account->saleMan?->name ?? '—',
                        'recovery_man' => $account->recoveryMan?->name ?? '—',
                        'customer' => $account->customer->name,
                        'phone' => $account->customer->mobile ?? '—',
                        'items' => $account->items->map(fn ($i) => $i->product?->name)->filter()->join(', '),
                        'quantity' => $account->items->sum('quantity'),
                        'total' => $account->total_amount,
                        'advance' => $account->advance_amount,
                        'paid' => $paid,
                        'balance' => $account->remaining_amount,
                        'status' => $account->status,
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

        $smOpts = Employee::saleMen()->orderBy('name')->get()
            ->map(fn ($e) => ['id' => $e->id, 'label' => $e->name]);

        return view('livewire.reports.item-sale-report', [
            'accounts' => $accounts,
            'totals' => $totals,
            'rmOpts' => $rmOpts,
            'smOpts' => $smOpts,
        ]);
    }
}
