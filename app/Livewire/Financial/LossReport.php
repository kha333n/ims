<?php

namespace App\Livewire\Financial;

use App\Models\Account;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class LossReport extends Component
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
        $losses = collect();
        if ($this->generated) {
            $losses = Account::with('customer')
                ->where('status', 'closed')
                ->whereBetween('closed_at', [$this->date_from, $this->date_to])
                ->where('remaining_amount', '>', 0)
                ->orderBy('closed_at', 'desc')
                ->get();
        }

        return view('livewire.financial.loss-report', ['losses' => $losses]);
    }
}
