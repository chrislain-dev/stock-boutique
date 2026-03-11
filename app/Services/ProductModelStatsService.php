<?php

namespace App\Services;

use App\Models\ProductModel;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class ProductModelStatsService
{
    public function getCategoryStats(string $period = 'month'): array
    {
        [$startDate, $endDate] = $this->getPeriodDates($period);

        $categories = ['telephone', 'pc', 'tablet', 'accessory'];
        $stats = [];

        foreach ($categories as $category) {
            // ─── Investissement actuel (stock disponible) ─────
            $investment = DB::table('products')
                ->join('product_models', 'products.product_model_id', '=', 'product_models.id')
                ->where('product_models.category', $category)
                ->where('products.state', 'available')
                ->whereNull('products.deleted_at')
                ->sum('products.purchase_price');

            // Investissement accessoires (non sérialisés)
            $accessoryInvestment = DB::table('product_models')
                ->where('category', $category)
                ->where('is_serialized', false)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->selectRaw('SUM(quantity_stock * default_purchase_price) as total')
                ->value('total') ?? 0;

            $totalInvestment = $investment + $accessoryInvestment;

            // ─── Ventes sur la période ────────────────────────
            $salesData = DB::table('sale_items')
                ->join('product_models', 'sale_items.product_model_id', '=', 'product_models.id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('product_models.category', $category)
                ->where('sales.sale_status', 'completed')
                ->whereBetween('sales.created_at', [$startDate, $endDate])
                ->whereNull('sales.deleted_at')
                ->selectRaw('
                    SUM(sale_items.line_total) as revenue,
                    SUM(sale_items.purchase_price_snapshot * sale_items.quantity) as cost,
                    SUM(sale_items.quantity) as units_sold,
                    COUNT(DISTINCT sales.id) as sales_count
                ')
                ->first();

            $revenue  = $salesData->revenue ?? 0;
            $cost     = $salesData->cost ?? 0;
            $profit   = $revenue - $cost;

            // ─── Nombre de modèles ────────────────────────────
            $modelsCount = ProductModel::where('category', $category)
                ->where('is_active', true)
                ->count();

            // ─── Stock disponible ─────────────────────────────
            $stockCount = ProductModel::where('category', $category)
                ->where('is_active', true)
                ->get()
                ->sum('available_stock');

            $stats[$category] = [
                'investment'  => (float) $totalInvestment,
                'revenue'     => (float) $revenue,
                'cost'        => (float) $cost,
                'profit'      => (float) $profit,
                'units_sold'  => (int) ($salesData->units_sold ?? 0),
                'sales_count' => (int) ($salesData->sales_count ?? 0),
                'models_count' => $modelsCount,
                'stock_count' => $stockCount,
            ];
        }

        return $stats;
    }

    private function getPeriodDates(string $period): array
    {
        return match ($period) {
            'day'     => [now()->startOfDay(),     now()->endOfDay()],
            'week'    => [now()->startOfWeek(),    now()->endOfWeek()],
            'month'   => [now()->startOfMonth(),   now()->endOfMonth()],
            'quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'semester' => [now()->subMonths(6),     now()],
            'year'    => [now()->startOfYear(),    now()->endOfYear()],
            default   => [now()->startOfMonth(),   now()->endOfMonth()],
        };
    }
}
