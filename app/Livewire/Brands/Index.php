<?php

namespace App\Livewire\Brands;

use App\Models\Brand;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use WithPagination, Toast;

    public string $viewMode = 'grid';

    // ─── Recherche & tri ──────────────────────────────────────
    public string $search = '';
    public array $sortBy  = ['column' => 'name', 'direction' => 'asc'];

    // ─── Modal ────────────────────────────────────────────────
    public bool $showModal = false;
    public bool $showDeleteModal = false;

    // ─── Formulaire ───────────────────────────────────────────
    public ?int $editingId = null;
    public string $name     = '';
    public ?string $logo_url = null;
    public bool $is_active  = true;

    // ─── Suppression ─────────────────────────────────────────
    public ?int $deletingId = null;

    protected function rules(): array
    {
        return [
            'name'      => 'required|string|max:100|unique:brands,name,' . ($this->editingId ?? 'NULL'),
            'logo_url'  => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'Le nom de la marque est obligatoire.',
        'name.unique'   => 'Cette marque existe déjà.',
        'logo_url.url'  => 'L\'URL du logo n\'est pas valide.',
    ];

    // ─── Reset pagination si recherche ────────────────────────
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // ─── Ouvrir modal création ────────────────────────────────
    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'name', 'logo_url']);
        $this->is_active = true;
        $this->showModal = true;
    }

    // ─── Ouvrir modal édition ─────────────────────────────────
    public function openEditModal(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $this->editingId = $brand->id;
        $this->name      = $brand->name;
        $this->logo_url  = $brand->logo_url;
        $this->is_active = $brand->is_active;
        $this->showModal = true;
    }

    // ─── Sauvegarder ─────────────────────────────────────────
    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            Brand::findOrFail($this->editingId)->update([
                'name'      => $this->name,
                'logo_url'  => $this->logo_url,
                'is_active' => $this->is_active,
            ]);
            $this->success('Marque mise à jour avec succès.');
        } else {
            Brand::create([
                'name'      => $this->name,
                'logo_url'  => $this->logo_url,
                'is_active' => $this->is_active,
            ]);
            $this->success('Marque créée avec succès.');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'logo_url']);
    }

    // ─── Confirmer suppression ────────────────────────────────
    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    // ─── Supprimer ────────────────────────────────────────────
    public function delete(): void
    {
        $brand = Brand::findOrFail($this->deletingId);

        if ($brand->productModels()->count() > 0) {
            $this->error('Impossible de supprimer — cette marque a des modèles associés.');
            $this->showDeleteModal = false;
            return;
        }

        $brand->delete();
        $this->success('Marque supprimée.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    // ─── Headers table ────────────────────────────────────────
    public function headers(): array
    {
        return [
            ['key' => 'name',       'label' => 'Marque',    'sortable' => true],
            ['key' => 'models_count', 'label' => 'Modèles',  'sortable' => false],
            ['key' => 'is_active',  'label' => 'Statut',    'sortable' => true],
            ['key' => 'created_at', 'label' => 'Créée le',  'sortable' => true],
        ];
    }

    public function render()
    {
        $brands = Brand::withCount([
            'products as products_count' => fn($q) => $q->where('state', 'available')
        ])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(15);

        return view('livewire.brands.index', [
            'brands'        => $brands,
            'headers'       => $this->headers(),
            'activeCount'   => Brand::where('is_active', true)->count(),
            'inactiveCount' => Brand::where('is_active', false)->count(),
        ])->layout('layouts.app', ['title' => 'Marques']);
    }
}
