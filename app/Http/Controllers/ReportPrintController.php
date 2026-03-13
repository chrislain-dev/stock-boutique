<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportPrintController extends Controller
{
    public function __invoke(string $from, string $to)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $caStats = Sale::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('sale_status', 'completed')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as total, SUM(paid_amount) as paid')
            ->groupBy('date')->orderBy('date')->get();

        $marginStats = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('product_models', 'sale_items.product_model_id', '=', 'product_models.id')
            ->join('brands', 'product_models.brand_id', '=', 'brands.id')
            ->whereBetween('sales.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('sales.sale_status', 'completed')
            ->selectRaw('brands.name as brand, SUM(sale_items.line_total) as ca, SUM(sale_items.purchase_price_snapshot * sale_items.quantity) as cost, SUM(sale_items.line_total - (sale_items.purchase_price_snapshot * sale_items.quantity)) as profit, COUNT(*) as qty')
            ->groupBy('brands.name')->orderByDesc('profit')->get();

        return view('reports.print', compact('caStats', 'marginStats', 'from', 'to'));
    }
}
