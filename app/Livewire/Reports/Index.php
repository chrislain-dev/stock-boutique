<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Reseller;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast;

    public string $period     = 'month';
    public string $dateFrom   = '';
    public string $dateTo     = '';
    public string $activeTab  = 'ca';

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function updatedPeriod(): void
    {
        match ($this->period) {
            'today'   => [$this->dateFrom, $this->dateTo] = [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'week'    => [$this->dateFrom, $this->dateTo] = [now()->startOfWeek()->format('Y-m-d'), now()->format('Y-m-d')],
            'month'   => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
            'quarter' => [$this->dateFrom, $this->dateTo] = [now()->startOfQuarter()->format('Y-m-d'), now()->format('Y-m-d')],
            'year'    => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
            'custom'  => null,
            default   => null,
        };
    }

    // ─── CA par période ───────────────────────────────────────
    public function getCaStats(): array
    {
        $sales = Sale::whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->where('sale_status', 'completed')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as total, SUM(paid_amount) as paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'rows'        => $sales,
            'total_ca'    => $sales->sum('total'),
            'total_paid'  => $sales->sum('paid'),
            'total_count' => $sales->sum('count'),
        ];
    }

    // ─── Marge et bénéfice ────────────────────────────────────
    public function getMarginStats(): array
    {
        $items = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('product_models', 'sale_items.product_model_id', '=', 'product_models.id')
            ->join('brands', 'product_models.brand_id', '=', 'brands.id')
            ->whereBetween('sales.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->where('sales.sale_status', 'completed')
            ->selectRaw('
                brands.name as brand,
                SUM(sale_items.line_total) as ca,
                SUM(sale_items.purchase_price_snapshot * sale_items.quantity) as cost,
                SUM(sale_items.line_total - (sale_items.purchase_price_snapshot * sale_items.quantity)) as profit,
                COUNT(*) as qty
            ')
            ->groupBy('brands.name')
            ->orderByDesc('profit')
            ->get();

        return [
            'rows'         => $items,
            'total_ca'     => $items->sum('ca'),
            'total_cost'   => $items->sum('cost'),
            'total_profit' => $items->sum('profit'),
        ];
    }

    // ─── Top produits ─────────────────────────────────────────
    public function getTopProducts(): array
    {
        return SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('product_models', 'sale_items.product_model_id', '=', 'product_models.id')
            ->join('brands', 'product_models.brand_id', '=', 'brands.id')
            ->whereBetween('sales.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->where('sales.sale_status', 'completed')
            ->selectRaw('
                brands.name || \' \' || product_models.name as product,
                COUNT(*) as qty,
                SUM(sale_items.line_total) as ca,
                SUM(sale_items.line_total - (sale_items.purchase_price_snapshot * sale_items.quantity)) as profit
            ')
            ->groupBy('brands.name', 'product_models.name')
            ->orderByDesc('qty')
            ->limit(20)
            ->get()
            ->toArray();
    }

    // ─── Créances ─────────────────────────────────────────────
    public function getCreances(): array
    {
        $clientCreances = Sale::where('payment_status', '!=', PaymentStatus::PAID->value)
            ->where('customer_type', 'client')
            ->where('sale_status', 'completed')
            ->selectRaw('customer_name, customer_phone, SUM(total_amount - paid_amount) as solde, COUNT(*) as count')
            ->groupBy('customer_name', 'customer_phone')
            ->orderByDesc('solde')
            ->get();

        $resellerCreances = Sale::where('payment_status', '!=', PaymentStatus::PAID->value)
            ->where('customer_type', 'reseller')
            ->where('sale_status', 'completed')
            ->join('resellers', 'sales.reseller_id', '=', 'resellers.id')
            ->selectRaw('resellers.name, SUM(sales.total_amount - sales.paid_amount) as solde, COUNT(*) as count')
            ->groupBy('resellers.name')
            ->orderByDesc('solde')
            ->get();

        return [
            'clients'          => $clientCreances,
            'resellers'        => $resellerCreances,
            'total_clients'    => $clientCreances->sum('solde'),
            'total_resellers'  => $resellerCreances->sum('solde'),
        ];
    }

    // ─── Mouvements stock ─────────────────────────────────────
    public function getStockStats(): array
    {
        $movements = StockMovement::whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->orderByDesc('count')
            ->get();

        return [
            'rows'  => $movements,
            'total' => $movements->sum('count'),
        ];
    }

    // ─── Export PDF ───────────────────────────────────────────
    public function exportPdf(): void
    {
        $this->redirect(route('reports.print', [
            'from' => $this->dateFrom,
            'to'   => $this->dateTo,
        ]));
    }

    // ─── Export Excel ─────────────────────────────────────────
    public function exportExcel(): void
    {
        $this->redirect(route('reports.excel', [
            'from' => $this->dateFrom,
            'to'   => $this->dateTo,
        ]));
    }

    public function render()
    {
        $periods = [
            ['id' => 'today',   'name' => "Aujourd'hui"],
            ['id' => 'week',    'name' => 'Cette semaine'],
            ['id' => 'month',   'name' => 'Ce mois'],
            ['id' => 'quarter', 'name' => 'Ce trimestre'],
            ['id' => 'year',    'name' => 'Cette année'],
            ['id' => 'custom',  'name' => 'Personnalisé'],
        ];

        $tabs = [
            ['id' => 'ca',       'name' => 'CA par période'],
            ['id' => 'margin',   'name' => 'Marge & Bénéfice'],
            ['id' => 'products', 'name' => 'Top Produits'],
            ['id' => 'creances', 'name' => 'Créances'],
            ['id' => 'stock',    'name' => 'Stock'],
        ];

        $caStats      = $this->getCaStats();
        $marginStats  = $this->getMarginStats();
        $topProducts  = $this->getTopProducts();
        $creances     = $this->getCreances();
        $stockStats   = $this->getStockStats();

        return view('livewire.reports.index', compact(
            'periods',
            'tabs',
            'caStats',
            'marginStats',
            'topProducts',
            'creances',
            'stockStats'
        ))->layout('layouts.app', ['title' => 'Rapports']);
    }
}
