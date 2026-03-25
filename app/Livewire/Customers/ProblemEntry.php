<?php

namespace App\Livewire\Customers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Problem;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProblemEntry extends Component
{
    public ?int $customer_id = null;

    public ?int $account_id = null;

    // Account info
    public ?string $customer_name = null;

    public ?string $customer_phone = null;

    public ?string $items_list = null;

    // Form
    public string $manager = '';

    public string $checker = '';

    public string $branch = '';

    public string $problem_text = '';

    public ?string $previous_promise_date = null;

    public ?string $new_commitment_date = null;

    public string $action_taken = '';

    public bool $closed = false;

    public ?array $actionSummary = null;

    public function updatedCustomerId(): void
    {
        $this->reset(['account_id', 'customer_name', 'customer_phone', 'items_list']);
        $this->resetForm();
    }

    public function updatedAccountId(): void
    {
        $this->resetForm();
        if ($this->account_id) {
            $account = Account::with(['customer', 'items.product', 'problems' => fn ($q) => $q->latest()])->find($this->account_id);
            if ($account) {
                $this->customer_name = $account->customer->name;
                $this->customer_phone = $account->customer->mobile ?? '—';
                $this->items_list = $account->items->pluck('product.name')->filter()->join(', ');

                $lastProblem = $account->problems->first();
                if ($lastProblem) {
                    $this->previous_promise_date = $lastProblem->new_commitment_date?->format('Y-m-d');
                }
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'account_id' => 'required|exists:accounts,id',
            'manager' => 'nullable|string|max:255',
            'checker' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'problem_text' => 'required|string|max:1000',
            'previous_promise_date' => 'nullable|date',
            'new_commitment_date' => 'nullable|date',
            'action_taken' => 'nullable|string|max:1000',
            'closed' => 'boolean',
        ]);

        Problem::create([
            'account_id' => $this->account_id,
            'manager' => $this->manager ?: null,
            'checker' => $this->checker ?: null,
            'branch' => $this->branch ?: null,
            'problem_text' => $this->problem_text,
            'previous_promise_date' => $this->previous_promise_date,
            'new_commitment_date' => $this->new_commitment_date,
            'action_taken' => $this->action_taken ?: null,
            'closed' => $this->closed,
        ]);

        $this->actionSummary = [
            'action' => 'Problem Recorded',
            'detail' => "Acc#{$this->account_id} — {$this->customer_name}".($this->closed ? ' (Closed)' : ''),
        ];

        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['manager', 'checker', 'branch', 'problem_text', 'previous_promise_date', 'new_commitment_date', 'action_taken', 'closed']);
        $this->resetValidation();
    }

    public function render()
    {
        $custOpts = Customer::orderBy('name')->get()->map(fn ($c) => ['id' => $c->id, 'label' => $c->name]);

        $accOpts = collect();
        if ($this->customer_id) {
            $accOpts = Account::where('customer_id', $this->customer_id)->active()->get()
                ->map(fn ($a) => ['id' => $a->id, 'label' => "Acc# {$a->id} — ".formatMoney($a->remaining_amount).' remaining']);
        }

        $history = collect();
        if ($this->account_id) {
            $history = Problem::where('account_id', $this->account_id)->latest()->get();
        }

        return view('livewire.customers.problem-entry', compact('custOpts', 'accOpts', 'history'));
    }
}
