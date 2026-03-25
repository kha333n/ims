<?php

namespace App\Livewire\Sales;

use App\Models\Account;
use App\Models\AccountItem;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FinancialLedger;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class NewSale extends Component
{
    // Customer panel
    public ?int $customer_id = null;

    public ?string $customer_name = null;

    public ?string $customer_father = null;

    public ?string $customer_mobile = null;

    public ?string $customer_address = null;

    public ?string $customer_reference = null;

    // Sale info
    public string $sale_date = '';

    public string $slip_number = '';

    // Items
    /** @var array<int, array{product_id: int, name: string, price: string, quantity: int, stock: int}> */
    public array $items = [];

    public ?int $selected_product_id = null;

    public string $item_price = '';

    public int $item_quantity = 1;

    // Financials
    public string $advance = '0';

    public string $discount = '0';

    // Installment
    public string $installment_type = 'monthly';

    public ?int $installment_day = null;

    public string $installment_amount = '';

    // Staff
    public ?int $sale_man_id = null;

    public ?int $recovery_man_id = null;

    // Force re-render of searchable-select after adding customer
    public int $customerSelectKey = 0;

    // Quick add customer modal
    public bool $showNewCustomerModal = false;

    public string $new_customer_name = '';

    public string $new_customer_father = '';

    public string $new_customer_mobile = '';

    public string $new_customer_cnic = '';

    public string $new_customer_home_address = '';

    public string $new_customer_shop_address = '';

    public string $new_customer_reference = '';

    // Summary after save
    public ?array $summary = null;

    public function mount(): void
    {
        $this->sale_date = now()->format('Y-m-d');
    }

    public function updatedCustomerId(): void
    {
        if ($this->customer_id) {
            $customer = Customer::find($this->customer_id);
            if ($customer) {
                $this->customer_name = $customer->name;
                $this->customer_father = $customer->father_name;
                $this->customer_mobile = $customer->mobile;
                $this->customer_address = $customer->home_address;
                $this->customer_reference = $customer->reference;

                return;
            }
        }

        $this->resetCustomerInfo();
    }

    public function updatedSelectedProductId(): void
    {
        if ($this->selected_product_id) {
            $product = Product::find($this->selected_product_id);
            if ($product) {
                $this->item_price = (string) ($product->sale_price / 100);
                $this->item_quantity = 1;

                return;
            }
        }

        $this->item_price = '';
    }

    public function addItem(): void
    {
        $this->validate([
            'selected_product_id' => 'required|exists:products,id',
            'item_price' => 'required|numeric|min:0',
            'item_quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($this->selected_product_id);

        if ($this->item_quantity > $product->quantity) {
            $this->addError('item_quantity', "Only {$product->quantity} in stock.");

            return;
        }

        $this->items[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $this->item_price,
            'quantity' => $this->item_quantity,
            'stock' => $product->quantity,
        ];

        $this->reset(['selected_product_id', 'item_price', 'item_quantity']);
        $this->item_quantity = 1;
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getTotalAmountProperty(): int
    {
        return (int) collect($this->items)->sum(fn ($item) => parseMoney($item['price']) * $item['quantity']);
    }

    public function getRemainingAmountProperty(): int
    {
        return max(0, $this->totalAmount - parseMoney($this->advance) - parseMoney($this->discount));
    }

    public function getTotalInstallmentsProperty(): ?int
    {
        $instAmount = parseMoney($this->installment_amount);
        if ($instAmount <= 0 || $this->remainingAmount <= 0) {
            return null;
        }

        return (int) ceil($this->remainingAmount / $instAmount);
    }

    public function getPeriodLabelProperty(): string
    {
        return match ($this->installment_type) {
            'daily' => 'days',
            'weekly' => 'weeks',
            'monthly' => 'months',
            default => 'periods',
        };
    }

    public function openNewCustomerModal(): void
    {
        $this->reset([
            'new_customer_name', 'new_customer_father', 'new_customer_mobile',
            'new_customer_cnic', 'new_customer_home_address', 'new_customer_shop_address',
            'new_customer_reference',
        ]);
        $this->showNewCustomerModal = true;
    }

    public function closeNewCustomerModal(): void
    {
        $this->showNewCustomerModal = false;
    }

    public function saveNewCustomer(): void
    {
        $this->validate([
            'new_customer_name' => 'required|string|max:255',
            'new_customer_father' => 'nullable|string|max:255',
            'new_customer_mobile' => 'nullable|string|max:20',
            'new_customer_cnic' => 'nullable|string|max:20',
            'new_customer_home_address' => 'nullable|string|max:500',
            'new_customer_shop_address' => 'nullable|string|max:500',
            'new_customer_reference' => 'nullable|string|max:255',
        ]);

        $customer = Customer::create([
            'name' => $this->new_customer_name,
            'father_name' => $this->new_customer_father ?: null,
            'mobile' => $this->new_customer_mobile ?: null,
            'cnic' => $this->new_customer_cnic ?: null,
            'home_address' => $this->new_customer_home_address ?: null,
            'shop_address' => $this->new_customer_shop_address ?: null,
            'reference' => $this->new_customer_reference ?: null,
        ]);

        // Auto-select the new customer
        $this->customer_id = $customer->id;
        $this->customer_name = $customer->name;
        $this->customer_father = $customer->father_name;
        $this->customer_mobile = $customer->mobile;
        $this->customer_address = $customer->home_address;
        $this->customer_reference = $customer->reference;

        $this->showNewCustomerModal = false;
        $this->customerSelectKey++;
    }

    public function dismissSummary(): void
    {
        $this->summary = null;
    }

    public function proceed(): void
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
            'slip_number' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'advance' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'installment_type' => 'required|in:daily,weekly,monthly',
            'installment_amount' => 'required|numeric|min:1',
            'sale_man_id' => 'required|exists:employees,id',
            'recovery_man_id' => 'required|exists:employees,id',
        ];

        if ($this->installment_type === 'weekly') {
            $rules['installment_day'] = 'required|integer|min:1|max:7';
        } elseif ($this->installment_type === 'monthly') {
            $rules['installment_day'] = 'required|integer|min:1|max:31';
        }

        $this->validate($rules);

        $totalAmount = $this->totalAmount;
        $advanceAmount = parseMoney($this->advance);
        $discountAmount = parseMoney($this->discount);
        $remainingAmount = $this->remainingAmount;
        $installmentAmount = parseMoney($this->installment_amount);
        $dayValue = $this->installment_type === 'daily' ? null : $this->installment_day;

        $customerName = $this->customer_name;
        $itemNames = collect($this->items)->pluck('name')->join(', ');
        $smName = Employee::find($this->sale_man_id)?->name;
        $rmName = Employee::find($this->recovery_man_id)?->name;
        $accountId = null;

        DB::transaction(function () use ($totalAmount, $advanceAmount, $discountAmount, $remainingAmount, $installmentAmount, $dayValue, &$accountId, $itemNames) {
            $account = Account::create([
                'customer_id' => $this->customer_id,
                'sale_man_id' => $this->sale_man_id,
                'recovery_man_id' => $this->recovery_man_id,
                'slip_number' => $this->slip_number ?: null,
                'sale_date' => $this->sale_date,
                'total_amount' => $totalAmount,
                'advance_amount' => $advanceAmount,
                'discount_amount' => $discountAmount,
                'remaining_amount' => $remainingAmount,
                'installment_type' => $this->installment_type,
                'installment_day' => $dayValue,
                'installment_amount' => $installmentAmount,
                'status' => 'active',
            ]);

            $accountId = $account->id;

            foreach ($this->items as $item) {
                AccountItem::create([
                    'account_id' => $account->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => parseMoney($item['price']),
                ]);

                Product::where('id', $item['product_id'])
                    ->decrement('quantity', $item['quantity']);
            }

            if ($advanceAmount > 0) {
                Payment::create([
                    'account_id' => $account->id,
                    'amount' => $advanceAmount,
                    'transaction_type' => 'advance',
                    'payment_date' => $this->sale_date,
                    'collected_by' => $this->sale_man_id,
                    'remarks' => 'Advance at sale',
                ]);
            }

            FinancialLedger::record('sale', [
                'account_id' => $account->id,
                'customer_id' => $this->customer_id,
                'debit' => $totalAmount,
                'balance_after' => $remainingAmount,
                'description' => "New sale Acc#{$account->id} — {$itemNames}",
            ]);

            if ($advanceAmount > 0) {
                FinancialLedger::record('payment', [
                    'account_id' => $account->id,
                    'customer_id' => $this->customer_id,
                    'employee_id' => $this->sale_man_id,
                    'debit' => $advanceAmount,
                    'balance_after' => $remainingAmount,
                    'description' => "Advance payment at sale Acc#{$account->id}",
                ]);
            }
        });

        $this->summary = [
            'account_id' => $accountId,
            'customer' => $customerName,
            'items' => $itemNames,
            'total' => $totalAmount,
            'advance' => $advanceAmount,
            'discount' => $discountAmount,
            'remaining' => $remainingAmount,
            'installment_type' => ucfirst($this->installment_type),
            'installment_amount' => $installmentAmount,
            'sale_man' => $smName,
            'recovery_man' => $rmName,
        ];

        $this->resetForm();
    }

    private function resetCustomerInfo(): void
    {
        $this->customer_name = null;
        $this->customer_father = null;
        $this->customer_mobile = null;
        $this->customer_address = null;
        $this->customer_reference = null;
    }

    private function resetForm(): void
    {
        $this->reset([
            'customer_id', 'slip_number', 'items', 'selected_product_id',
            'item_price', 'item_quantity', 'advance', 'discount',
            'installment_type', 'installment_day', 'installment_amount',
            'sale_man_id', 'recovery_man_id',
        ]);
        $this->resetCustomerInfo();
        $this->sale_date = now()->format('Y-m-d');
        $this->advance = '0';
        $this->discount = '0';
        $this->item_quantity = 1;
        $this->installment_type = 'monthly';
        $this->resetValidation();
    }

    public function render()
    {
        $customerOpts = Customer::orderBy('name')->get()->map(fn ($c) => ['id' => $c->id, 'label' => $c->name]);
        $productOpts = Product::where('quantity', '>', 0)->orderBy('name')->get()->map(fn ($p) => ['id' => $p->id, 'label' => "{$p->name} ({$p->quantity} in stock)"]);
        $smOpts = Employee::saleMen()->orderBy('name')->get()->map(fn ($e) => ['id' => $e->id, 'label' => $e->name]);
        $rmOpts = Employee::recoveryMen()->orderBy('name')->get()->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);

        return view('livewire.sales.new-sale', [
            'customerOpts' => $customerOpts,
            'productOpts' => $productOpts,
            'smOpts' => $smOpts,
            'rmOpts' => $rmOpts,
        ]);
    }
}
