<?php

namespace App\Livewire\Financial;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class CollectionReport extends Component
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
        $this->validate(['date_from' => 'required|date', 'date_to' => 'required|date|after_or_equal:date_from']);
        $this->generated = true;
    }

    public function render()
    {
        $rows = collect();
        if ($this->generated) {
            $rms = Employee::recoveryMen()
                ->when($this->recovery_man_id, fn ($q) => $q->where('id', $this->recovery_man_id))
                ->orderBy('name')->get();

            $rows = $rms->map(function ($rm) {
                $accountCount = Account::where('recovery_man_id', $rm->id)->where('status', 'active')->count();
                $expected = Account::where('recovery_man_id', $rm->id)->where('status', 'active')->sum('installment_amount');
                $collected = Payment::whereHas('account', fn ($q) => $q->where('recovery_man_id', $rm->id))
                    ->whereBetween('payment_date', [$this->date_from, $this->date_to])
                    ->sum('amount');
                $shortfall = max(0, $expected - $collected);
                $rate = $expected > 0 ? round(($collected / $expected) * 100, 1) : 0;

                return [
                    'name' => $rm->name, 'area' => $rm->area ?? '—', 'accounts' => $accountCount,
                    'expected' => $expected, 'collected' => $collected, 'shortfall' => $shortfall, 'rate' => $rate,
                ];
            });
        }

        $rmOpts = Employee::recoveryMen()->orderBy('name')->get()->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);

        return view('livewire.financial.collection-report', ['rows' => $rows, 'rmOpts' => $rmOpts]);
    }
}
