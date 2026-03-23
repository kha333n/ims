<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Overview extends Component
{
    public function render(DashboardService $service)
    {
        return view('livewire.dashboard.overview', [
            'stats' => $service->getTopStats(),
            'monthly' => $service->getMonthlyComparison(),
            'recentPayments' => $service->getRecentPayments(),
            'recentSales' => $service->getRecentSales(),
            'defaulters' => $service->getDefaulterSummary(),
            'recovery' => $service->getRecoveryPerformance(),
            'lowStock' => $service->getLowStockProducts(),
            'profit' => $service->getProfitOverview(),
        ]);
    }
}
