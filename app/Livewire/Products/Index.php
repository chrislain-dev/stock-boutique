<?php

namespace App\Livewire\Products;

use App\Enums\ProductCategory;
use App\Enums\ProductCondition;
use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use WithPagination, Toast;

    public string $selectedState   = '';
    public string $search          = '';
    public string $filterCategory  = '';
    public string $filterCondition = '';
    public string $filterLocation  = '';
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public bool $showDeleteModal = false;
    public ?int $deletingId      = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }
    public function updatedFilterCondition(): void
    {
        $this->resetPage();
    }
    public function updatedFilterLocation(): void
    {
        $this->resetPage();
    }

    public function selectState(string $state): void
    {
        $this->selectedState = $state;
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $product = Product::findOrFail($this->deletingId);

        if ($product->state->value === 'sold') {
            $this->error('Impossible — ce produit a été vendu.');
            $this->showDeleteModal = false;
            return;
        }

        $product->delete();
        $this->success('Produit supprimé.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function headers(): array
    {
        return [
            ['key' => 'model_name',   'label' => 'Modèle',       'sortable' => false],
            ['key' => 'identifier',   'label' => 'IMEI / Série',  'sortable' => false],
            ['key' => 'condition',    'label' => 'Condition',     'sortable' => false],
            ['key' => 'location',     'label' => 'Localisation',  'sortable' => true],
            ['key' => 'client_price', 'label' => 'Prix client',   'sortable' => true],
        ];
    }

    public function render()
    {
        // Compteurs par état
        $stateCounts = Product::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')
            ->toArray();

        // Stats financières par état (admin)
        $stateStats = [];
        if (auth()->user()->isAdmin()) {
            $stateStats = Product::selectRaw('
                state,
                SUM(purchase_price) as total_investment,
                SUM(client_price) as total_client_value,
                SUM(client_price - purchase_price) as total_margin
            ')
                ->groupBy('state')
                ->get()
                ->keyBy('state')
                ->toArray();
        }

        $products = Product::with(['productModel.brand', 'supplier'])
            ->when(
                $this->selectedState,
                fn($q) =>
                $q->where('state', $this->selectedState)
            )
            ->when(
                $this->search,
                fn($q) =>
                $q->where('imei', 'like', '%' . $this->search . '%')
                    ->orWhere('serial_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas(
                        'productModel',
                        fn($q) =>
                        $q->where('name', 'like', '%' . $this->search . '%')
                    )
            )
            ->when(
                $this->filterCondition,
                fn($q) =>
                $q->whereHas(
                    'productModel',
                    fn($q) =>
                    $q->where('condition', $this->filterCondition)
                )
            )
            ->when(
                $this->filterLocation,
                fn($q) =>
                $q->where('location', $this->filterLocation)
            )
            ->when(
                $this->filterCategory,
                fn($q) =>
                $q->whereHas(
                    'productModel',
                    fn($q) =>
                    $q->where('category', $this->filterCategory)
                )
            )
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(20);

        return view('livewire.products.index', [
            'products'    => $products,
            'headers'     => $this->headers(),
            'states'      => ProductState::cases(),
            'stateCounts' => $stateCounts,
            'stateStats'  => $stateStats,
            'conditions'  => ProductCondition::cases(),
            'locations'   => ProductLocation::cases(),
            'categories'  => ProductCategory::cases(),
        ])->layout('layouts.app', ['title' => 'Produits']);
    }
}
