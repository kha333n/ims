<?php

namespace App\Livewire\HR;

use App\Models\CommissionRecord;
use App\Models\Employee;
use App\Models\FinancialLedger;
use App\Models\PayrollEntry;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Payroll extends Component
{
    public ?array $actionSummary = null;

    // Pay modal
    public bool $showPayModal = false;

    public ?int $payingEmployeeId = null;

    public ?string $payingEmployeeName = null;

    public string $payAmount = '';

    public string $payDescription = '';

    public function openPayModal(int $employeeId): void
    {
        $employee = Employee::findOrFail($employeeId);
        $this->payingEmployeeId = $employee->id;
        $this->payingEmployeeName = $employee->name;
        $this->payAmount = '';
        $this->payDescription = '';
        $this->showPayModal = true;
    }

    public function closePayModal(): void
    {
        $this->showPayModal = false;
        $this->payingEmployeeId = null;
    }

    public function processPayment(): void
    {
        $this->validate([
            'payingEmployeeId' => 'required|exists:employees,id',
            'payAmount' => 'required|numeric|min:1',
            'payDescription' => 'nullable|string|max:500',
        ]);

        $amount = parseMoney($this->payAmount);
        $employee = Employee::findOrFail($this->payingEmployeeId);

        PayrollEntry::create([
            'employee_id' => $employee->id,
            'entry_type' => 'payment',
            'amount' => $amount,
            'period_month' => now()->format('Y-m'),
            'description' => $this->payDescription ?: 'Payroll payment',
            'recorded_by' => auth()->id(),
        ]);

        $employee->decrement('balance', $amount);

        // Mark pending commission records as paid (oldest first)
        $remainingToMark = $amount;
        $pendingCommissions = CommissionRecord::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->orderBy('id')
            ->get();

        foreach ($pendingCommissions as $commission) {
            if ($remainingToMark <= 0) {
                break;
            }
            $commission->update(['status' => 'paid', 'paid_at' => now()]);
            $remainingToMark -= $commission->amount;
        }

        FinancialLedger::record('payroll', [
            'employee_id' => $employee->id,
            'credit' => $amount,
            'description' => "Payroll payment to {$employee->name}",
        ]);

        $this->actionSummary = [
            'action' => 'Payment Processed',
            'detail' => formatMoney($amount)." paid to {$employee->name}",
        ];

        $this->showPayModal = false;
        $this->payingEmployeeId = null;
    }

    public function accrueSalaries(): void
    {
        $currentMonth = now()->format('Y-m');
        $count = 0;

        Employee::where('salary', '>', 0)->each(function (Employee $employee) use ($currentMonth, &$count) {
            $alreadyAccrued = PayrollEntry::where('employee_id', $employee->id)
                ->where('entry_type', 'salary_accrual')
                ->where('period_month', $currentMonth)
                ->exists();

            if (! $alreadyAccrued) {
                PayrollEntry::create([
                    'employee_id' => $employee->id,
                    'entry_type' => 'salary_accrual',
                    'amount' => $employee->salary,
                    'period_month' => $currentMonth,
                    'description' => "Monthly salary for {$currentMonth}",
                ]);

                $employee->increment('balance', $employee->salary);
                $count++;
            }
        });

        if ($count > 0) {
            $this->actionSummary = ['action' => 'Salaries Accrued', 'detail' => "{$count} employees for {$currentMonth}"];
        } else {
            $this->actionSummary = ['action' => 'Already Accrued', 'detail' => "Salaries for {$currentMonth} were already accrued."];
        }
    }

    public function render()
    {
        $currentMonth = now()->format('Y-m');
        $monthRange = [Carbon::now()->startOfMonth(), Carbon::now()];

        $employees = Employee::orderByRaw("CASE type WHEN 'sale_man' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get()
            ->map(function (Employee $emp) use ($currentMonth, $monthRange) {
                $salaryAccrued = PayrollEntry::where('employee_id', $emp->id)
                    ->where('entry_type', 'salary_accrual')
                    ->where('period_month', $currentMonth)
                    ->exists();

                $pendingCommission = CommissionRecord::where('employee_id', $emp->id)
                    ->where('status', 'pending')
                    ->sum('amount');

                $commissionThisMonth = CommissionRecord::where('employee_id', $emp->id)
                    ->whereBetween('created_at', $monthRange)
                    ->sum('amount');

                $paidThisMonth = PayrollEntry::where('employee_id', $emp->id)
                    ->where('entry_type', 'payment')
                    ->where('period_month', $currentMonth)
                    ->sum('amount');

                return [
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'type' => $emp->type === 'sale_man' ? 'Sale Man' : 'Recovery Man',
                    'salary' => $emp->salary,
                    'commission_percent' => $emp->commission_percent,
                    'balance' => $emp->balance,
                    'salary_accrued' => $salaryAccrued,
                    'pending_commission' => $pendingCommission,
                    'commission_this_month' => $commissionThisMonth,
                    'paid_this_month' => $paidThisMonth,
                    'total_due' => $emp->balance + $pendingCommission + ($salaryAccrued ? 0 : $emp->salary),
                ];
            });

        return view('livewire.h-r.payroll', ['employees' => $employees, 'currentMonth' => $currentMonth]);
    }
}
