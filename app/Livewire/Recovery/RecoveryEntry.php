<?php

namespace App\Livewire\Recovery;

use App\Models\Account;
use App\Models\Employee;
use App\Models\FinancialLedger;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class RecoveryEntry extends Component
{
    public ?int $recovery_man_id = null;

    public string $category = '';

    public bool $loaded = false;

    /** @var array<int, bool> */
    public array $checked = [];

    /** @var array<int, string> — editable amount per account */
    public array $amounts = [];

    /** @var array<int, bool> — which accounts already paid today */
    public array $paidToday = [];

    public ?array $actionSummary = null;

    public bool $showDuplicateWarning = false;

    public int $duplicateCount = 0;

    public function load(): void
    {
        $this->validate([
            'recovery_man_id' => 'required|exists:employees,id',
            'category' => 'required|in:daily,weekly,monthly',
        ]);

        $this->loaded = true;
        $this->checked = [];
        $this->amounts = [];
        $this->paidToday = [];
        $this->actionSummary = null;

        $accounts = Account::where('recovery_man_id', $this->recovery_man_id)
            ->where('installment_type', $this->category)
            ->where('status', 'active')
            ->where('remaining_amount', '>', 0)
            ->get();

        foreach ($accounts as $account) {
            $this->amounts[$account->id] = (string) ($account->installment_amount / 100);

            $this->paidToday[$account->id] = Payment::where('account_id', $account->id)
                ->whereDate('payment_date', today())
                ->where('transaction_type', 'installment')
                ->exists();
        }
    }

    public function updateStatus(): void
    {
        $selected = array_keys(array_filter($this->checked));

        if (empty($selected)) {
            $this->addError('checked', 'Select at least one account.');

            return;
        }

        foreach ($selected as $accountId) {
            $amount = $this->amounts[$accountId] ?? '';
            if ($amount !== '' && (! is_numeric($amount) || (float) $amount <= 0)) {
                $this->addError("amounts.{$accountId}", 'Amount must be a positive number.');

                return;
            }
        }

        // Check for duplicates before processing
        $dupes = 0;
        foreach ($selected as $accountId) {
            if ($this->paidToday[$accountId] ?? false) {
                $dupes++;
            }
        }

        if ($dupes > 0) {
            $this->duplicateCount = $dupes;
            $this->showDuplicateWarning = true;

            return;
        }

        $this->processPayments();
    }

    public function cancelDuplicateWarning(): void
    {
        $this->showDuplicateWarning = false;
        $this->duplicateCount = 0;
    }

    public function confirmDuplicateUpdate(): void
    {
        $this->showDuplicateWarning = false;
        $this->duplicateCount = 0;
        $this->processPayments();
    }

    private function processPayments(): void
    {
        $selected = array_keys(array_filter($this->checked));
        $count = 0;
        $totalAmount = 0;
        $duplicates = 0;

        foreach ($selected as $accountId) {
            $account = Account::find($accountId);
            if (! $account || $account->status !== 'active') {
                continue;
            }

            $enteredAmount = isset($this->amounts[$accountId])
                ? parseMoney($this->amounts[$accountId])
                : $account->installment_amount;

            if ($enteredAmount <= 0) {
                continue;
            }

            $isDuplicate = $this->paidToday[$accountId] ?? false;
            if ($isDuplicate) {
                $duplicates++;
            }

            Payment::create([
                'account_id' => $accountId,
                'amount' => $enteredAmount,
                'transaction_type' => 'installment',
                'payment_date' => today(),
                'collected_by' => $this->recovery_man_id,
                'remarks' => $isDuplicate ? 'Duplicate entry (same day)' : null,
            ]);

            $account->decrement('remaining_amount', min($enteredAmount, $account->remaining_amount));

            FinancialLedger::record('recovery', [
                'account_id' => $accountId,
                'customer_id' => $account->customer_id,
                'employee_id' => $this->recovery_man_id,
                'debit' => $enteredAmount,
                'balance_after' => $account->fresh()->remaining_amount,
                'description' => "Recovery collection Acc#{$accountId}".($isDuplicate ? ' (duplicate)' : ''),
            ]);

            $count++;
            $totalAmount += $enteredAmount;
        }

        $rmName = Employee::find($this->recovery_man_id)?->name ?? '';

        $this->actionSummary = [
            'action' => 'Recovery Updated',
            'rm' => $rmName,
            'category' => ucfirst($this->category),
            'count' => $count,
            'total' => $totalAmount,
            'duplicates' => $duplicates,
        ];

        $this->checked = [];
        $summary = $this->actionSummary;
        $this->load();
        $this->actionSummary = $summary;
    }

    public function render()
    {
        $rmOpts = Employee::recoveryMen()->orderBy('name')->get()
            ->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);

        $accounts = collect();
        if ($this->loaded && $this->recovery_man_id && $this->category) {
            $accounts = Account::with('customer')
                ->where('recovery_man_id', $this->recovery_man_id)
                ->where('installment_type', $this->category)
                ->where('status', 'active')
                ->where('remaining_amount', '>', 0)
                ->orderBy('id')
                ->get();
        }

        return view('livewire.recovery.recovery-entry', [
            'rmOpts' => $rmOpts,
            'accounts' => $accounts,
        ]);
    }
}
