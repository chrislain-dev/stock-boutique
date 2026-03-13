<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Services\ActivityLogService;

class PurchaseObserver
{
    public function created(Purchase $purchase): void
    {
        ActivityLogService::log(
            action: 'create',
            description: "Achat {$purchase->reference} créé — {$purchase->total_amount} F",
            model: $purchase,
            newValues: $purchase->toArray(),
        );
    }

    public function updated(Purchase $purchase): void
    {
        $dirty = $purchase->getDirty();
        if (empty($dirty)) return;

        ActivityLogService::log(
            action: 'update',
            description: "Achat {$purchase->reference} modifié",
            model: $purchase,
            oldValues: array_intersect_key($purchase->getOriginal(), $dirty),
            newValues: $dirty,
        );
    }

    public function deleted(Purchase $purchase): void
    {
        ActivityLogService::log(
            action: 'delete',
            description: "Achat {$purchase->reference} supprimé",
            model: $purchase,
        );
    }
}
