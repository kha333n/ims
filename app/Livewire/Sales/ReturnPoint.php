<?php

namespace App\Livewire\Sales;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FinancialLedger;
use App\Models\Product;
use App\Models\ProductReturn;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ReturnPoint extends Component
{
    public ?int $recovery_man_id = null;

    public ?int $customer_id = null;

    public ?int $account_id = null;

    public ?int $account_item_id = null;

    // Account info display
    public ?array $accountInfo = null;

    // Summary
    public ?array $returnSummary = null;

    public bool $showReturnConfirm = false;

    // Return fields
    public string $returning_amount = '';

    public string $return_date = '';

    public string $reason = '';

    public string $inventory_action = 'restock';

    public function mount(): void
    {
        $this->return_date = now()->format('Y-m-d');
    }

    public function updatedRecoveryManId(): void
    {
        $this->reset(['customer_id', 'account_id', 'account_item_id', 'accountInfo', 'returning_amount', 'reason']);
    }

    public function updatedCustomerId(): void
    {
        $this->reset(['account_id', 'account_item_id', 'accountInfo', 'returning_amount', 'reason']);
    }

    public function updatedAccountId(): void
    {
        $this->reset(['account_item_id', 'returning_amount', 'reason']);

        if ($this->account_id) {
            $account = Account::with(['customer', 'saleMan', 'recoveryMan', 'items.product'])->find($this->account_id);
            if ($account) {
                $this->accountInfo = [
                    'customer_name' => $account->customer->name,
                    'phone' => $account->customer->mobile ?? '—',
                    'slip' => $account->slip_number ?? '—',
                    'sale_man' => $account->saleMan?->name ?? '—',
                    'sale_date' => $account->sale_date ? formatDate($account->sale_date) : '—',
                    'total' => $account->total_amount,
                    'remaining' => $account->remaining_amount,
                    'items' => $account->items->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->product?->name ?? 'Unknown',
                        'price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'returned' => $item->returned,
                    ])->toArray(),
                ];

                return;
            }
        }

        $this->accountInfo = null;
    }

    public function confirmReturn(): void
    {
        $this->validate([
            'account_id' => 'required|exists:accounts,id',
            'account_item_id' => 'required|exists:account_items,id',
            'returning_amount' => 'required|numeric|min:1',
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
            'inventory_action' => 'required|in:restock,scrap',
        ]);

        $this->showReturnConfirm = true;
    }

    public function cancelReturn(): void
    {
        $this->showReturnConfirm = false;
    }

    public function processReturn(): void
    {
        $this->showReturnConfirm = false;

        $returningAmount = parseMoney($this->returning_amount);

        DB::transaction(function () use ($returningAmount) {
            $account = Account::findOrFail($this->account_id);
            $accountItem = $account->items()->findOrFail($this->account_item_id);

            ProductReturn::create([
                'account_id' => $account->id,
                'account_item_id' => $accountItem->id,
                'quantity' => $accountItem->quantity,
                'returning_amount' => $returningAmount,
                'return_date' => $this->return_date,
                'reason' => $this->reason ?: null,
                'inventory_action' => $this->inventory_action,
            ]);

            $account->decrement('remaining_amount', min($returningAmount, $account->remaining_amount));

            $accountItem->update(['returned' => true]);

            if ($this->inventory_action === 'restock') {
                Product::where('id', $accountItem->product_id)
                    ->increment('quantity', $accountItem->quantity);
            }

            FinancialLedger::record('return', [
                'account_id' => $account->id,
                'customer_id' => $account->customer_id,
                'product_id' => $accountItem->product_id,
                'credit' => $returningAmount,
                'balance_after' => $account->fresh()->remaining_amount,
                'description' => "Return on Acc#{$account->id} — {$this->inventory_action}",
            ]);
        });

        $itemName = collect($this->accountInfo['items'] ?? [])->firstWhere('id', $this->account_item_id)['name'] ?? 'Unknown';
        $custName = $this->accountInfo['customer_name'] ?? '';

        $this->returnSummary = [
            'customer' => $custName,
            'account_id' => $this->account_id,
            'item' => $itemName,
            'amount' => $returningAmount,
            'action' => ucfirst($this->inventory_action),
            'reason' => $this->reason ?: '—',
        ];

        $this->reset(['account_id', 'account_item_id', 'accountInfo', 'returning_amount', 'reason']);
        $this->inventory_action = 'restock';
        $this->return_date = now()->format('Y-m-d');
    }

    public function render()
    {
        $recoveryMen = Employee::recoveryMen()->orderBy('name')->get();

        $customers = collect();
        if ($this->recovery_man_id) {
            $customerIds = Account::where('recovery_man_id', $this->recovery_man_id)
                ->active()
                ->pluck('customer_id')
                ->unique();
            $customers = Customer::whereIn('id', $customerIds)->orderBy('name')->get();
        }

        $accounts = collect();
        if ($this->customer_id) {
            $accounts = Account::where('customer_id', $this->customer_id)
                ->when($this->recovery_man_id, fn ($q) => $q->where('recovery_man_id', $this->recovery_man_id))
                ->active()
                ->get();
        }

        $rmOpts = $recoveryMen->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);
        $custOpts = $customers->map(fn ($c) => ['id' => $c->id, 'label' => $c->name]);
        $accOpts = $accounts->map(fn ($a) => ['id' => $a->id, 'label' => "Acc# {$a->id}"]);

        return view('livewire.sales.return-point', [
            'rmOpts' => $rmOpts,
            'custOpts' => $custOpts,
            'accOpts' => $accOpts,
        ]);
    }
}
