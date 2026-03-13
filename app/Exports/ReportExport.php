<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\SaleItem;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportExport implements WithMultipleSheets
{
    public function __construct(
        public string $dateFrom,
        public string $dateTo,
    ) {}

    public function sheets(): array
    {
        return [
            'CA'       => new Sheets\CaSheet($this->dateFrom, $this->dateTo),
            'Marge'    => new Sheets\MarginSheet($this->dateFrom, $this->dateTo),
            'Produits' => new Sheets\TopProductsSheet($this->dateFrom, $this->dateTo),
            'Créances' => new Sheets\CreancesSheet($this->dateFrom, $this->dateTo),
        ];
    }
}
