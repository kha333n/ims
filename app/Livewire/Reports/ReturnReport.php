<?php

namespace App\Livewire\Reports;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\ProductReturn;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class ReturnReport extends Component
{
    public string $date_from = '';

    public string $date_to = '';

    public ?int $sale_man_id = null;

    public ?int $customer_id = null;

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
            'sale_man_id' => 'nullable|exists:employees,id',
            'customer_id' => 'nullable|exists:customers,id',
        ]);
        $this->generated = true;
    }

    public function render()
    {
        $returns = collect();
        $totals = ['total_amount' => 0, 'received' => 0, 'remaining' => 0];

        if ($this->generated) {
            $returns = ProductReturn::with([
                'account.customer',
                'account.saleMan',
                'account.recoveryMan',
                'account.payments',
                'accountItem.product',
            ])
                ->whereBetween('return_date', [$this->date_from, $this->date_to])
                ->when($this->sale_man_id, fn ($q) => $q->whereHas('account', fn ($a) => $a->where('sale_man_id', $this->sale_man_id)))
                ->when($this->customer_id, fn ($q) => $q->whereHas('account', fn ($a) => $a->where('customer_id', $this->customer_id)))
                ->orderBy('return_date', 'desc')
                ->get()
                ->map(function ($ret) {
                    $account = $ret->account;
                    $received = $account?->payments->sum('amount') ?? 0;

                    return [
                        'account_id' => $ret->account_id,
                        'return_date' => $ret->return_date,
                        'customer' => $account?->customer?->name ?? '—',
                        'phone' => $account?->customer?->mobile ?? '—',
                        'recovery_man' => $account?->recoveryMan?->name ?? '—',
                        'sale_man' => $account?->saleMan?->name ?? '—',
                        'item' => $ret->accountItem?->product?->name ?? '—',
                        'quantity' => $ret->quantity,
                        'total_amount' => $account?->total_amount ?? 0,
                        'received' => $received,
                        'remaining' => $account?->remaining_amount ?? 0,
                        'reason' => $ret->reason ?? '—',
                    ];
                });

            $totals = [
                'total_amount' => $returns->sum('total_amount'),
                'received' => $returns->sum('received'),
                'remaining' => $returns->sum('remaining'),
            ];
        }

        $smOpts = Employee::saleMen()->orderBy('name')->get()
            ->map(fn ($e) => ['id' => $e->id, 'label' => $e->name]);

        $customerOpts = Customer::orderBy('name')->get()
            ->map(fn ($c) => ['id' => $c->id, 'label' => $c->name]);

        return view('livewire.reports.return-report', [
            'returns' => $returns,
            'totals' => $totals,
            'smOpts' => $smOpts,
            'customerOpts' => $customerOpts,
        ]);
    }
}
