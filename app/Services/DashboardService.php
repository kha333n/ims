<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountItem;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Setting;
use Carbon\Carbon;

class DashboardService
{
    public function getTopStats(): array
    {
        return [
            'active_accounts' => Account::active()->count(),
            'total_receivables' => Account::active()->sum('remaining_amount'),
            'today_collections' => Payment::whereDate('payment_date', today())->sum('amount'),
            'total_stock' => Product::sum('quantity'),
        ];
    }

    public function getMonthlyComparison(): array
    {
        $thisMonth = [now()->startOfMonth(), now()];
        $lastMonth = [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()];

        return [
            'this_month' => [
                'sales' => Account::whereBetween('sale_date', $thisMonth)->sum('total_amount'),
                'collections' => Payment::whereBetween('payment_date', $thisMonth)->sum('amount'),
                'purchases' => Purchase::whereBetween('purchase_date', $thisMonth)->selectRaw('COALESCE(SUM(unit_cost * quantity), 0) as total')->value('total'),
                'sales_count' => Account::whereBetween('sale_date', $thisMonth)->count(),
            ],
            'last_month' => [
                'sales' => Account::whereBetween('sale_date', $lastMonth)->sum('total_amount'),
                'collections' => Payment::whereBetween('payment_date', $lastMonth)->sum('amount'),
                'purchases' => Purchase::whereBetween('purchase_date', $lastMonth)->selectRaw('COALESCE(SUM(unit_cost * quantity), 0) as total')->value('total'),
                'sales_count' => Account::whereBetween('sale_date', $lastMonth)->count(),
            ],
        ];
    }

    public function getRecentPayments(int $limit = 10): array
    {
        return Payment::with('account.customer')
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'date' => $p->payment_date,
                'customer' => $p->account?->customer?->name ?? '—',
                'amount' => $p->amount,
                'account_id' => $p->account_id,
                'type' => $p->transaction_type,
            ])
            ->toArray();
    }

    public function getRecentSales(int $limit = 5): array
    {
        return Account::with(['customer', 'items.product'])
            ->orderBy('sale_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'date' => $a->sale_date,
                'customer' => $a->customer->name,
                'items' => $a->items->pluck('product.name')->filter()->join(', '),
                'total' => $a->total_amount,
            ])
            ->toArray();
    }

    public function getDefaulterSummary(): array
    {
        $days = (int) Setting::get('defaulter_days', 30);
        $cutoff = Carbon::now()->subDays($days);

        $defaulters = Account::with('customer')
            ->active()
            ->where('remaining_amount', '>', 0)
            ->where('sale_date', '<=', $cutoff)
            ->orderByDesc('remaining_amount')
            ->get();

        return [
            'count' => $defaulters->count(),
            'total' => $defaulters->sum('remaining_amount'),
            'days_threshold' => $days,
            'top' => $defaulters->take(5)->map(fn ($a) => [
                'id' => $a->id,
                'customer' => $a->customer->name,
                'remaining' => $a->remaining_amount,
                'days' => Carbon::parse($a->sale_date)->diffInDays(now()),
            ])->toArray(),
        ];
    }

    public function getRecoveryPerformance(): array
    {
        $thisMonth = [now()->startOfMonth(), now()];

        return Employee::recoveryMen()->orderBy('name')->get()->map(function ($rm) use ($thisMonth) {
            $accounts = Account::where('recovery_man_id', $rm->id)->active()->count();
            $collected = Payment::whereHas('account', fn ($q) => $q->where('recovery_man_id', $rm->id))
                ->whereBetween('payment_date', $thisMonth)
                ->sum('amount');
            $pending = Account::where('recovery_man_id', $rm->id)->active()->sum('remaining_amount');

            return [
                'name' => $rm->name,
                'area' => $rm->area ?? '—',
                'accounts' => $accounts,
                'collected' => $collected,
                'pending' => $pending,
            ];
        })->toArray();
    }

    public function getLowStockProducts(int $threshold = 5): array
    {
        return Product::where('quantity', '<=', $threshold)
            ->orderBy('quantity')
            ->get()
            ->map(fn ($p) => ['name' => $p->name, 'quantity' => $p->quantity])
            ->toArray();
    }

    public function getProfitOverview(): array
    {
        $thisMonth = [now()->startOfMonth(), now()];
        $accountIds = Account::whereBetween('sale_date', $thisMonth)->pluck('id');

        $revenue = Account::whereBetween('sale_date', $thisMonth)->sum('total_amount');
        $cogs = AccountItem::whereIn('account_id', $accountIds)
            ->join('products', 'account_items.product_id', '=', 'products.id')
            ->selectRaw('COALESCE(SUM(account_items.quantity * products.purchase_price), 0) as total')
            ->value('total');
        $discounts = Account::whereBetween('sale_date', $thisMonth)->sum('discount_amount');
        $losses = Account::where('status', 'closed')
            ->whereBetween('closed_at', $thisMonth)
            ->where('remaining_amount', '>', 0)
            ->sum('remaining_amount');

        return [
            'revenue' => $revenue,
            'cogs' => (int) $cogs,
            'gross' => $revenue - (int) $cogs,
            'discounts' => $discounts,
            'losses' => $losses,
            'net' => $revenue - (int) $cogs - $discounts - $losses,
        ];
    }
}
