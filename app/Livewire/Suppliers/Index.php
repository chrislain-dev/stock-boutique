<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use WithPagination, Toast;

    public string $search = '';
    public array $sortBy  = ['column' => 'name', 'direction' => 'asc'];
    public bool $showModal       = false;
    public bool $showDeleteModal = false;
    public ?int $editingId  = null;
    public ?int $deletingId = null;

    // ─── Champs formulaire ────────────────────────────────────
    public string $name             = '';
    public string $phone            = '';
    public string $phone_secondary  = '';
    public string $email            = '';
    public string $address          = '';
    public string $country          = '';
    public string $notes            = '';
    public bool $is_active          = true;

    protected function rules(): array
    {
        return [
            'name'    => 'required|string|max:100',
            'phone'   => 'required|string|max:20|unique:suppliers,phone,' . ($this->editingId ?? 'NULL'),
            'phone_secondary' => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'notes'   => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'name.required'  => 'Le nom est obligatoire.',
        'phone.required' => 'Le téléphone est obligatoire.',
        'phone.unique'   => 'Ce numéro est déjà utilisé.',
        'email.email'    => 'L\'email n\'est pas valide.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'editingId',
            'name',
            'phone',
            'phone_secondary',
            'email',
            'address',
            'country',
            'notes'
        ]);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $this->editingId       = $supplier->id;
        $this->name            = $supplier->name;
        $this->phone           = $supplier->phone;
        $this->phone_secondary = $supplier->phone_secondary ?? '';
        $this->email           = $supplier->email ?? '';
        $this->address         = $supplier->address ?? '';
        $this->country         = $supplier->country ?? '';
        $this->notes           = $supplier->notes ?? '';
        $this->is_active       = $supplier->is_active;
        $this->showModal       = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'            => $this->name,
            'phone'           => $this->phone,
            'phone_secondary' => $this->phone_secondary ?: null,
            'email'           => $this->email ?: null,
            'address'         => $this->address ?: null,
            'country'         => $this->country ?: null,
            'notes'           => $this->notes ?: null,
            'is_active'       => $this->is_active,
        ];

        if ($this->editingId) {
            Supplier::findOrFail($this->editingId)->update($data);
            $this->success('Fournisseur mis à jour.');
        } else {
            Supplier::create($data);
            $this->success('Fournisseur créé.');
        }

        $this->showModal = false;
        $this->reset([
            'editingId',
            'name',
            'phone',
            'phone_secondary',
            'email',
            'address',
            'country',
            'notes'
        ]);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $supplier = Supplier::findOrFail($this->deletingId);

        if ($supplier->products()->count() > 0 || $supplier->purchases()->count() > 0) {
            $this->error('Impossible — ce fournisseur a des produits ou achats associés.');
            $this->showDeleteModal = false;
            return;
        }

        $supplier->delete();
        $this->success('Fournisseur supprimé.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function headers(): array
    {
        return [
            ['key' => 'name',       'label' => 'Nom',       'sortable' => true],
            ['key' => 'phone',      'label' => 'Téléphone',  'sortable' => false],
            ['key' => 'country',    'label' => 'Pays',       'sortable' => true],
            ['key' => 'is_active',  'label' => 'Statut',     'sortable' => true],
        ];
    }

    public function render()
    {
        $suppliers = Supplier::when(
            $this->search,
            fn($q) =>
            $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('phone', 'like', '%' . $this->search . '%')
        )
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(15);

        return view('livewire.suppliers.index', [
            'suppliers' => $suppliers,
            'headers'   => $this->headers(),
        ])->layout('layouts.app', ['title' => 'Fournisseurs']);
    }
}
