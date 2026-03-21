<?php

namespace App\Livewire\Reprises;

use App\Enums\ProductCondition;
use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    // Modal "Mettre en boutique"
    public bool   $showStoreModal   = false;
    public ?int   $storeProductId   = null;
    public string $store_client_price   = '';
    public string $store_reseller_price = '';
    public string $store_condition      = 'used';
    public string $store_notes          = '';

    // Modal "Envoyer en réparation"
    public bool   $showRepairModal  = false;
    public ?int   $repairProductId  = null;
    public string $repair_notes     = '';

    public bool    $showDetailModal = false;
    public ?int    $detailProductId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ─── Ouvrir modal boutique ────────────────────────────────
    public function openStoreModal(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->storeProductId        = $productId;
        $this->store_client_price    = (string) $product->client_price;
        $this->store_reseller_price  = (string) $product->reseller_price;
        $this->store_condition       = 'used';
        $this->store_notes           = $product->notes ?? '';
        $this->showStoreModal        = true;
        $this->resetErrorBag();
    }

    // ─── Mettre en boutique ───────────────────────────────────
    public function putInStore(): void
    {
        $this->validate([
            'store_client_price'   => 'required|numeric|min:0',
            'store_reseller_price' => 'required|numeric|min:0',
        ], [
            'store_client_price.required'   => 'Le prix client est obligatoire.',
            'store_reseller_price.required' => 'Le prix revendeur est obligatoire.',
            'store_client_price.min'        => 'Le prix doit être positif.',
        ]);

        $product = Product::findOrFail($this->storeProductId);

        DB::transaction(function () use ($product) {
            $product->update([
                'location'       => ProductLocation::STORE->value,  // physiquement en boutique
                'state'          => ProductState::TRADE_IN->value,  // ← reste dans card Reprise
                'condition'      => $this->store_condition,
                'client_price'   => (int) $this->store_client_price,
                'reseller_price' => (int) $this->store_reseller_price,
                'notes'          => $this->store_notes ?: null,
                'updated_by'     => Auth::id(),
            ]);

            StockMovement::create([
                'product_model_id' => $product->product_model_id,
                'product_id'       => $product->id,
                'type'             => StockMovementType::TRANSFER->value,
                'quantity'         => 1,
                'quantity_before'  => 1,
                'quantity_after'   => 1,
                'location_from'    => ProductLocation::REPRISE->value,
                'location_to'      => ProductLocation::STORE->value,
                'notes'            => "Reprise mise en vente — prix client : {$this->store_client_price}",
                'created_by'       => Auth::id(),
            ]);
        });

        $this->showStoreModal = false;
        $this->success('Produit mis en boutique avec succès.');
    }

    // ─── Ouvrir modal réparation ──────────────────────────────
    public function openRepairModal(int $productId): void
    {
        $this->repairProductId = $productId;
        $this->repair_notes    = '';
        $this->showRepairModal = true;
        $this->resetErrorBag();
    }

    // ─── Envoyer en réparation ────────────────────────────────
    public function sendToRepair(): void
    {
        $this->validate([
            'repair_notes' => 'required|min:5',
        ], [
            'repair_notes.required' => 'Décrivez la raison de l\'envoi en réparation.',
            'repair_notes.min'      => 'La description doit faire au moins 5 caractères.',
        ]);

        $product = Product::findOrFail($this->repairProductId);

        DB::transaction(function () use ($product) {
            $product->update([
                'location'   => ProductLocation::REPAIR_SHOP->value,
                'state'      => ProductState::IN_REPAIR->value,
                'updated_by' => Auth::id(),
            ]);

            StockMovement::create([
                'product_model_id' => $product->product_model_id,
                'product_id'       => $product->id,
                'type'             => StockMovementType::TRANSFER->value,
                'quantity'         => 1,
                'quantity_before'  => 1,
                'quantity_after'   => 1,
                'location_from'    => ProductLocation::REPRISE->value,
                'location_to'      => ProductLocation::REPAIR_SHOP->value,
                'notes'            => "Reprise envoyée en réparation : {$this->repair_notes}",
                'created_by'       => Auth::id(),
            ]);
        });

        $this->showRepairModal = false;
        $this->success('Produit envoyé en réparation.');
    }


    // Ouvrir modal détail
    public function openDetailModal(int $productId): void
    {
        $this->detailProductId = $productId;
        $this->showDetailModal = true;
    }

    // Depuis la modal détail → ouvrir boutique
    public function openStoreFromDetail(): void
    {
        $this->showDetailModal = false;
        $this->openStoreModal($this->detailProductId);
    }

    // Depuis la modal détail → ouvrir réparation
    public function openRepairFromDetail(): void
    {
        $this->showDetailModal = false;
        $this->openRepairModal($this->detailProductId);
    }

    public function render()
    {
        $reprises = Product::with('productModel.brand')
            ->where('location', ProductLocation::REPRISE->value)
            ->when(
                $this->search,
                fn($q) =>
                $q->where(
                    fn($q2) =>
                    $q2->where('imei', 'ilike', "%{$this->search}%")
                        ->orWhere('notes', 'ilike', "%{$this->search}%")
                        ->orWhereHas(
                            'productModel',
                            fn($q3) =>
                            $q3->where('name', 'ilike', "%{$this->search}%")
                        )
                )
            )
            ->orderByDesc('created_at')
            ->paginate(20);

        $conditions = collect(ProductCondition::cases())
            ->map(fn($c) => ['id' => $c->value, 'name' => $c->label()])
            ->toArray();

        $detailProduct = $this->detailProductId
            ? Product::with(['productModel.brand', 'createdBy'])->find($this->detailProductId)
            : null;

        return view('livewire.reprises.index', compact('reprises', 'conditions', 'detailProduct'))
            ->layout('layouts.app', ['title' => 'Reprises / Troc']);
    }
}
