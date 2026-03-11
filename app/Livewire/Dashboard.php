<?php

namespace App\Livewire;

use App\Services\DashboardService;
use Livewire\Component;

class Dashboard extends Component
{
    public string $period = 'day';
    public string $chartPeriod = 'week';

    public array $stats       = [];
    public array $chartData   = [];

    public function mount(DashboardService $service): void
    {
        $this->loadStats($service);
    }

    public function updatedPeriod(): void
    {
        $this->loadStats(app(DashboardService::class));
    }

    public function updatedChartPeriod(): void
    {
        $this->chartData = app(DashboardService::class)
            ->getSalesChartData($this->chartPeriod);
    }

    private function loadStats(DashboardService $service): void
    {
        if (auth()->user()->isAdmin()) {
            $this->stats     = $service->getAdminStats($this->period);
            $this->chartData = $service->getSalesChartData($this->chartPeriod);
        } else {
            $this->stats = $service->getVendeurStats(auth()->id());
        }
    }

    public function render(DashboardService $service)
    {
        return view('livewire.dashboard', [
            'recentSales'       => $service->getRecentSales(),
            'lowStockProducts'  => $service->getLowStockProducts(),
            'resellersWithDebt' => $service->getResellersWithDebt(),
        ])->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
