<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Models\Employee;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class MonthlyRecoveryReport extends Component
{
    public string $date_from = '';

    public string $date_to = '';

    public ?int $recovery_man_id = null;

    public bool $generated = false;

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->endOfMonth()->format('Y-m-d');
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
        $rows = collect();

        if ($this->generated) {
            $rows = Account::with(['customer', 'recoveryMan', 'payments'])
                ->when($this->recovery_man_id, fn ($q) => $q->where('recovery_man_id', $this->recovery_man_id))
                ->whereHas('payments', fn ($q) => $q->whereBetween('payment_date', [$this->date_from, $this->date_to]))
                ->get()
                ->map(function ($account) {
                    $collectedPeriod = $account->payments
                        ->whereBetween('payment_date', [$this->date_from, $this->date_to])
                        ->sum('amount');

                    $collectedAllTime = $account->payments->sum('amount');

                    return [
                        'id' => $account->id,
                        'customer' => $account->customer->name,
                        'phone' => $account->customer->mobile ?? '—',
                        'recovery_man' => $account->recoveryMan?->name ?? '—',
                        'area' => $account->recoveryMan?->area ?? '—',
                        'total' => $account->total_amount,
                        'advance' => $account->advance_amount,
                        'collected_period' => $collectedPeriod,
                        'collected_all_time' => $collectedAllTime,
                        'remaining' => $account->remaining_amount,
                    ];
                });
        }

        $rmOpts = Employee::recoveryMen()->orderBy('name')->get()
            ->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);

        return view('livewire.reports.monthly-recovery-report', ['rows' => $rows, 'rmOpts' => $rmOpts]);
    }
}
