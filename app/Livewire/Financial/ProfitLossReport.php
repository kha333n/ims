<?php

namespace App\Livewire\Financial;

use App\Models\Account;
use App\Models\AccountItem;
use App\Models\ProductReturn;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class ProfitLossReport extends Component
{
    public string $date_from = '';

    public string $date_to = '';

    public bool $generated = false;

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
    }

    public function generate(): void
    {
        $this->validate(['date_from' => 'required|date', 'date_to' => 'required|date|after_or_equal:date_from']);
        $this->generated = true;
    }

    public function render()
    {
        $data = null;
        if ($this->generated) {
            $accountIds = Account::whereBetween('sale_date', [$this->date_from, $this->date_to])->pluck('id');

            $revenue = Account::whereBetween('sale_date', [$this->date_from, $this->date_to])->sum('total_amount');

            $cogs = AccountItem::whereIn('account_id', $accountIds)
                ->join('products', 'account_items.product_id', '=', 'products.id')
                ->selectRaw('COALESCE(SUM(account_items.quantity * products.purchase_price), 0) as total')
                ->value('total');

            $discounts = Account::whereBetween('sale_date', [$this->date_from, $this->date_to])->sum('discount_amount');

            $losses = Account::where('status', 'closed')
                ->whereBetween('closed_at', [$this->date_from, $this->date_to])
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount');

            $returns = ProductReturn::whereBetween('return_date', [$this->date_from, $this->date_to])->sum('returning_amount');

            $gross = $revenue - $cogs;
            $net = $gross - $discounts - $losses - $returns;

            $data = compact('revenue', 'cogs', 'gross', 'discounts', 'losses', 'returns', 'net');
        }

        return view('livewire.financial.profit-loss-report', ['data' => $data]);
    }
}
