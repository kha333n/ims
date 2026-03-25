<?php

namespace App\Livewire\Customers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FinancialLedger;
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

    public bool $showCloseConfirm = false;

    // Summary
    public ?array $actionSummary = null;

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
                    'discount' => $account->discount_amount,
                    'discount_slip' => $account->discount_slip,
                    'closed_at' => $account->closed_at?->format('d/M/Y'),
                ];

                return;
            }
        }

        $this->accountInfo = null;
    }

    public function confirmClose(): void
    {
        $this->validate([
            'account_id' => 'required|exists:accounts,id',
            'discount_amount' => 'required|numeric|min:0',
            'discount_slip' => 'nullable|string|max:255',
        ]);

        $this->showCloseConfirm = true;
    }

    public function cancelClose(): void
    {
        $this->showCloseConfirm = false;
    }

    public function closeAccount(): void
    {
        $this->showCloseConfirm = false;

        $account = Account::findOrFail($this->account_id);
        $discount = parseMoney($this->discount_amount);

        $account->update([
            'status' => 'closed',
            'closed_at' => now(),
            'discount_amount' => $account->discount_amount + $discount,
            'remaining_amount' => max(0, $account->remaining_amount - $discount),
            'discount_slip' => $this->discount_slip ?: null,
        ]);

        $custName = $this->accountInfo['name'] ?? '';
        $accId = $this->account_id;
        $remainingAfter = $account->fresh()->remaining_amount;

        FinancialLedger::record('closure', [
            'account_id' => $accId,
            'customer_id' => $account->customer_id,
            'balance_after' => $remainingAfter,
            'description' => "Account #{$accId} closed".($discount > 0 ? ' with discount '.formatMoney($discount) : ''),
            'meta' => ['discount' => $discount, 'remaining_at_close' => $remainingAfter, 'discount_slip' => $this->discount_slip],
        ]);

        if ($remainingAfter > 0) {
            FinancialLedger::record('loss', [
                'account_id' => $accId,
                'customer_id' => $account->customer_id,
                'credit' => $remainingAfter,
                'balance_after' => $remainingAfter,
                'description' => "Write-off Acc#{$accId} — unpaid balance at closure",
            ]);
        }

        $this->actionSummary = [
            'action' => 'Closed',
            'account_id' => $accId,
            'customer' => $custName,
            'discount' => $discount,
            'remaining' => $remainingAfter,
        ];

        $this->reset(['account_id', 'accountInfo', 'discount_amount', 'discount_slip']);
    }

    public function activateAccount(): void
    {
        $this->validate([
            'account_id' => 'required|exists:accounts,id',
        ]);

        $custName = $this->accountInfo['name'] ?? '';
        $accId = $this->account_id;

        $account = Account::findOrFail($this->account_id);
        $account->update([
            'status' => 'active',
            'closed_at' => null,
        ]);

        FinancialLedger::record('activation', [
            'account_id' => $accId,
            'customer_id' => $account->customer_id,
            'balance_after' => $account->remaining_amount,
            'description' => "Account #{$accId} reactivated",
            'meta' => ['remaining' => $account->remaining_amount],
        ]);

        $this->actionSummary = [
            'action' => 'Activated',
            'account_id' => $accId,
            'customer' => $custName,
            'remaining' => $account->remaining_amount,
        ];

        $this->reset(['account_id', 'accountInfo']);
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

        $rmOpts = $recoveryMen->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);
        $custOpts = $customers->map(fn ($c) => ['id' => $c->id, 'label' => $c->name]);
        $accOpts = $accounts->map(fn ($a) => ['id' => $a->id, 'label' => "Acc# {$a->id} — ".formatMoney($a->remaining_amount).' remaining']);

        return view('livewire.customers.account-closure', [
            'rmOpts' => $rmOpts,
            'custOpts' => $custOpts,
            'accOpts' => $accOpts,
        ]);
    }
}
