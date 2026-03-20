<?php

namespace App\Livewire\Products;

use App\Http\Requests\CreateProductRequest;
use App\Models\ProductModel;
use App\Models\Supplier;
use App\Services\ProductService;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class Create extends Component
{
    use Toast, WithFileUploads;

    // ─── Mode d'ajout ─────────────────────────────────────────
    public string $mode = ''; // single, bulk, import

    // ─── Champs communs ───────────────────────────────────────
    public ?int $product_model_id  = null;
    public string $defects         = '';
    public int $purchase_price   = 0;
    public int $client_price     = 0;
    public int $reseller_price   = 0;
    public string $purchase_date   = '';
    public ?int $supplier_id       = null;
    public string $notes           = '';

    // ─── Mode single ──────────────────────────────────────────
    public string $imei          = '';
    public string $serial_number = '';

    // ─── Mode bulk (plusieurs IMEI) ───────────────────────────
    public string $imeiList = '';   // Un IMEI par ligne
    public array $imeiFields = ['']; // Champs dynamiques

    // ─── Mode import CSV ──────────────────────────────────────
    public $csvFile = null;

    // ─── Résultats après traitement ───────────────────────────
    public array $results = [];
    public bool $showResults = false;

    public string $modelCategory = ''; // telephone, pc, tablet, accessory

    public bool $showAfterSaveModal = false;
    public ?int $lastCreatedProductId = null;
    public int $lastCreatedCount = 0;



    public function mount(): void
    {
        $this->purchase_date = now()->format('Y-m-d');
    }

    // ─── Chargement auto des prix depuis le modèle ────────────
    public function updatedProductModelId(): void
    {
        if (!$this->product_model_id) return;

        $model = ProductModel::find($this->product_model_id);
        if (!$model) return;

        $this->modelCategory = $model->category->value;

        if ($model->default_purchase_price) $this->purchase_price = $model->default_purchase_price;
        if ($model->default_client_price)   $this->client_price   = $model->default_client_price;
        if ($model->default_reseller_price) $this->reseller_price = $model->default_reseller_price;
    }

    // ─── Ajout/suppression champs IMEI dynamiques ─────────────
    public function addImeiField(): void
    {
        $this->imeiFields[] = '';
    }

    public function removeImeiField(int $index): void
    {
        unset($this->imeiFields[$index]);
        $this->imeiFields = array_values($this->imeiFields);
    }

    public function redirectToProduct(): void
    {
        $this->redirect(route('products.show', $this->lastCreatedProductId), navigate: true);
    }

    public function redirectToList(): void
    {
        $this->redirect(route('products.index'), navigate: true);
    }

    public function resetAfterSave(): void
    {
        $this->showAfterSaveModal  = false;
        $this->lastCreatedProductId = null;
        $this->mode                = '';
        $this->purchase_date       = now()->format('Y-m-d');
    }

    // ─── Sauvegarder ─────────────────────────────────────────
    public function save(ProductService $service): void
    {
        // Prepare data for validation
        $data = [
            'product_model_id' => $this->product_model_id,
            'purchase_price'   => $this->purchase_price,
            'client_price'     => $this->client_price,
            'reseller_price'   => $this->reseller_price,
            'supplier_id'      => $this->supplier_id,
            'purchase_date'    => $this->purchase_date,
            'defects'          => $this->defects,
            'notes'            => $this->notes,
        ];

        // Add IMEI/serial for single mode
        if ($this->mode === 'single') {
            $data['imei'] = $this->imei;
            $data['serial_number'] = $this->serial_number;
        }

        // Validate using FormRequest
        $validator = Validator::make($data, (new CreateProductRequest())->rules(), (new CreateProductRequest())->messages());
        $validator->validate();

        $commonData = [
            'product_model_id' => $this->product_model_id,
            'defects'          => $this->defects ?: null,
            'purchase_price'   => $this->purchase_price,
            'client_price'     => $this->client_price,
            'reseller_price'   => $this->reseller_price,
            'purchase_date'    => $this->purchase_date,
            'supplier_id'      => $this->supplier_id,
            'notes'            => $this->notes ?: null,
        ];

        if ($this->mode === 'single') {
            $product = $service->createSingle(array_merge($commonData, [
                'imei'          => $this->imei ?: null,
                'serial_number' => $this->serial_number ?: null,
            ]));
            $this->lastCreatedProductId = $product->id;
            $this->lastCreatedCount = 1;
            $this->showAfterSaveModal = true;
            return;
        }

        if ($this->mode === 'bulk') {
            $fromTextarea = array_filter(array_map('trim', explode("\n", $this->imeiList)));
            $fromFields   = array_filter(array_map('trim', $this->imeiFields));
            $allImei      = array_unique(array_merge($fromTextarea, $fromFields));

            $this->results     = $service->createBulkFromImei($allImei, $commonData);
            $this->showResults = true;
            $this->lastCreatedCount = count($this->results['success']);

            if ($this->lastCreatedCount > 0) $this->success("{$this->lastCreatedCount} produit(s) ajouté(s).");
            if (count($this->results['errors']) > 0) $this->warning(count($this->results['errors']) . " erreur(s).");
            return;
        }

        if ($this->mode === 'import') {
            $content           = file_get_contents($this->csvFile->getRealPath());
            $this->results     = $service->importFromCsv($content, $commonData);
            $this->showResults = true;
            $this->lastCreatedCount = count($this->results['success']);

            if ($this->lastCreatedCount > 0) $this->success("{$this->lastCreatedCount} produit(s) importé(s).");
            if (count($this->results['errors']) > 0) $this->warning(count($this->results['errors']) . " erreur(s).");
            return;
        }
    }

    public function render()
    {
        $productModels = ProductModel::with('brand')
            ->where('is_active', true)
            ->where('is_serialized', true)
            ->when(!auth()->user()->isAdmin(), fn($q) => $q->where('category', '!=', 'sextoys'))
            ->orderBy('name')
            ->get()
            ->map(fn($m) => ['id' => $m->id, 'name' => $m->display_label]);

        $suppliers = Supplier::active()->orderBy('name')->get()
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name]);

        return view('livewire.products.create', [
            'productModels' => $productModels,
            'suppliers'     => $suppliers,
        ])->layout('layouts.app', ['title' => 'Ajouter des produits']);
    }
}
