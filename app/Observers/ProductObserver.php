<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\ActivityLogService;

class ProductObserver
{
    public function created(Product $product): void
    {
        ActivityLogService::log(
            action: 'create',
            description: "Produit créé — " . ($product->imei ?? $product->serial_number ?? "ID {$product->id}"),
            model: $product,
        );
    }

    public function updated(Product $product): void
    {
        $dirty = $product->getDirty();
        // Ignorer les champs de traçabilité banals
        unset($dirty['updated_at'], $dirty['updated_by']);
        if (empty($dirty)) return;

        ActivityLogService::log(
            action: 'update',
            description: "Produit modifié — " . ($product->imei ?? $product->serial_number ?? "ID {$product->id}"),
            model: $product,
            oldValues: array_intersect_key($product->getOriginal(), $dirty),
            newValues: $dirty,
        );
    }

    public function deleted(Product $product): void
    {
        ActivityLogService::log(
            action: 'delete',
            description: "Produit supprimé — " . ($product->imei ?? $product->serial_number ?? "ID {$product->id}"),
            model: $product,
        );
    }
}
