<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class CustomerReport extends Component
{
    public ?int $account_id = null;

    public bool $generated = false;

    public function generate(): void
    {
        $this->validate(['account_id' => 'required|exists:accounts,id']);
        $this->generated = true;
    }

    public function render()
    {
        $account = null;
        if ($this->generated && $this->account_id) {
            $account = Account::with(['customer', 'saleMan', 'recoveryMan', 'items.product', 'payments'])
                ->find($this->account_id);
        }

        $accOpts = Account::with('customer')->orderBy('id', 'desc')->get()
            ->map(fn ($a) => ['id' => $a->id, 'label' => "Acc# {$a->id} — {$a->customer->name}"]);

        return view('livewire.reports.customer-report', ['account' => $account, 'accOpts' => $accOpts]);
    }
}
