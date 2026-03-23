<?php

namespace App\Livewire\Financial;

use App\Models\Account;
use App\Models\Employee;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class ReceivablesReport extends Component
{
    public ?int $recovery_man_id = null;

    public function render()
    {
        $accounts = Account::with(['customer', 'recoveryMan'])
            ->where('status', 'active')
            ->where('remaining_amount', '>', 0)
            ->when($this->recovery_man_id, fn ($q) => $q->where('recovery_man_id', $this->recovery_man_id))
            ->orderBy('sale_date')
            ->get()
            ->map(function ($a) {
                $days = Carbon::parse($a->sale_date)->diffInDays(now());
                $bucket = $days <= 30 ? '0-30' : ($days <= 60 ? '31-60' : ($days <= 90 ? '61-90' : '90+'));

                return [
                    'id' => $a->id, 'customer' => $a->customer->name, 'phone' => $a->customer->mobile ?? '—',
                    'rm' => $a->recoveryMan?->name ?? '—', 'sale_date' => $a->sale_date, 'days' => $days,
                    'total' => $a->total_amount, 'paid' => $a->total_amount - $a->remaining_amount,
                    'remaining' => $a->remaining_amount, 'bucket' => $bucket,
                ];
            });

        $buckets = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];
        $bucketCounts = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];
        foreach ($accounts as $a) {
            $buckets[$a['bucket']] += $a['remaining'];
            $bucketCounts[$a['bucket']]++;
        }

        $rmOpts = Employee::recoveryMen()->orderBy('name')->get()->map(fn ($e) => ['id' => $e->id, 'label' => $e->name.($e->area ? " ({$e->area})" : '')]);

        return view('livewire.financial.receivables-report', compact('accounts', 'buckets', 'bucketCounts', 'rmOpts'));
    }
}
