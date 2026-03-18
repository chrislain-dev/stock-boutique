<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MarginSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(public string $dateFrom, public string $dateTo) {}

    public function title(): string
    {
        return 'Marge par marque';
    }

    public function headings(): array
    {
        return ['Marque', 'Qté', 'CA', 'Coût', 'Bénéfice', 'Marge %'];
    }

    public function collection()
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('product_models', 'sale_items.product_model_id', '=', 'product_models.id')
            ->join('brands', 'product_models.brand_id', '=', 'brands.id')
            ->whereBetween('sales.created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->where('sales.sale_status', 'completed')
            ->selectRaw('brands.name, COUNT(*) as qty, SUM(sale_items.line_total) as ca, SUM(sale_items.purchase_price_snapshot * sale_items.quantity) as cost, SUM(sale_items.line_total - (sale_items.purchase_price_snapshot * sale_items.quantity)) as profit')
            ->groupBy('brands.name')
            ->orderByDesc('profit')
            ->get()
            ->map(fn($r) => [
                $r->name,
                $r->qty,
                $r->ca,
                $r->cost,
                $r->profit,
                $r->ca > 0 ? round(($r->profit / $r->ca) * 100, 1) . '%' : '0%',
            ]);
    }
}
