<?php

namespace App\Livewire\Products;

use App\Enums\ProductCondition;
use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Supplier;
use App\Services\ProductService;
use Livewire\Component;
use Mary\Traits\Toast;

class Edit extends Component
{
    use Toast;

    public Product $product;

    // ─── Champs ───────────────────────────────────────────────
    public ?int $product_model_id = null;
    public string $imei           = '';
    public string $serial_number  = '';
    public string $condition      = '';
    public string $state          = '';
    public string $location       = '';
    public string $defects        = '';
    public int $purchase_price  = 0;
    public int $client_price    = 0;
    public int $reseller_price  = 0;
    public string $purchase_date  = '';
    public ?int $supplier_id      = null;
    public string $notes          = '';
    public string $price_change_reason = '';

    // ─── Détecter si les prix ont changé ──────────────────────
    public int $original_purchase_price = 0;
    public int $original_client_price   = 0;
    public int $original_reseller_price = 0;

    public function mount(Product $product): void
    {
        abort_if(!auth()->user()->isAdmin(), 403);
        $this->product = $product;

        $this->product_model_id         = $product->product_model_id;
        $this->imei                     = $product->imei ?? '';
        $this->serial_number            = $product->serial_number ?? '';
        $this->condition                = $product->condition->value;
        $this->state                    = $product->state->value;
        $this->location                 = $product->location->value;
        $this->defects                  = $product->defects ?? '';
        $this->purchase_price           = $product->purchase_price;
        $this->client_price             = $product->client_price;
        $this->reseller_price           = $product->reseller_price;
        $this->purchase_date            = $product->purchase_date?->format('Y-m-d') ?? '';
        $this->supplier_id              = $product->supplier_id;
        $this->notes                    = $product->notes ?? '';

        // Sauvegarder les prix originaux pour détecter le changement
        $this->original_purchase_price  = $product->purchase_price;
        $this->original_client_price    = $product->client_price;
        $this->original_reseller_price  = $product->reseller_price;
    }

    public function pricesChanged(): bool
    {
        return $this->purchase_price !== $this->original_purchase_price
            || $this->client_price   !== $this->original_client_price
            || $this->reseller_price !== $this->original_reseller_price;
    }

    protected function rules(): array
    {
        return [
            'product_model_id'    => 'required|exists:product_models,id',
            'imei'                => 'nullable|string|unique:products,imei,' . $this->product->id,
            'serial_number'       => 'nullable|string|unique:products,serial_number,' . $this->product->id,
            'condition'           => 'required|in:sealed,refurbished,used,defective',
            'state'               => 'required|in:available,sold,reserved,returned,defective,in_repair',
            'location'            => 'required|in:store,transit,client,reseller,repair_shop',
            'purchase_price'      => 'required|numeric|min:0',
            'client_price'        => 'required|numeric|min:0',
            'reseller_price'      => 'required|numeric|min:0',
            'purchase_date'       => 'required|date',
            'supplier_id'         => 'nullable|exists:suppliers,id',
            'price_change_reason' => $this->pricesChanged() ? 'required|string|min:3' : 'nullable',
        ];
    }

    protected $messages = [
        'product_model_id.required'    => 'Le modèle est obligatoire.',
        'condition.required'           => 'La condition est obligatoire.',
        'state.required'               => 'L\'état est obligatoire.',
        'location.required'            => 'La localisation est obligatoire.',
        'purchase_price.required'      => 'Le prix d\'achat est obligatoire.',
        'client_price.required'        => 'Le prix client est obligatoire.',
        'reseller_price.required'      => 'Le prix revendeur est obligatoire.',
        'purchase_date.required'       => 'La date d\'achat est obligatoire.',
        'price_change_reason.required' => 'Veuillez indiquer la raison du changement de prix.',
        'imei.unique'                  => 'Cet IMEI est déjà utilisé.',
        'serial_number.unique'         => 'Ce numéro de série est déjà utilisé.',
    ];

    public function save(ProductService $service): void
    {
        $this->validate();

        $service->update($this->product, [
            'product_model_id'    => $this->product_model_id,
            'imei'                => $this->imei ?: null,
            'serial_number'       => $this->serial_number ?: null,
            'condition'           => $this->condition,
            'state'               => $this->state,
            'location'            => $this->location,
            'defects'             => $this->defects ?: null,
            'purchase_price'      => $this->purchase_price,
            'client_price'        => $this->client_price,
            'reseller_price'      => $this->reseller_price,
            'purchase_date'       => $this->purchase_date,
            'supplier_id'         => $this->supplier_id,
            'notes'               => $this->notes ?: null,
            'price_change_reason' => $this->price_change_reason ?: null,
        ]);

        $this->success('Produit mis à jour.');
        $this->redirect(route('products.show', $this->product->id), navigate: true);
    }

    public function render()
    {
        $productModels = ProductModel::with('brand')
            ->where('is_active', true)
            ->when(!auth()->user()->isAdmin(), fn($q) => $q->where('category', '!=', 'sextoys'))
            ->orderBy('name')
            ->get()
            ->map(fn($m) => ['id' => $m->id, 'name' => $m->full_name]);

        $suppliers = Supplier::active()->orderBy('name')->get()
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name]);

        return view('livewire.products.edit', [
            'productModels' => $productModels,
            'suppliers'     => $suppliers,
            'conditions'    => ProductCondition::cases(),
            'states'        => ProductState::cases(),
            'locations'     => ProductLocation::cases(),
        ])->layout('layouts.app', ['title' => 'Modifier — ' . $this->product->identifier]);
    }
}
