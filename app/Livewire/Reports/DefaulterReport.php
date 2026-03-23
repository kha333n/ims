<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Models\Employee;
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

    public function render()
    {
        $defaulters = collect();
        if ($this->generated) {
            $cutoff = Carbon::now()->subDays($this->days_overdue);

            $defaulters = Account::with(['customer', 'saleMan', 'recoveryMan', 'items.product'])
                ->where('status', 'active')
                ->where('remaining_amount', '>', 0)
                ->where('sale_date', '<=', $cutoff)
                ->when($this->recovery_man_id, fn ($q) => $q->where('recovery_man_id', $this->recovery_man_id))
                ->orderBy('sale_date')
                ->get()
                ->map(function ($account) {
                    $daysSinceSale = Carbon::parse($account->sale_date)->diffInDays(now());

                    return [
                        'id' => $account->id,
                        'sale_date' => $account->sale_date,
                        'days' => $daysSinceSale,
                        'customer' => $account->customer->name,
                        'address' => $account->customer->home_address ?? '—',
                        'phone' => $account->customer->mobile ?? '—',
                        'sale_man' => $account->saleMan?->name ?? '—',
                        'rm' => $account->recoveryMan?->name ?? '—',
                        'items' => $account->items->pluck('product.name')->filter()->join(', '),
                        'total' => $account->total_amount,
                        'remaining' => $account->remaining_amount,
                        'paid' => $account->total_amount - $account->remaining_amount,
                    ];
                });
        }

        $rmOpts = Employee::recoveryMen()->orderBy('name')->get()
            ->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);

        return view('livewire.reports.defaulter-report', ['defaulters' => $defaulters, 'rmOpts' => $rmOpts]);
    }
}
