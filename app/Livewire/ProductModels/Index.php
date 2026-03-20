<?php

namespace App\Livewire\ProductModels;

use App\Enums\ProductCategory;
use App\Models\Brand;
use App\Models\ProductModel;
use App\Services\ProductModelStatsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use WithPagination, Toast;

    public string $search    = '';
    public string $filterCategory = '';
    public string $filterBrand    = '';
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public bool $showModal       = false;
    public bool $showDeleteModal = false;
    public ?int $editingId  = null;
    public ?int $deletingId = null;

    // ─── Champs formulaire ────────────────────────────────────
    public string $name         = '';
    public ?int $brand_id       = null;
    public string $model_number = '';
    public string $category     = '';
    public string $condition = '';
    public string $description  = '';
    public string $image_url    = '';
    public bool $is_serialized  = true;
    public bool $is_active      = true;

    // Specs communes
    public string $color        = '';
    public ?int $ram_gb         = null;
    public ?int $storage_gb     = null;
    public string $storage_type = '';

    // Specs téléphone
    public string $network      = '';
    public string $sim_type     = '';
    public string $screen_size  = '';

    // Specs PC
    public string $cpu              = '';
    public string $cpu_generation   = '';
    public string $gpu              = '';
    public string $screen_size_pc   = '';
    public string $screen_resolution = '';
    public string $os               = '';
    public string $battery          = '';
    public string $pc_type          = '';

    // Specs tablette
    public string $connectivity   = '';
    public string $stylus_support = '';

    // Specs accessoire
    public string $accessory_type = '';
    public string $compatibility  = '';
    public string $connector_type = '';

    // Stock accessoires
    public int $quantity_stock  = 0;
    public int $stock_minimum   = 0;

    // Prix
    public ?int $default_purchase_price  = null;
    public ?int $default_client_price    = null;
    public ?int $default_reseller_price  = null;

    protected function rules(): array
    {
        $rules = [
            'name'      => 'required|string|max:255',
            'brand_id'  => 'required|exists:brands,id',
            'category' => 'required|in:telephone,pc,tablet,accessory' . (auth()->user()->isAdmin() ? ',sextoys' : ''),
            'is_active' => 'boolean',
            'default_purchase_price'  => 'nullable|numeric|min:0',
            'default_client_price'    => 'nullable|numeric|min:0',
            'default_reseller_price'  => 'nullable|numeric|min:0',
        ];

        if (in_array($this->category, ['telephone', 'pc', 'tablet'])) {
            $rules['ram_gb']     = 'nullable|integer|min:1';
            $rules['storage_gb'] = 'nullable|integer|min:1';
        }

        if ($this->category === 'accessory') {
            $rules['accessory_type']  = 'required|string|max:100';
            $rules['quantity_stock']  = 'required|integer|min:0';
            $rules['stock_minimum']   = 'required|integer|min:0';
        }

        if (in_array($this->category, ['telephone', 'pc', 'tablet'])) {
            $rules['condition'] = 'required|in:sealed,refurbished,used';  // ← AJOUTER
            $rules['ram_gb']     = 'nullable|integer|min:1';
            $rules['storage_gb'] = 'nullable|integer|min:1';
        }

        return $rules;
    }

    protected $messages = [
        'name.required'         => 'Le nom est obligatoire.',
        'brand_id.required'     => 'La marque est obligatoire.',
        'category.required'     => 'La catégorie est obligatoire.',
        'condition.required' => 'La condition est obligatoire.',
        'accessory_type.required' => 'Le type d\'accessoire est obligatoire.',
    ];

    public string $selectedCategory = '';
    public string $statsPeriod      = 'month';
    public array $categoryStats     = [];
    public array $counts            = [];

    public function mount(ProductModelStatsService $service): void
    {
        $this->loadStats($service);
    }

    public function updatedStatsPeriod(): void
    {
        $this->loadStats(app(ProductModelStatsService::class));
    }

    private function loadStats(ProductModelStatsService $service): void
    {
        $this->categoryStats = $service->getCategoryStats($this->statsPeriod);

        $this->counts = ProductModel::selectRaw('category, count(*) as total')
            ->where('is_active', true)
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }
    public function updatedFilterBrand(): void
    {
        $this->resetPage();
    }

    // ─── Quand la catégorie change, ajuster is_serialized ─────
    public function updatedCategory(): void
    {
        $this->is_serialized = $this->category !== 'accessory';
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $model = ProductModel::findOrFail($id);
        $this->editingId              = $model->id;
        $this->name                   = $model->name;
        $this->brand_id               = $model->brand_id;
        $this->model_number           = $model->model_number ?? '';
        $this->category               = $model->category->value;
        $this->description            = $model->description ?? '';
        $this->image_url              = $model->image_url ?? '';
        $this->is_serialized          = $model->is_serialized;
        $this->is_active              = $model->is_active;
        $this->condition = $model->condition?->value ?? '';
        $this->color                  = $model->color ?? '';
        $this->ram_gb                 = $model->ram_gb;
        $this->storage_gb             = $model->storage_gb;
        $this->storage_type           = $model->storage_type ?? '';
        $this->network                = $model->network ?? '';
        $this->sim_type               = $model->sim_type ?? '';
        $this->screen_size            = $model->screen_size ?? '';
        $this->cpu                    = $model->cpu ?? '';
        $this->cpu_generation         = $model->cpu_generation ?? '';
        $this->gpu                    = $model->gpu ?? '';
        $this->screen_size_pc         = $model->screen_size_pc ?? '';
        $this->screen_resolution      = $model->screen_resolution ?? '';
        $this->os                     = $model->os ?? '';
        $this->battery                = $model->battery ?? '';
        $this->pc_type                = $model->pc_type ?? '';
        $this->connectivity           = $model->connectivity ?? '';
        $this->stylus_support         = $model->stylus_support ?? '';
        $this->accessory_type         = $model->accessory_type ?? '';
        $this->compatibility          = $model->compatibility ?? '';
        $this->connector_type         = $model->connector_type ?? '';
        $this->quantity_stock         = $model->quantity_stock;
        $this->stock_minimum          = $model->stock_minimum;
        $this->default_purchase_price = $model->default_purchase_price;
        $this->default_client_price   = $model->default_client_price;
        $this->default_reseller_price = $model->default_reseller_price;
        $this->showModal              = true;
    }

    public function save(): void
    {
        $this->validate();

        $isEditing = (bool) $this->editingId;

        $data = [
            'name'                    => $this->name,
            'brand_id'                => $this->brand_id,
            'model_number'            => $this->model_number ?: null,
            'category'                => $this->category,
            'condition'               => $this->condition ?: null,
            'description'             => $this->description ?: null,
            'image_url'               => $this->image_url ?: null,
            'is_serialized'           => $this->is_serialized,
            'is_active'               => $this->is_active,
            'color'                   => $this->color ?: null,
            'ram_gb'                  => $this->ram_gb,
            'storage_gb'              => $this->storage_gb,
            'storage_type'            => $this->storage_type ?: null,
            'network'                 => $this->network ?: null,
            'sim_type'                => $this->sim_type ?: null,
            'screen_size'             => $this->screen_size ?: null,
            'cpu'                     => $this->cpu ?: null,
            'cpu_generation'          => $this->cpu_generation ?: null,
            'gpu'                     => $this->gpu ?: null,
            'screen_size_pc'          => $this->screen_size_pc ?: null,
            'screen_resolution'       => $this->screen_resolution ?: null,
            'os'                      => $this->os ?: null,
            'battery'                 => $this->battery ?: null,
            'pc_type'                 => $this->pc_type ?: null,
            'connectivity'            => $this->connectivity ?: null,
            'stylus_support'          => $this->stylus_support ?: null,
            'accessory_type'          => $this->accessory_type ?: null,
            'compatibility'           => $this->compatibility ?: null,
            'connector_type'          => $this->connector_type ?: null,
            'quantity_stock'          => $this->category === 'accessory' ? $this->quantity_stock : 0,
            'stock_minimum'           => $this->stock_minimum,
            'default_purchase_price'  => $this->default_purchase_price,
            'default_client_price'    => $this->default_client_price,
            'default_reseller_price'  => $this->default_reseller_price,
        ];

        if ($this->editingId) {
            $model = ProductModel::findOrFail($this->editingId);

            // Historique des prix si changement
            if (
                $model->default_purchase_price != $this->default_purchase_price ||
                $model->default_client_price   != $this->default_client_price ||
                $model->default_reseller_price != $this->default_reseller_price
            ) {
                \App\Models\PriceHistory::create([
                    'product_model_id'    => $model->id,
                    'old_purchase_price'  => $model->default_purchase_price,
                    'old_client_price'    => $model->default_client_price,
                    'old_reseller_price'  => $model->default_reseller_price,
                    'new_purchase_price'  => $this->default_purchase_price,
                    'new_client_price'    => $this->default_client_price,
                    'new_reseller_price'  => $this->default_reseller_price,
                    'reason'              => 'Modification via interface',
                    'created_by'          => Auth::id(),
                ]);
            }

            $model->update($data);
        } else {
            ProductModel::create($data);
        }

        $this->showModal = false;
        $this->resetForm();

        $isEditing
            ? $this->success('Modèle mis à jour.')
            : $this->success('Modèle créé.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $model = ProductModel::findOrFail($this->deletingId);

        if ($model->products()->count() > 0) {
            $this->error('Impossible — ce modèle a des produits associés.');
            $this->showDeleteModal = false;
            return;
        }

        $model->delete();
        $this->success('Modèle supprimé.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'brand_id',
            'model_number',
            'category',
            'condition',
            'description',
            'image_url',
            'color',
            'ram_gb',
            'storage_gb',
            'storage_type',
            'network',
            'sim_type',
            'screen_size',
            'cpu',
            'cpu_generation',
            'gpu',
            'screen_size_pc',
            'screen_resolution',
            'os',
            'battery',
            'pc_type',
            'connectivity',
            'stylus_support',
            'accessory_type',
            'compatibility',
            'connector_type',
            'default_purchase_price',
            'default_client_price',
            'default_reseller_price',
        ]);
        $this->is_serialized  = true;
        $this->is_active      = true;
        $this->quantity_stock = 0;
        $this->stock_minimum  = 0;
    }

    public function headers(): array
    {
        return [
            ['key' => 'full_name',  'label' => 'Modèle',      'sortable' => false],
            ['key' => 'condition',  'label' => 'Condition',   'sortable' => true],
            ['key' => 'category',   'label' => 'Catégorie',   'sortable' => true],
            ['key' => 'stock',      'label' => 'Stock',        'sortable' => false],
            ['key' => 'is_active',  'label' => 'Statut',       'sortable' => true],
        ];
    }

    public function render()
    {
        $brands = Brand::active()->orderBy('name')->get();

        $categories = collect(ProductCategory::cases())
            ->when(!auth()->user()->isAdmin(), fn($c) => $c->filter(
                fn($cat) => $cat !== ProductCategory::SEXTOYS
            ))->values();

        $productModels = ProductModel::with('brand')
            ->when(!auth()->user()->isAdmin(), fn($q) => $q->where('category', '!=', 'sextoys'))
            ->when(
                $this->search,
                fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
            )
            ->when(
                $this->selectedCategory,
                fn($q) =>    // ← AJOUTER
                $q->where('category', $this->selectedCategory)
            )
            ->when(
                $this->filterBrand,
                fn($q) =>
                $q->where('brand_id', $this->filterBrand)
            )
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(15);

        $counts = ProductModel::selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        return view('livewire.product-models.index', [
            'productModels' => $productModels,
            'headers'       => $this->headers(),
            'brands'        => $brands,
            'categories'    => $categories,
            'counts'        => $counts,
        ])->layout('layouts.app', ['title' => 'Modèles de produits']);
    }
}
