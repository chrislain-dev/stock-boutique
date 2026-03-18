<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TopProductsSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(public string $dateFrom, public string $dateTo) {}

    public function title(): string
    {
        return 'Top Produits';
    }

    public function headings(): array
    {
        return ['Produit', 'Qté vendue', 'CA', 'Bénéfice'];
    }

    public function collection()
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('product_models', 'sale_items.product_model_id', '=', 'product_models.id')
            ->join('brands', 'product_models.brand_id', '=', 'brands.id')
            ->whereBetween('sales.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->where('sales.sale_status', 'completed')
            ->selectRaw("brands.name || ' ' || product_models.name as product, COUNT(*) as qty, SUM(sale_items.line_total) as ca, SUM(sale_items.line_total - (sale_items.purchase_price_snapshot * sale_items.quantity)) as profit")
            ->groupBy('brands.name', 'product_models.name')
            ->orderByDesc('qty')
            ->limit(20)
            ->get()
            ->map(fn($r) => [$r->product, $r->qty, $r->ca, $r->profit]);
    }
}
