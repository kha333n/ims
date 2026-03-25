<?php

namespace App\Livewire\Customers;

use App\Models\AccountTransfer as AccountTransferModel;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AccountTransfer extends Component
{
    public ?int $customer_id = null;

    public ?int $to_recovery_man_id = null;

    public string $notes = '';

    public ?array $transferSummary = null;

    // Display data
    public ?string $customer_name = null;

    public ?string $current_rm_name = null;

    public ?int $current_rm_id = null;

    public int $active_account_count = 0;

    public int $total_remaining = 0;

    public function updatedCustomerId(): void
    {
        if ($this->customer_id) {
            $customer = Customer::find($this->customer_id);
            if ($customer) {
                $this->customer_name = $customer->name;
                $activeAccounts = $customer->accounts()->active()->get();
                $this->active_account_count = $activeAccounts->count();
                $this->total_remaining = $activeAccounts->sum('remaining_amount');

                $firstRm = $activeAccounts->first()?->recoveryMan;
                $this->current_rm_name = $firstRm?->name ?? 'Not assigned';
                $this->current_rm_id = $firstRm?->id;

                return;
            }
        }

        $this->resetCustomerInfo();
    }

    public function transfer(): void
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'to_recovery_man_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $customer = Customer::findOrFail($this->customer_id);
        $activeAccounts = $customer->accounts()->active()->get();

        if ($activeAccounts->isEmpty()) {
            $this->addError('customer_id', 'This customer has no active accounts.');

            return;
        }

        DB::transaction(function () use ($activeAccounts) {
            foreach ($activeAccounts as $account) {
                AccountTransferModel::create([
                    'account_id' => $account->id,
                    'from_recovery_man_id' => $account->recovery_man_id,
                    'to_recovery_man_id' => $this->to_recovery_man_id,
                    'transfer_date' => now(),
                    'notes' => $this->notes ?: null,
                ]);

                $account->update(['recovery_man_id' => $this->to_recovery_man_id]);
            }
        });

        $newRmName = Employee::find($this->to_recovery_man_id)?->name ?? '';
        $custName = $this->customer_name;
        $fromRm = $this->current_rm_name;
        $count = $activeAccounts->count();

        $this->transferSummary = [
            'customer' => $custName,
            'from_rm' => $fromRm,
            'to_rm' => $newRmName,
            'accounts' => $count,
        ];

        $this->reset(['customer_id', 'to_recovery_man_id', 'notes']);
        $this->resetCustomerInfo();
    }

    private function resetCustomerInfo(): void
    {
        $this->customer_name = null;
        $this->current_rm_name = null;
        $this->current_rm_id = null;
        $this->active_account_count = 0;
        $this->total_remaining = 0;
    }

    public function render()
    {
        $custOpts = Customer::orderBy('name')->get()->map(fn ($c) => ['id' => $c->id, 'label' => $c->name]);
        $rmOpts = Employee::recoveryMen()->orderBy('name')->get()->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);

        return view('livewire.customers.account-transfer', [
            'custOpts' => $custOpts,
            'rmOpts' => $rmOpts,
        ]);
    }
}
