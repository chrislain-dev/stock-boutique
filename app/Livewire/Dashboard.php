<?php

namespace App\Livewire;

use App\Enums\PaymentStatus;
use App\Enums\ProductState;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function getKpis(): array
    {
        $today = now()->format('Y-m-d');
        $monthStart = now()->startOfMonth()->format('Y-m-d');

        // CA jour
        $caJour = Sale::whereDate('created_at', $today)
            ->where('sale_status', 'completed')
            ->sum('total_amount');

        // CA mois
        $caMois = Sale::whereBetween('created_at', [$monthStart . ' 00:00:00', $today . ' 23:59:59'])
            ->where('sale_status', 'completed')
            ->sum('total_amount');

        // Nb ventes jour
        $ventesJour = Sale::whereDate('created_at', $today)
            ->where('sale_status', 'completed')
            ->count();

        // Stock disponible
        $stockDispo = Product::where('state', ProductState::AVAILABLE->value)->count();

        // Créances totales
        $creances = Sale::where('payment_status', '!=', PaymentStatus::PAID->value)
            ->where('sale_status', 'completed')
            ->sum(DB::raw('total_amount - paid_amount'));

        return compact('caJour', 'caMois', 'ventesJour', 'stockDispo', 'creances');
    }

    public function getChartData(): array
    {
        $data = Sale::where('sale_status', 'completed')
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->locale('fr')->isoFormat('ddd D');
            $values[] = (float) ($data[$date]->total ?? 0);
        }

        return compact('labels', 'values');
    }

    public function getTopProducts(): array
    {
        return SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('product_models', 'sale_items.product_model_id', '=', 'product_models.id')
            ->join('brands', 'product_models.brand_id', '=', 'brands.id')
            ->where('sales.sale_status', 'completed')
            ->whereMonth('sales.created_at', now()->month)
            ->whereYear('sales.created_at', now()->year)
            ->selectRaw("brands.name || ' ' || product_models.name as product, COUNT(*) as qty, SUM(sale_items.line_total) as ca")
            ->groupBy('brands.name', 'product_models.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function getLastSales()
    {
        return Sale::with(['createdBy', 'reseller'])
            ->where('sale_status', 'completed')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();
    }

    public function getStockAlerts(): array
    {
        // Produits en réparation ou défectueux
        $enReparation = Product::where('state', ProductState::IN_REPAIR->value)->count();
        $defectueux   = Product::where('state', ProductState::DEFECTIVE->value)->count();

        // Modèles avec stock < 2
        $stockBas = DB::table('product_models')
            ->join('brands', 'product_models.brand_id', '=', 'brands.id')
            ->leftJoin('products', function ($join) {
                $join->on('products.product_model_id', '=', 'product_models.id')
                    ->where('products.state', ProductState::AVAILABLE->value);
            })
            ->selectRaw("brands.name || ' ' || product_models.name as product, COUNT(products.id) as qty")
            ->groupBy('brands.name', 'product_models.name')
            ->having(DB::raw('COUNT(products.id)'), '<', 2)
            ->orderBy('qty')
            ->limit(5)
            ->get()
            ->toArray();

        return compact('enReparation', 'defectueux', 'stockBas');
    }

    public function render()
    {
        $kpis        = $this->getKpis();
        $chartData   = $this->getChartData();
        $topProducts = $this->getTopProducts();
        $lastSales   = $this->getLastSales();
        $alerts      = $this->getStockAlerts();

        return view('livewire.dashboard', compact(
            'kpis',
            'chartData',
            'topProducts',
            'lastSales',
            'alerts'
        ))->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
