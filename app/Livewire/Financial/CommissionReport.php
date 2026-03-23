<?php

namespace App\Livewire\Financial;

use App\Models\Account;
use App\Models\Employee;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class CommissionReport extends Component
{
    public string $date_from = '';

    public string $date_to = '';

    public ?int $sale_man_id = null;

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
        $detail = collect();
        if ($this->generated) {
            $sms = Employee::saleMen()
                ->when($this->sale_man_id, fn ($q) => $q->where('id', $this->sale_man_id))
                ->orderBy('name')->get();

            $rows = $sms->map(function ($sm) {
                $accounts = Account::where('sale_man_id', $sm->id)
                    ->whereBetween('sale_date', [$this->date_from, $this->date_to])->get();
                $totalSales = $accounts->sum('total_amount');
                $commission = (int) ($totalSales * ($sm->commission_percent ?? 0) / 100);

                return [
                    'name' => $sm->name, 'count' => $accounts->count(), 'total' => $totalSales,
                    'percent' => $sm->commission_percent ?? 0, 'commission' => $commission,
                ];
            });

            if ($this->sale_man_id) {
                $detail = Account::with('customer')
                    ->where('sale_man_id', $this->sale_man_id)
                    ->whereBetween('sale_date', [$this->date_from, $this->date_to])
                    ->orderBy('sale_date', 'desc')->get();
            }
        }

        $smOpts = Employee::saleMen()->orderBy('name')->get()->map(fn ($e) => ['id' => $e->id, 'label' => $e->name]);

        return view('livewire.financial.commission-report', ['rows' => $rows, 'detail' => $detail, 'smOpts' => $smOpts]);
    }
}
