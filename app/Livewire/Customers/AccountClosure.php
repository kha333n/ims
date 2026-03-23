<?php

namespace App\Livewire\Customers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Employee;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AccountClosure extends Component
{
    public string $mode = 'close'; // close or activate

    public ?int $recovery_man_id = null;

    public ?int $customer_id = null;

    public ?int $account_id = null;

    // Close fields
    public string $discount_amount = '0';

    public string $discount_slip = '';

    // Display
    public ?array $accountInfo = null;

    public function updatedMode(): void
    {
        $this->reset(['recovery_man_id', 'customer_id', 'account_id', 'discount_amount', 'discount_slip', 'accountInfo']);
    }

    public function updatedRecoveryManId(): void
    {
        $this->reset(['customer_id', 'account_id', 'accountInfo']);
    }

    public function updatedCustomerId(): void
    {
        $this->reset(['account_id', 'accountInfo']);
    }

    public function updatedAccountId(): void
    {
        if ($this->account_id) {
            $account = Account::with(['customer', 'recoveryMan'])->find($this->account_id);
            if ($account) {
                $this->accountInfo = [
                    'name' => $account->customer->name,
                    'address' => $account->customer->home_address ?? '—',
                    'contact' => $account->customer->mobile ?? '—',
                    'total' => $account->total_amount,
                    'paid' => $account->total_amount - $account->remaining_amount,
                    'remaining' => $account->remaining_amount,
                    'status' => $account->status,
                ];

                return;
            }
        }

        $this->accountInfo = null;
    }

    public function closeAccount(): void
    {
        $this->validate([
            'account_id' => 'required|exists:accounts,id',
            'discount_amount' => 'required|numeric|min:0',
        ]);

        $account = Account::findOrFail($this->account_id);
        $discount = parseMoney($this->discount_amount);

        $account->update([
            'status' => 'closed',
            'closed_at' => now(),
            'discount_amount' => $account->discount_amount + $discount,
            'remaining_amount' => max(0, $account->remaining_amount - $discount),
            'discount_slip' => $this->discount_slip ?: null,
        ]);

        $this->reset(['account_id', 'accountInfo', 'discount_amount', 'discount_slip']);
        session()->flash('success', 'Account closed successfully.');
    }

    public function activateAccount(): void
    {
        $this->validate([
            'account_id' => 'required|exists:accounts,id',
        ]);

        $account = Account::findOrFail($this->account_id);
        $account->update([
            'status' => 'active',
            'closed_at' => null,
        ]);

        $this->reset(['account_id', 'accountInfo']);
        session()->flash('success', 'Account activated successfully.');
    }

    public function render()
    {
        $recoveryMen = Employee::recoveryMen()->orderBy('name')->get();

        $customers = collect();
        if ($this->recovery_man_id) {
            $status = $this->mode === 'close' ? 'active' : 'closed';
            $customerIds = Account::where('recovery_man_id', $this->recovery_man_id)
                ->where('status', $status)
                ->pluck('customer_id')
                ->unique();
            $customers = Customer::whereIn('id', $customerIds)->orderBy('name')->get();
        }

        $accounts = collect();
        if ($this->customer_id) {
            $status = $this->mode === 'close' ? 'active' : 'closed';
            $accounts = Account::where('customer_id', $this->customer_id)
                ->where('status', $status)
                ->when($this->recovery_man_id, fn ($q) => $q->where('recovery_man_id', $this->recovery_man_id))
                ->get();
        }

        return view('livewire.customers.account-closure', [
            'recoveryMen' => $recoveryMen,
            'customers' => $customers,
            'accounts' => $accounts,
        ]);
    }
}
