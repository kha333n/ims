<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CustomerDetail extends Component
{
    public Customer $customer;

    public ?array $actionSummary = null;

    // Edit mode
    public bool $editing = false;

    public string $name = '';

    public string $father_name = '';

    public string $mobile = '';

    public string $cnic = '';

    public string $reference = '';

    public string $home_address = '';

    public string $shop_address = '';

    // Payment form
    public ?int $payment_account_id = null;

    public string $payment_amount = '';

    public string $transaction_type = 'installment';

    public string $payment_date = '';

    public string $payment_remarks = '';

    public function mount(int $id): void
    {
        $this->customer = Customer::findOrFail($id);
        $this->payment_date = now()->format('Y-m-d');
    }

    public function startEdit(): void
    {
        $this->name = $this->customer->name;
        $this->father_name = $this->customer->father_name ?? '';
        $this->mobile = $this->customer->mobile ?? '';
        $this->cnic = $this->customer->cnic ?? '';
        $this->reference = $this->customer->reference ?? '';
        $this->home_address = $this->customer->home_address ?? '';
        $this->shop_address = $this->customer->shop_address ?? '';
        $this->editing = true;
    }

    public function cancelEdit(): void
    {
        $this->editing = false;
        $this->resetValidation();
    }

    public function saveCustomer(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|max:20',
            'reference' => 'nullable|string|max:255',
            'home_address' => 'nullable|string|max:500',
            'shop_address' => 'nullable|string|max:500',
        ]);

        $this->customer->update([
            'name' => $this->name,
            'father_name' => $this->father_name ?: null,
            'mobile' => $this->mobile ?: null,
            'cnic' => $this->cnic ?: null,
            'reference' => $this->reference ?: null,
            'home_address' => $this->home_address ?: null,
            'shop_address' => $this->shop_address ?: null,
        ]);

        $this->actionSummary = ['action' => 'Customer Updated', 'detail' => $this->name];
        $this->editing = false;
    }

    public function savePayment(): void
    {
        $this->validate([
            'payment_account_id' => 'required|exists:accounts,id',
            'payment_amount' => 'required|numeric|min:1',
            'transaction_type' => 'required|in:installment,advance,manual',
            'payment_date' => 'required|date',
            'payment_remarks' => 'nullable|string|max:500',
        ]);

        $amount = parseMoney($this->payment_amount);

        Payment::create([
            'account_id' => $this->payment_account_id,
            'amount' => $amount,
            'transaction_type' => $this->transaction_type,
            'payment_date' => $this->payment_date,
            'remarks' => $this->payment_remarks ?: null,
        ]);

        $accId = $this->payment_account_id;
        $txType = $this->transaction_type;

        $account = $this->customer->accounts()->find($accId);
        $account->decrement('remaining_amount', $amount);

        $this->reset(['payment_account_id', 'payment_amount', 'transaction_type', 'payment_remarks']);
        $this->transaction_type = 'installment';
        $this->payment_date = now()->format('Y-m-d');

        $this->customer->refresh();

        $this->actionSummary = [
            'action' => 'Payment Recorded',
            'detail' => formatMoney($amount)." on Acc# {$accId} ({$txType})",
        ];
    }

    public function render()
    {
        $accounts = $this->customer->accounts()
            ->with(['items.product', 'recoveryMan'])
            ->latest('sale_date')
            ->get();

        $recoveryMen = Employee::recoveryMen()->orderBy('name')->get();

        return view('livewire.customers.customer-detail', [
            'accounts' => $accounts,
            'recoveryMen' => $recoveryMen,
        ]);
    }
}
