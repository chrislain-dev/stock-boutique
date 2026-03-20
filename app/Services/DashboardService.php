<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductModel;
use App\Models\Reseller;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    // ─── Stats admin ──────────────────────────────────────────

    public function getAdminStats(string $period = 'day'): array
    {
        $query = Sale::query()->where('sale_status', 'completed');

        $query = match ($period) {
            'day'     => $query->whereDate('created_at', today()),
            'week'    => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month'   => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'quarter' => $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]),
            'semester' => $query->whereBetween('created_at', [now()->subMonths(6), now()]),
            'year'    => $query->whereYear('created_at', now()->year),
            default   => $query->whereDate('created_at', today()),
        };

        $sales = $query->get();

        // Chiffre d'affaires = somme des montants payés
        $revenue = $sales->sum('paid_amount');

        // Bénéfice = revenue - coût d'achat des produits vendus
        $profit = SaleItem::query()
            ->whereIn('sale_id', $sales->pluck('id'))
            ->select(DB::raw('SUM(line_total - (purchase_price_snapshot * quantity)) as profit'))
            ->value('profit') ?? 0;

        return [
            'revenue'       => $revenue,
            'profit'        => $profit,
            'sales_count'   => $sales->count(),
            'units_sold'    => SaleItem::whereIn('sale_id', $sales->pluck('id'))->sum('quantity'),
            'low_stock'     => $this->getLowStockCount(),
        ];
    }

    public function getSalesChartData(string $period = 'week'): array
    {
        return match ($period) {
            'week'  => $this->getWeeklyChart(),
            'month' => $this->getMonthlyChart(),
            'year'  => $this->getYearlyChart(),
            default => $this->getWeeklyChart(),
        };
    }

    private function getWeeklyChart(): array
    {
        $days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            $sales = Sale::whereDate('created_at', $date)
                ->where('sale_status', 'completed')
                ->sum('paid_amount');
            return [
                'label'  => $date->locale('fr')->dayName,
                'amount' => (int) $sales,
            ];
        });

        return $days->toArray();
    }

    private function getMonthlyChart(): array
    {
        $weeks = collect(range(3, 0))->map(function ($weeksAgo) {
            $start = now()->subWeeks($weeksAgo)->startOfWeek();
            $end   = now()->subWeeks($weeksAgo)->endOfWeek();
            $sales = Sale::whereBetween('created_at', [$start, $end])
                ->where('sale_status', 'completed')
                ->sum('paid_amount');
            return [
                'label'  => 'Sem. ' . $start->week,
                'amount' => (int) $sales,
            ];
        });

        return $weeks->toArray();
    }

    private function getYearlyChart(): array
    {
        $months = collect(range(1, 12))->map(function ($month) {
            $sales = Sale::whereMonth('created_at', $month)
                ->whereYear('created_at', now()->year)
                ->where('sale_status', 'completed')
                ->sum('paid_amount');
            return [
                'label'  => now()->setMonth($month)->locale('fr')->monthName,
                'amount' => (int) $sales,
            ];
        });

        return $months->toArray();
    }

    // ─── Stats vendeur ────────────────────────────────────────

    public function getVendeurStats(int $userId): array
    {
        $todaySales = Sale::query()
            ->where('created_by', $userId)
            ->where('sale_status', 'completed')
            ->whereDate('created_at', today())
            ->get();

        return [
            'sales_count' => $todaySales->count(),
            'units_sold'  => SaleItem::whereIn('sale_id', $todaySales->pluck('id'))
                ->sum('quantity'),
        ];
    }

    public function getRecentSales(int $limit = 5): \Illuminate\Support\Collection
    {
        return Sale::with(['reseller', 'items', 'createdBy'])
            ->where('sale_status', 'completed')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getResellersWithDebt(): \Illuminate\Support\Collection
    {
        return Reseller::where('solde_du', '>', 0)
            ->orderByDesc('solde_du')
            ->limit(5)
            ->get();
    }

    // ─── Commun ───────────────────────────────────────────────

    public function getLowStockCount(): int
    {
        return ProductModel::where('is_serialized', false)
            ->where('is_active', true)
            ->whereRaw('quantity_stock <= stock_minimum')
            ->count();
    }

    public function getLowStockProducts(): \Illuminate\Support\Collection
    {
        return ProductModel::with('brand')
            ->where('is_active', true)
            ->whereRaw('quantity_stock <= stock_minimum')
            ->orderByRaw('quantity_stock - stock_minimum ASC')
            ->limit(5)
            ->get();
    }
}
