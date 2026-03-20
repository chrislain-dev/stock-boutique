<?php

namespace App\Livewire\Brands;

use App\Models\Brand;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Mary\Traits\Toast;

class Index extends Component
{
    use WithPagination, Toast, WithFileUploads;

    public string $viewMode = 'grid';

    // ─── Recherche & tri ──────────────────────────────────────
    public string $search = '';
    public array $sortBy  = ['column' => 'name', 'direction' => 'asc'];

    // ─── Modal ────────────────────────────────────────────────
    public bool $showModal = false;
    public bool $showDeleteModal = false;

    // ─── Formulaire ───────────────────────────────────────────
    public ?int $editingId = null;
    public string $name      = '';
    public $logo             = null; // fichier temporaire Livewire
    public ?string $existingLogoPath = null; // logo déjà stocké
    public bool $is_active   = true;

    // ─── Suppression ─────────────────────────────────────────
    public ?int $deletingId = null;

    protected function rules(): array
    {
        return [
            'name'  => 'required|string|max:100|unique:brands,name,' . ($this->editingId ?? 'NULL'),
            'logo'  => 'nullable|image|max:2048', // 2 Mo max
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'Le nom de la marque est obligatoire.',
        'name.unique'   => 'Cette marque existe déjà.',
        'logo.image'    => 'Le fichier doit être une image.',
        'logo.max'      => 'L\'image ne doit pas dépasser 2 Mo.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // ─── Ouvrir modal création ────────────────────────────────
    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'name', 'logo', 'existingLogoPath']);
        $this->is_active = true;
        $this->showModal = true;
    }

    // ─── Ouvrir modal édition ─────────────────────────────────
    public function openEditModal(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $this->editingId        = $brand->id;
        $this->name             = $brand->name;
        $this->existingLogoPath = $brand->logo_path;
        $this->logo             = null;
        $this->is_active        = $brand->is_active;
        $this->showModal        = true;
    }

    // ─── Supprimer le logo existant ───────────────────────────
    public function removeLogo(): void
    {
        if ($this->existingLogoPath) {
            Storage::disk('public')->delete($this->existingLogoPath);
            $this->existingLogoPath = null;

            if ($this->editingId) {
                Brand::findOrFail($this->editingId)->update(['logo_path' => null]);
            }
        }
    }

    // ─── Sauvegarder ─────────────────────────────────────────
    public function save(): void
    {
        $this->validate();

        $logoPath = $this->existingLogoPath; // garde l'ancien par défaut

        if ($this->logo) {
            // Supprimer l'ancien logo si édition
            if ($this->existingLogoPath) {
                Storage::disk('public')->delete($this->existingLogoPath);
            }
            $logoPath = $this->logo->store('brands/logos', 'public');
        }

        $data = [
            'name'      => $this->name,
            'logo_path' => $logoPath,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            Brand::findOrFail($this->editingId)->update($data);
            $this->success('Marque mise à jour avec succès.');
        } else {
            Brand::create($data);
            $this->success('Marque créée avec succès.');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'logo', 'existingLogoPath']);
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

        // Supprimer le fichier logo associé
        if ($brand->logo_path) {
            Storage::disk('public')->delete($brand->logo_path);
        }

        $brand->delete();
        $this->success('Marque supprimée.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function headers(): array
    {
        return [
            ['key' => 'name',          'label' => 'Marque',   'sortable' => true],
            ['key' => 'products_count', 'label' => 'Produits',  'sortable' => false],
            ['key' => 'is_active',     'label' => 'Statut',   'sortable' => true],
            ['key' => 'created_at',    'label' => 'Créée le', 'sortable' => true],
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
