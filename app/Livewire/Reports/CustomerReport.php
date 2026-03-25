<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class CustomerReport extends Component
{
    public ?int $account_id = null;

    public ?int $customer_id = null;

    /** @var 'account'|'customer' */
    public string $filter_mode = 'account';

    public bool $generated = false;

    public function generate(): void
    {
        if ($this->filter_mode === 'account') {
            $this->validate(['account_id' => 'required|exists:accounts,id']);
        } else {
            $this->validate(['customer_id' => 'required|exists:customers,id']);
        }

        $this->generated = true;
    }

    public function updatedFilterMode(): void
    {
        $this->generated = false;
        $this->account_id = null;
        $this->customer_id = null;
    }

    public function render()
    {
        $account = null;
        $customer = null;
        $accounts = collect();

        if ($this->generated) {
            if ($this->filter_mode === 'account' && $this->account_id) {
                $account = Account::with(['customer', 'saleMan', 'recoveryMan', 'items.product', 'payments'])
                    ->find($this->account_id);
            } elseif ($this->filter_mode === 'customer' && $this->customer_id) {
                $customer = Customer::find($this->customer_id);
                $accounts = Account::with(['saleMan', 'recoveryMan', 'items.product', 'payments'])
                    ->where('customer_id', $this->customer_id)
                    ->orderBy('sale_date', 'desc')
                    ->get();
            }
        }

        $accOpts = Account::with('customer')->orderBy('id', 'desc')->get()
            ->map(fn ($a) => ['id' => $a->id, 'label' => "Acc# {$a->id} — {$a->customer->name}"]);

        $customerOpts = Customer::orderBy('name')->get()
            ->map(fn ($c) => ['id' => $c->id, 'label' => "#{$c->id} {$c->name}"]);

        return view('livewire.reports.customer-report', [
            'account' => $account,
            'customer' => $customer,
            'accounts' => $accounts,
            'accOpts' => $accOpts,
            'customerOpts' => $customerOpts,
        ]);
    }
}
