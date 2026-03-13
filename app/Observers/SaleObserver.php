<?php

namespace App\Observers;

use App\Models\Sale;
use App\Services\ActivityLogService;

class SaleObserver
{
    public function created(Sale $sale): void
    {
        ActivityLogService::log(
            action: 'create',
            description: "Vente {$sale->reference} créée — {$sale->total_amount} F",
            model: $sale,
            newValues: $sale->toArray(),
        );
    }

    public function updated(Sale $sale): void
    {
        $dirty = $sale->getDirty();
        if (empty($dirty)) return;

        ActivityLogService::log(
            action: 'update',
            description: "Vente {$sale->reference} modifiée",
            model: $sale,
            oldValues: array_intersect_key($sale->getOriginal(), $dirty),
            newValues: $dirty,
        );
    }

    public function deleted(Sale $sale): void
    {
        ActivityLogService::log(
            action: 'delete',
            description: "Vente {$sale->reference} supprimée",
            model: $sale,
        );
    }
}
