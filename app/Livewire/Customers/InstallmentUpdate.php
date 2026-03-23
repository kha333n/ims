<?php

namespace App\Livewire\Customers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\InstallmentPlanChange;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class InstallmentUpdate extends Component
{
    public ?int $customer_id = null;

    public ?int $account_id = null;

    // Current plan display
    public ?string $current_type = null;

    public ?int $current_day = null;

    public ?int $current_amount = null;

    public ?int $remaining_amount = null;

    public ?string $customer_address = null;

    public ?string $customer_mobile = null;

    // New plan
    public string $new_type = '';

    public ?int $new_day = null;

    public string $new_amount = '';

    public function updatedCustomerId(): void
    {
        $this->reset(['account_id', 'current_type', 'current_day', 'current_amount', 'remaining_amount', 'customer_address', 'customer_mobile', 'new_type', 'new_day', 'new_amount']);
    }

    public function updatedAccountId(): void
    {
        if ($this->account_id) {
            $account = Account::with('customer')->find($this->account_id);
            if ($account) {
                $this->current_type = $account->installment_type;
                $this->current_day = $account->installment_day;
                $this->current_amount = $account->installment_amount;
                $this->remaining_amount = $account->remaining_amount;
                $this->customer_address = $account->customer->home_address ?? '—';
                $this->customer_mobile = $account->customer->mobile ?? '—';
                $this->new_type = $account->installment_type ?? '';
                $this->new_day = $account->installment_day;
                $this->new_amount = $account->installment_amount ? (string) ($account->installment_amount / 100) : '';

                return;
            }
        }

        $this->current_type = null;
        $this->remaining_amount = null;
    }

    public function getPeriodsToCompleteProperty(): ?int
    {
        if (! $this->remaining_amount || ! $this->new_amount || (float) $this->new_amount <= 0) {
            return null;
        }

        $amountInPaisas = parseMoney($this->new_amount);

        if ($amountInPaisas <= 0) {
            return null;
        }

        return (int) ceil($this->remaining_amount / $amountInPaisas);
    }

    public function getPeriodLabelProperty(): string
    {
        return match ($this->new_type) {
            'daily' => 'days',
            'weekly' => 'weeks',
            'monthly' => 'months',
            default => 'periods',
        };
    }

    public function save(): void
    {
        $rules = [
            'account_id' => 'required|exists:accounts,id',
            'new_type' => 'required|in:daily,weekly,monthly',
            'new_amount' => 'required|numeric|min:1',
        ];

        if ($this->new_type === 'weekly') {
            $rules['new_day'] = 'required|integer|min:1|max:7';
        } elseif ($this->new_type === 'monthly') {
            $rules['new_day'] = 'required|integer|min:1|max:31';
        }

        $this->validate($rules);

        $account = Account::findOrFail($this->account_id);

        $dayValue = $this->new_type === 'daily' ? null : $this->new_day;

        InstallmentPlanChange::create([
            'account_id' => $account->id,
            'old_type' => $account->installment_type,
            'old_day' => $account->installment_day,
            'old_amount' => $account->installment_amount,
            'new_type' => $this->new_type,
            'new_day' => $dayValue,
            'new_amount' => parseMoney($this->new_amount),
            'changed_at' => now(),
        ]);

        $account->update([
            'installment_type' => $this->new_type,
            'installment_day' => $dayValue,
            'installment_amount' => parseMoney($this->new_amount),
        ]);

        $this->current_type = $this->new_type;
        $this->current_day = $dayValue;
        $this->current_amount = parseMoney($this->new_amount);

        session()->flash('success', 'Installment plan updated successfully.');
    }

    public function render()
    {
        $customers = Customer::orderBy('name')->get();

        $accounts = collect();
        if ($this->customer_id) {
            $accounts = Account::where('customer_id', $this->customer_id)
                ->active()
                ->get();
        }

        return view('livewire.customers.installment-update', [
            'customers' => $customers,
            'accounts' => $accounts,
        ]);
    }
}
