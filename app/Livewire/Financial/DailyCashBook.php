<?php

namespace App\Livewire\Financial;

use App\Models\FinancialLedger;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class DailyCashBook extends Component
{
    public string $date = '';

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function render()
    {
        $opening = FinancialLedger::whereDate('event_date', '<', $this->date)
            ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as balance')
            ->value('balance') ?? 0;

        $entries = FinancialLedger::whereDate('event_date', $this->date)->orderBy('event_date')->get();
        $receipts = $entries->where('debit', '>', 0);
        $payments = $entries->where('credit', '>', 0);
        $closing = $opening + $receipts->sum('debit') - $payments->sum('credit');

        return view('livewire.financial.daily-cash-book', compact('opening', 'receipts', 'payments', 'closing'));
    }
}
