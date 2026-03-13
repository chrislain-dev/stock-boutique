<?php

namespace App\Exports\Sheets;

use App\Enums\PaymentStatus;
use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CreancesSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(public string $dateFrom, public string $dateTo) {}

    public function title(): string
    {
        return 'Créances';
    }

    public function headings(): array
    {
        return ['Client', 'Téléphone', 'Type', 'Nb ventes', 'Solde dû'];
    }

    public function collection()
    {
        $clients = Sale::where('payment_status', '!=', PaymentStatus::PAID->value)
            ->where('customer_type', 'client')
            ->where('sale_status', 'completed')
            ->selectRaw('customer_name, customer_phone, SUM(total_amount - paid_amount) as solde, COUNT(*) as count')
            ->groupBy('customer_name', 'customer_phone')
            ->get()
            ->map(fn($r) => [$r->customer_name ?: 'Anonyme', $r->customer_phone, 'Client', $r->count, $r->solde]);

        $resellers = Sale::where('payment_status', '!=', PaymentStatus::PAID->value)
            ->where('customer_type', 'reseller')
            ->where('sale_status', 'completed')
            ->join('resellers', 'sales.reseller_id', '=', 'resellers.id')
            ->selectRaw('resellers.name, SUM(sales.total_amount - sales.paid_amount) as solde, COUNT(*) as count')
            ->groupBy('resellers.name')
            ->get()
            ->map(fn($r) => [$r->name, '—', 'Revendeur', $r->count, $r->solde]);

        return $clients->merge($resellers);
    }
}
