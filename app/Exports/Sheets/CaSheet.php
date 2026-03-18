<?php

namespace App\Exports\Sheets;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CaSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        public string $dateFrom,
        public string $dateTo,
    ) {}

    public function title(): string
    {
        return 'CA par jour';
    }

    public function headings(): array
    {
        return ['Date', 'Nb ventes', 'CA total', 'Encaissé', 'Reste dû'];
    }

    public function collection()
    {
        return Sale::whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->where('sale_status', 'completed')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as total, SUM(paid_amount) as paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => [
                $r->date,
                $r->count,
                $r->total,
                $r->paid,
                $r->total - $r->paid,
            ]);
    }
}
