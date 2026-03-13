<?php

namespace App\Services;

use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductService
{
    // ─── Créer un seul produit sérialisé ──────────────────────
    public function createSingle(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create([
                'product_model_id' => $data['product_model_id'],
                'imei'             => $data['imei'] ?? null,
                'serial_number'    => $data['serial_number'] ?? null,
                'state'            => ProductState::AVAILABLE->value,
                'location'         => ProductLocation::STORE->value,
                'defects'          => $data['defects'] ?? null,
                'purchase_price'   => $data['purchase_price'],
                'client_price'     => $data['client_price'],
                'reseller_price'   => $data['reseller_price'],
                'purchase_date'    => $data['purchase_date'],
                'supplier_id'      => $data['supplier_id'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'created_by'       => Auth::id(),
                'updated_by'       => Auth::id(),
            ]);

            // ─── Mouvement de stock ───────────────────────────
            StockMovement::create([
                'product_model_id' => $product->product_model_id,
                'product_id'       => $product->id,
                'type'             => StockMovementType::STOCK_IN->value,
                'quantity'         => 1,
                'quantity_before'  => 0,
                'quantity_after'   => 1,
                'location_from'    => null,
                'location_to'      => ProductLocation::STORE->value,
                'notes'            => 'Entrée stock initiale',
                'created_by'       => Auth::id(),
            ]);

            return $product;
        });
    }

    // ─── Créer plusieurs produits depuis liste IMEI ───────────
    public function createBulkFromImei(array $imeiList, array $commonData): array
    {
        $results = ['success' => [], 'errors' => []];

        foreach ($imeiList as $imei) {
            $imei = trim($imei);
            if (empty($imei)) continue;

            // Vérifier IMEI unique
            if (Product::where('imei', $imei)->exists()) {
                $results['errors'][] = "IMEI {$imei} déjà existant en base.";
                continue;
            }

            try {
                $product = $this->createSingle(array_merge($commonData, ['imei' => $imei]));
                $results['success'][] = $imei;
            } catch (\Exception $e) {
                $results['errors'][] = "IMEI {$imei} — erreur : {$e->getMessage()}";
            }
        }

        return $results;
    }

    // ─── Import CSV ───────────────────────────────────────────
    public function importFromCsv(string $csvContent, array $commonData): array
    {
        $lines = array_filter(
            array_map('trim', explode("\n", $csvContent))
        );

        // Ignorer la première ligne si c'est un header
        if (count($lines) > 0 && !is_numeric(substr($lines[0], 0, 1))) {
            array_shift($lines);
        }

        $imeiList = [];
        foreach ($lines as $line) {
            // Supporter CSV avec colonnes ou juste IMEI seul
            $columns = str_getcsv($line);
            $imeiList[] = $columns[0]; // première colonne = IMEI
        }

        return $this->createBulkFromImei($imeiList, $commonData);
    }

    // ─── Mettre à jour un produit ─────────────────────────────
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            // Historique prix si changement
            if (
                $product->purchase_price != $data['purchase_price'] ||
                $product->client_price   != $data['client_price'] ||
                $product->reseller_price != $data['reseller_price']
            ) {
                \App\Models\PriceHistory::create([
                    'product_id'         => $product->id,
                    'old_purchase_price' => $product->purchase_price,
                    'old_client_price'   => $product->client_price,
                    'old_reseller_price' => $product->reseller_price,
                    'new_purchase_price' => $data['purchase_price'],
                    'new_client_price'   => $data['client_price'],
                    'new_reseller_price' => $data['reseller_price'],
                    'reason'             => $data['price_change_reason'] ?? 'Modification via interface',
                    'created_by'         => Auth::id(),
                ]);
            }

            // Mouvement si changement de localisation
            if ($product->location->value !== $data['location']) {
                StockMovement::create([
                    'product_model_id' => $product->product_model_id,
                    'product_id'       => $product->id,
                    'type'             => StockMovementType::TRANSFER->value,
                    'quantity'         => 1,
                    'quantity_before'  => 1,
                    'quantity_after'   => 1,
                    'location_from'    => $product->location->value,
                    'location_to'      => $data['location'],
                    'notes'            => 'Changement de localisation',
                    'created_by'       => Auth::id(),
                ]);
            }

            $product->update([
                'imei'           => $data['imei'] ?? $product->imei,
                'serial_number'  => $data['serial_number'] ?? $product->serial_number,
                'state'          => $data['state'],
                'location'       => $data['location'],
                'defects'        => $data['defects'] ?? null,
                'purchase_price' => $data['purchase_price'],
                'client_price'   => $data['client_price'],
                'reseller_price' => $data['reseller_price'],
                'purchase_date'  => $data['purchase_date'],
                'supplier_id'    => $data['supplier_id'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'updated_by'     => Auth::id(),
            ]);

            return $product->fresh();
        });
    }
}
