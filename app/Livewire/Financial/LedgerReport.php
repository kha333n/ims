<?php

namespace App\Livewire\Financial;

use App\Models\Customer;
use App\Models\FinancialLedger;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class LedgerReport extends Component
{
    public string $date_from = '';

    public string $date_to = '';

    public string $event_type = 'all';

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
            'event_type' => 'required|in:all,sale,payment,recovery,return,closure,activation,purchase,loss',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $this->generated = true;
    }

    public function render()
    {
        $entries = collect();
        if ($this->generated) {
            $entries = FinancialLedger::with(['customer'])
                ->whereBetween('event_date', [$this->date_from, $this->date_to.' 23:59:59'])
                ->when($this->event_type !== 'all', fn ($q) => $q->where('event_type', $this->event_type))
                ->when($this->customer_id, fn ($q) => $q->where('customer_id', $this->customer_id))
                ->orderBy('event_date')
                ->limit(500)
                ->get();
        }

        $custOpts = Customer::orderBy('name')->get()->map(fn ($c) => ['id' => $c->id, 'label' => $c->name]);

        return view('livewire.financial.ledger-report', ['entries' => $entries, 'custOpts' => $custOpts]);
    }
}
