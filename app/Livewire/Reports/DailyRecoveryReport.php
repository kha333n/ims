<?php

namespace App\Livewire\Reports;

use App\Models\Employee;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class DailyRecoveryReport extends Component
{
    public string $date_from = '';

    public string $date_to = '';

    public ?int $recovery_man_id = null;

    public bool $generated = false;

    public function mount(): void
    {
        $this->date_from = now()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
    }

    public function generate(): void
    {
        $this->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'recovery_man_id' => 'required|exists:employees,id',
        ]);
        $this->generated = true;
    }

    public function render()
    {
        $payments = collect();
        $rmName = '';
        $rmArea = '';

        if ($this->generated) {
            $rm = Employee::find($this->recovery_man_id);
            $rmName = $rm?->name ?? '';
            $rmArea = $rm?->area ?? '';

            $payments = Payment::with(['account.customer'])
                ->where('collected_by', $this->recovery_man_id)
                ->whereBetween('payment_date', [$this->date_from, $this->date_to])
                ->orderBy('payment_date')
                ->get()
                ->map(function ($payment) {
                    return [
                        'date' => $payment->payment_date,
                        'account_id' => $payment->account_id,
                        'customer' => $payment->account?->customer?->name ?? '—',
                        'paid_amount' => $payment->amount,
                        'remaining' => $payment->account?->remaining_amount ?? 0,
                        'status' => $payment->account?->status ?? '—',
                    ];
                });
        }

        $rmOpts = Employee::recoveryMen()->orderBy('name')->get()
            ->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);

        return view('livewire.reports.daily-recovery-report', [
            'payments' => $payments,
            'rmName' => $rmName,
            'rmArea' => $rmArea,
            'rmOpts' => $rmOpts,
        ]);
    }
}
