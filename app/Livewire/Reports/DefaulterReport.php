<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Setting;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class DefaulterReport extends Component
{
    public ?int $recovery_man_id = null;

    public int $days_overdue = 30;

    public bool $generated = false;

    public function mount(): void
    {
        $this->days_overdue = (int) Setting::get('defaulter_days', 30);
    }

    public function generate(): void
    {
        $this->validate(['days_overdue' => 'required|integer|min:1']);
        $this->generated = true;
    }

    /** @return array{label: string, min: int, max: int|null, rowClass: string, badgeClass: string} */
    private function bands(): array
    {
        return [
            ['label' => 'Critical (> 90 days)', 'min' => 91, 'max' => null, 'rowClass' => 'bg-red-50', 'badgeClass' => 'bg-red-100 text-red-800'],
            ['label' => 'Severe (61 – 90 days)', 'min' => 61, 'max' => 90, 'rowClass' => 'bg-orange-50', 'badgeClass' => 'bg-orange-100 text-orange-700'],
            ['label' => 'Moderate (30 – 60 days)', 'min' => 30, 'max' => 60, 'rowClass' => 'bg-yellow-50', 'badgeClass' => 'bg-yellow-100 text-yellow-700'],
            ['label' => 'Early (< 30 days)', 'min' => 0, 'max' => 29, 'rowClass' => 'bg-white', 'badgeClass' => 'bg-gray-100 text-gray-600'],
        ];
    }

    private function bandForDays(int $days): array
    {
        foreach ($this->bands() as $band) {
            if ($band['max'] === null && $days >= $band['min']) {
                return $band;
            }
            if ($band['max'] !== null && $days >= $band['min'] && $days <= $band['max']) {
                return $band;
            }
        }

        return $this->bands()[3];
    }

    public function render()
    {
        $defaulters = collect();
        $bandSummary = [];

        if ($this->generated) {
            $cutoff = Carbon::now()->subDays($this->days_overdue);

            // Pre-load latest payment dates per account to avoid N+1
            $accountIds = Account::where('status', 'active')
                ->where('remaining_amount', '>', 0)
                ->where('sale_date', '<=', $cutoff)
                ->when($this->recovery_man_id, fn ($q) => $q->where('recovery_man_id', $this->recovery_man_id))
                ->pluck('id');

            $latestPayments = Payment::selectRaw('account_id, MAX(payment_date) as last_payment_date')
                ->whereIn('account_id', $accountIds)
                ->groupBy('account_id')
                ->pluck('last_payment_date', 'account_id');

            $defaulters = Account::with(['customer', 'saleMan', 'recoveryMan', 'items.product'])
                ->whereIn('id', $accountIds)
                ->orderBy('sale_date')
                ->get()
                ->map(function ($account) use ($latestPayments) {
                    $daysSinceSale = (int) Carbon::parse($account->sale_date)->diffInDays(now());
                    $lastPaymentDate = $latestPayments->get($account->id);
                    $daysSincePayment = $lastPaymentDate
                        ? (int) Carbon::parse($lastPaymentDate)->diffInDays(now())
                        : $daysSinceSale;

                    $band = $this->bandForDays($daysSinceSale);

                    return [
                        'id' => $account->id,
                        'sale_date' => $account->sale_date,
                        'days_since_sale' => $daysSinceSale,
                        'last_payment_date' => $lastPaymentDate,
                        'days_since_payment' => $daysSincePayment,
                        'customer' => $account->customer->name,
                        'address' => $account->customer->home_address ?? '—',
                        'phone' => $account->customer->mobile ?? '—',
                        'sale_man' => $account->saleMan?->name ?? '—',
                        'rm' => $account->recoveryMan?->name ?? '—',
                        'items' => $account->items->pluck('product.name')->filter()->join(', '),
                        'total' => $account->total_amount,
                        'remaining' => $account->remaining_amount,
                        'paid' => $account->total_amount - $account->remaining_amount,
                        'installment_amount' => $account->installment_amount ?? 0,
                        'band' => $band,
                    ];
                });

            // Build band summary
            foreach ($this->bands() as $band) {
                $bandRows = $defaulters->filter(fn ($d) => $d['band']['label'] === $band['label']);
                $bandSummary[] = [
                    'label' => $band['label'],
                    'badgeClass' => $band['badgeClass'],
                    'count' => $bandRows->count(),
                    'outstanding' => $bandRows->sum('remaining'),
                ];
            }
        }

        $rmOpts = Employee::recoveryMen()->orderBy('name')->get()
            ->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);

        return view('livewire.reports.defaulter-report', [
            'defaulters' => $defaulters,
            'rmOpts' => $rmOpts,
            'bandSummary' => $bandSummary,
        ]);
    }
}
