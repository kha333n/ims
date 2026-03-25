<?php

namespace App\Livewire\Sales;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FinancialLedger;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\Purchase;
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

    public ?array $accountInfo = null;

    public ?array $returnSummary = null;

    public bool $showReturnConfirm = false;

    public bool $showClosePrompt = false;

    public ?int $pendingCloseAccountId = null;

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
                $totalPaid = $account->total_amount - $account->remaining_amount;
                $this->accountInfo = [
                    'customer_name' => $account->customer->name,
                    'phone' => $account->customer->mobile ?? '—',
                    'slip' => $account->slip_number ?? '—',
                    'sale_man' => $account->saleMan?->name ?? '—',
                    'recovery_man' => $account->recoveryMan?->name ?? '—',
                    'area' => $account->recoveryMan?->area ?? '—',
                    'sale_date' => $account->sale_date ? formatDate($account->sale_date) : '—',
                    'total' => $account->total_amount,
                    'advance' => $account->advance_amount,
                    'paid' => $totalPaid,
                    'remaining' => $account->remaining_amount,
                    'items' => $account->items->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->product?->name ?? 'Unknown',
                        'price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'subtotal' => $item->unit_price * $item->quantity,
                        'returned' => $item->returned,
                    ])->toArray(),
                ];

                return;
            }
        }

        $this->accountInfo = null;
    }

    public function updatedAccountItemId(): void
    {
        if ($this->account_item_id && $this->accountInfo) {
            $item = collect($this->accountInfo['items'])->firstWhere('id', $this->account_item_id);
            if ($item) {
                $this->returning_amount = (string) ($item['subtotal'] / 100);
            }
        }
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

                // Add back to newest purchase batch
                $latestBatch = Purchase::where('product_id', $accountItem->product_id)
                    ->orderByDesc('id')
                    ->first();

                if ($latestBatch) {
                    $latestBatch->increment('remaining_qty', $accountItem->quantity);
                }
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

        // Check if all items returned — auto-close or prompt
        $account = Account::with('items')->find($this->account_id);
        if ($account && $account->items->every(fn ($i) => $i->returned)) {
            if ($account->remaining_amount <= 0) {
                $account->update(['status' => 'closed', 'closed_at' => now()]);
                FinancialLedger::record('closure', [
                    'account_id' => $account->id,
                    'customer_id' => $account->customer_id,
                    'balance_after' => 0,
                    'description' => "Auto-closed Acc#{$account->id} — all items returned",
                ]);
                $this->returnSummary['auto_closed'] = true;
            } else {
                $this->pendingCloseAccountId = $account->id;
                $this->showClosePrompt = true;
            }
        }

        $this->reset(['account_id', 'account_item_id', 'accountInfo', 'returning_amount', 'reason']);
        $this->inventory_action = 'restock';
        $this->return_date = now()->format('Y-m-d');
    }

    public function closeAccountAfterReturn(): void
    {
        if ($this->pendingCloseAccountId) {
            $account = Account::findOrFail($this->pendingCloseAccountId);
            $writeOff = $account->remaining_amount;

            $account->update(['status' => 'closed', 'closed_at' => now(), 'remaining_amount' => 0]);

            if ($writeOff > 0) {
                FinancialLedger::record('loss', [
                    'account_id' => $account->id,
                    'customer_id' => $account->customer_id,
                    'credit' => $writeOff,
                    'balance_after' => 0,
                    'description' => "Write-off Acc#{$account->id} — closed after all items returned",
                ]);
            }

            FinancialLedger::record('closure', [
                'account_id' => $account->id,
                'customer_id' => $account->customer_id,
                'balance_after' => 0,
                'description' => "Acc#{$account->id} closed after return — balance written off",
            ]);
        }

        $this->showClosePrompt = false;
        $this->pendingCloseAccountId = null;
    }

    public function keepAccountOpen(): void
    {
        $this->showClosePrompt = false;
        $this->pendingCloseAccountId = null;
    }

    public function render()
    {
        // Customer list: show all customers with active accounts, optionally filtered by RM
        $customerQuery = Customer::whereHas('accounts', function ($q) {
            $q->active();
            if ($this->recovery_man_id) {
                $q->where('recovery_man_id', $this->recovery_man_id);
            }
        })->orderBy('name');

        $customers = $customerQuery->get();

        $accounts = collect();
        if ($this->customer_id) {
            $accounts = Account::where('customer_id', $this->customer_id)
                ->when($this->recovery_man_id, fn ($q) => $q->where('recovery_man_id', $this->recovery_man_id))
                ->active()
                ->get();
        }

        $rmOpts = Employee::recoveryMen()->orderBy('name')->get()
            ->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);
        $custOpts = $customers->map(fn ($c) => ['id' => $c->id, 'label' => $c->name]);
        $accOpts = $accounts->map(fn ($a) => ['id' => $a->id, 'label' => "Acc# {$a->id}"]);

        return view('livewire.sales.return-point', [
            'rmOpts' => $rmOpts,
            'custOpts' => $custOpts,
            'accOpts' => $accOpts,
        ]);
    }
}
