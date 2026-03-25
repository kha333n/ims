<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use App\Models\FinancialLedger;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ExpenseEntry extends Component
{
    public string $amount = '';

    public string $description = '';

    public string $expense_date = '';

    public string $category = '';

    public ?array $actionSummary = null;

    public function mount(): void
    {
        $this->expense_date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $this->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:500',
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:255',
        ]);

        $amountPaisas = parseMoney($this->amount);

        /** @var User $user */
        $user = auth()->user();

        $expense = Expense::create([
            'amount' => $amountPaisas,
            'description' => $this->description,
            'expense_date' => $this->expense_date,
            'category' => $this->category ?: null,
            'recorded_by' => $user->id,
            'employee_id' => $user->employee_id,
        ]);

        FinancialLedger::record('expense', [
            'credit' => $amountPaisas,
            'employee_id' => $user->employee_id,
            'description' => 'Expense: '.$this->description,
        ]);

        $this->actionSummary = [
            'id' => $expense->id,
            'description' => $this->description,
            'category' => $this->category ?: '—',
            'amount' => $amountPaisas,
            'date' => $this->expense_date,
        ];

        $this->amount = '';
        $this->description = '';
        $this->category = '';
        $this->expense_date = now()->format('Y-m-d');
    }

    public function render()
    {
        $todayExpenses = Expense::whereDate('expense_date', now()->format('Y-m-d'))
            ->orderByDesc('created_at')
            ->get();

        $categories = Expense::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('livewire.expenses.expense-entry', compact('todayExpenses', 'categories'));
    }
}
