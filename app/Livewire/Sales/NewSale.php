<?php

namespace App\Livewire\Sales;

use App\Models\Account;
use App\Models\AccountItem;
use App\Models\Customer;
use App\Models\Employee;
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
                $this->item_price = (string) ($product->price / 100);
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

    public function proceed(): void
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
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

        DB::transaction(function () use ($totalAmount, $advanceAmount, $discountAmount, $remainingAmount, $installmentAmount, $dayValue) {
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
        });

        $this->resetForm();
        session()->flash('success', 'Sale completed successfully.');
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
        return view('livewire.sales.new-sale', [
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::where('quantity', '>', 0)->orderBy('name')->get(),
            'saleMen' => Employee::saleMen()->orderBy('name')->get(),
            'recoveryMen' => Employee::recoveryMen()->orderBy('name')->get(),
        ]);
    }
}
