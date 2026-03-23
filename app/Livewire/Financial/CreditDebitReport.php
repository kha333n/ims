<?php

namespace App\Livewire\Financial;

use App\Models\FinancialLedger;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.report')]
class CreditDebitReport extends Component
{
    public string $date_from = '';

    public string $date_to = '';

    public string $group_by = 'day';

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
        $rows = collect();
        if ($this->generated) {
            $format = match ($this->group_by) {
                'week' => "strftime('%Y-W%W', event_date)",
                'month' => "strftime('%Y-%m', event_date)",
                default => 'date(event_date)',
            };

            $rows = FinancialLedger::whereBetween('event_date', [$this->date_from, $this->date_to.' 23:59:59'])
                ->select(DB::raw("{$format} as period"), DB::raw('SUM(debit) as total_in'), DB::raw('SUM(credit) as total_out'))
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->map(function ($r) {
                    $r->net = ($r->total_in ?? 0) - ($r->total_out ?? 0);

                    return $r;
                });

            // Add cumulative
            $cumulative = 0;
            $rows = $rows->map(function ($r) use (&$cumulative) {
                $cumulative += $r->net;
                $r->cumulative = $cumulative;

                return $r;
            });
        }

        return view('livewire.financial.credit-debit-report', ['rows' => $rows]);
    }
}
