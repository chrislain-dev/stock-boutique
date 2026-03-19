<?php

namespace App\Livewire\SupplierReturns;

use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast, WithPagination;

    public string $statusFilter = 'pending';

    // Modal "Marquer comme envoyé"
    public bool   $showSentModal   = false;
    public ?int   $sentReturnId    = null;
    public string $sent_notes      = '';

    // Modal "Remplacement reçu"
    public bool   $showReplaceModal    = false;
    public ?int   $replaceReturnId     = null;
    public string $replace_imei        = '';
    public string $replace_search_error = '';
    public ?int   $replace_product_id  = null;
    public string $replace_notes       = '';

    // Modal "Déclarer un retour" (appelé depuis sales.show via event)
    public bool   $showDeclareModal = false;
    public ?int   $declare_sale_id  = null;
    public ?int   $declare_product_id = null;
    public string $declare_reason   = '';
    public string $declare_notes    = '';
    public string $declare_error    = '';

    public bool   $showCreateReplacementModal = false;
    public string $new_product_model_id       = '';
    public string $new_product_imei           = '';
    public string $new_product_purchase_price = '';

    protected $listeners = ['openDeclareReturn' => 'openDeclareModal'];

    public function openCreateReplacementModal(): void
    {
        // Pré-remplir l'IMEI si déjà saisi dans le champ recherche
        $this->new_product_imei           = $this->replace_imei;
        $this->new_product_model_id       = '';
        $this->new_product_purchase_price = '';
        $this->showCreateReplacementModal = true;
        $this->resetErrorBag();
    }

    public function createReplacementProduct(): void
    {
        $this->validate([
            'new_product_model_id' => 'required|exists:product_models,id',
            'new_product_imei'     => 'required|string|unique:products,imei',
        ], [
            'new_product_model_id.required' => 'Sélectionnez un modèle.',
            'new_product_imei.required'     => 'L\'IMEI est obligatoire.',
            'new_product_imei.unique'       => 'Cet IMEI existe déjà en base.',
        ]);

        $productReturn   = ProductReturn::with('product')->findOrFail($this->replaceReturnId);
        $originalProduct = $productReturn->product;

        $newProduct = Product::create([
            'product_model_id' => (int) $this->new_product_model_id,
            'imei'             => trim($this->new_product_imei),
            'state'            => ProductState::AVAILABLE->value,
            'location'         => ProductLocation::STORE->value,
            'purchase_price'   => $originalProduct->purchase_price,
            'client_price'     => $originalProduct->client_price,     // ← correct
            'reseller_price'   => $originalProduct->reseller_price,   // ← correct
            'purchase_date'    => now()->toDateString(),
            'supplier_id'      => $originalProduct->supplier_id,
            'created_by'       => Auth::id(),
            'updated_by'       => Auth::id(),
        ]);

        $this->replace_product_id         = $newProduct->id;
        $this->replace_imei               = $newProduct->imei;
        $this->replace_search_error       = '';
        $this->showCreateReplacementModal = false;

        $this->success("Produit créé — IMEI {$newProduct->imei}");
    }

    // ─── Déclarer un retour ───────────────────────────────────
    public function openDeclareModal(int $saleId, int $productId): void
    {
        $this->declare_sale_id    = $saleId;
        $this->declare_product_id = $productId;
        $this->declare_reason     = '';
        $this->declare_notes      = '';
        $this->declare_error      = '';
        $this->showDeclareModal   = true;
        $this->resetErrorBag();
    }

    public function declareReturn(): void
    {
        $this->validate([
            'declare_reason' => 'required|min:5',
        ], [
            'declare_reason.required' => 'Décrivez la défaillance constatée.',
            'declare_reason.min'      => 'La description doit faire au moins 5 caractères.',
        ]);

        $product = Product::findOrFail($this->declare_product_id);

        DB::transaction(function () use ($product) {
            // Changer l'état et la location du produit
            $product->update([
                'state'      => ProductState::DEFECTIVE->value,
                'location'   => ProductLocation::SUPPLIER_RETURN->value,
                'updated_by' => Auth::id(),
            ]);

            // Créer le ticket de retour
            ProductReturn::create([
                'product_id'  => $product->id,
                'sale_id'     => $this->declare_sale_id,
                'reason'      => $this->declare_reason,
                'notes'       => $this->declare_notes ?: null,
                'status'      => 'pending',
                'declared_by' => Auth::id(),
            ]);

            // Mouvement de stock
            StockMovement::create([
                'product_model_id' => $product->product_model_id,
                'product_id'       => $product->id,
                'type'             => StockMovementType::CLIENT_RETURN->value,
                'quantity'         => 1,
                'quantity_before'  => 0,
                'quantity_after'   => 1,
                'location_from'    => ProductLocation::CLIENT->value,
                'location_to'      => ProductLocation::SUPPLIER_RETURN->value,
                'notes'            => "Retour client défectueux — {$this->declare_reason}",
                'created_by'       => Auth::id(),
            ]);
        });

        $this->showDeclareModal = false;
        $this->success('Retour déclaré. Le produit est dans la file "À renvoyer fournisseur".');
    }

    // ─── Marquer comme envoyé ─────────────────────────────────
    public function openSentModal(int $returnId): void
    {
        $this->sentReturnId  = $returnId;
        $this->sent_notes    = '';
        $this->showSentModal = true;
        $this->resetErrorBag();
    }

    public function markAsSent(): void
    {
        $productReturn = ProductReturn::findOrFail($this->sentReturnId);

        $productReturn->update([
            'status'  => 'sent_to_supplier',
            'sent_at' => now(),
            'notes'   => $this->sent_notes
                ? ($productReturn->notes ? $productReturn->notes . "\n" . $this->sent_notes : $this->sent_notes)
                : $productReturn->notes,
        ]);

        $productReturn->product->update([
            'state'      => ProductState::RETURNED_TO_SUPPLIER->value,
            'updated_by' => Auth::id(),
        ]);

        StockMovement::create([
            'product_model_id' => $productReturn->product->product_model_id,
            'product_id'       => $productReturn->product_id,
            'type'             => StockMovementType::SUPPLIER_RETURN->value,
            'quantity'         => 1,
            'quantity_before'  => 1,
            'quantity_after'   => 0,
            'location_from'    => ProductLocation::SUPPLIER_RETURN->value,
            'location_to'      => null,
            'notes'            => 'Envoyé au fournisseur pour remplacement' . ($this->sent_notes ? " — {$this->sent_notes}" : ''),
            'created_by'       => Auth::id(),
        ]);

        $this->showSentModal = false;
        $this->success('Produit marqué comme envoyé au fournisseur.');
    }

    // ─── Remplacement reçu ────────────────────────────────────
    public function openReplaceModal(int $returnId): void
    {
        $this->replaceReturnId      = $returnId;
        $this->replace_imei         = '';
        $this->replace_search_error = '';
        $this->replace_product_id   = null;
        $this->replace_notes        = '';
        $this->showReplaceModal     = true;
        $this->resetErrorBag();
    }

    public function searchReplacement(): void
    {
        $this->replace_search_error = '';
        $this->replace_product_id   = null;

        if (empty(trim($this->replace_imei))) return;

        $product = Product::where(
            fn($q) => $q->where('imei', trim($this->replace_imei))
                ->orWhere('serial_number', trim($this->replace_imei))
        )
            ->where('state', ProductState::AVAILABLE->value)
            ->first();

        if ($product) {
            $this->replace_product_id = (int) $product->id;
        } else {
            $this->replace_search_error = 'Produit non trouvé dans le stock disponible.';
        }
    }

    public function receiveReplacement(): void
    {
        $this->validate([
            'replace_product_id' => 'required|exists:products,id',
        ], [
            'replace_product_id.required' => 'Scannez l\'IMEI du produit de remplacement.',
        ]);

        $productReturn      = ProductReturn::with('product')->findOrFail($this->replaceReturnId);
        $replacementProduct = Product::findOrFail($this->replace_product_id);

        DB::transaction(function () use ($productReturn, $replacementProduct) {
            // Mettre le remplacement en boutique
            $replacementProduct->update([
                'location'   => ProductLocation::STORE->value,
                'state'      => ProductState::AVAILABLE->value,
                'updated_by' => Auth::id(),
            ]);

            // Clore le ticket de retour
            $productReturn->update([
                'status'                => 'replacement_received',
                'replacement_product_id' => $replacementProduct->id,
                'replaced_at'           => now(),
                'replaced_by'           => Auth::id(),
                'notes'                 => $productReturn->notes
                    ? $productReturn->notes . ($this->replace_notes ? "\n" . $this->replace_notes : '')
                    : ($this->replace_notes ?: null),
            ]);

            // Mouvement : entrée du remplacement en boutique
            StockMovement::create([
                'product_model_id' => $replacementProduct->product_model_id,
                'product_id'       => $replacementProduct->id,
                'type'             => StockMovementType::STOCK_IN->value,
                'quantity'         => 1,
                'quantity_before'  => 0,
                'quantity_after'   => 1,
                'location_from'    => null,
                'location_to'      => ProductLocation::STORE->value,
                'notes'            => "Remplacement fournisseur — retour #{$productReturn->id}",
                'created_by'       => Auth::id(),
            ]);
        });

        $this->showReplaceModal = false;
        $this->success('Remplacement enregistré. Le nouveau produit est en boutique.');
    }

    public function render()
    {
        $returns = ProductReturn::with([
            'product.productModel.brand',
            'sale',
            'replacementProduct.productModel',
            'declaredBy',
        ])
            ->when(
                $this->statusFilter !== 'all',
                fn($q) => $q->where('status', $this->statusFilter)
            )
            ->orderByDesc('created_at')
            ->paginate(20);

        $replaceProduct = $this->replace_product_id
            ? Product::with('productModel')->find($this->replace_product_id)
            : null;

        $declareSale = $this->declare_sale_id
            ? \App\Models\Sale::find($this->declare_sale_id)
            : null;

        $declareProduct = $this->declare_product_id
            ? Product::with('productModel')->find($this->declare_product_id)
            : null;

        $productModels = \App\Models\ProductModel::with('brand')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.supplier-returns.index', compact(
            'returns',
            'replaceProduct',
            'declareSale',
            'declareProduct',
            'productModels',
        ))->layout('layouts.app', ['title' => 'Retours fournisseur']);
    }
}
