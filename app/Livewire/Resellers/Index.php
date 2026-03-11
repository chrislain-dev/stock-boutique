<?php

namespace App\Livewire\Resellers;

use App\Models\Reseller;
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

    // ─── Filtre ───────────────────────────────────────────────
    public string $filterDebt = 'all'; // all, with_debt, no_debt

    // ─── Champs formulaire ────────────────────────────────────
    public string $name            = '';
    public string $phone           = '';
    public string $phone_secondary = '';
    public string $address         = '';
    public string $notes           = '';
    public bool $is_active         = true;

    protected function rules(): array
    {
        return [
            'name'           => 'required|string|max:100',
            'phone'          => 'required|string|max:20|unique:resellers,phone,' . ($this->editingId ?? 'NULL'),
            'phone_secondary' => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
            'is_active'      => 'boolean',
        ];
    }

    protected $messages = [
        'name.required'  => 'Le nom est obligatoire.',
        'phone.required' => 'Le téléphone est obligatoire.',
        'phone.unique'   => 'Ce numéro est déjà utilisé.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    public function updatedFilterDebt(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'name', 'phone', 'phone_secondary', 'address', 'notes']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $reseller = Reseller::findOrFail($id);
        $this->editingId       = $reseller->id;
        $this->name            = $reseller->name;
        $this->phone           = $reseller->phone;
        $this->phone_secondary = $reseller->phone_secondary ?? '';
        $this->address         = $reseller->address ?? '';
        $this->notes           = $reseller->notes ?? '';
        $this->is_active       = $reseller->is_active;
        $this->showModal       = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'            => $this->name,
            'phone'           => $this->phone,
            'phone_secondary' => $this->phone_secondary ?: null,
            'address'         => $this->address ?: null,
            'notes'           => $this->notes ?: null,
            'is_active'       => $this->is_active,
        ];

        if ($this->editingId) {
            Reseller::findOrFail($this->editingId)->update($data);
            $this->success('Revendeur mis à jour.');
        } else {
            Reseller::create($data);
            $this->success('Revendeur créé.');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'phone', 'phone_secondary', 'address', 'notes']);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $reseller = Reseller::findOrFail($this->deletingId);

        if ($reseller->sales()->count() > 0) {
            $this->error('Impossible — ce revendeur a des ventes associées.');
            $this->showDeleteModal = false;
            return;
        }

        $reseller->delete();
        $this->success('Revendeur supprimé.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function headers(): array
    {
        return [
            ['key' => 'name',      'label' => 'Nom',        'sortable' => true],
            ['key' => 'phone',     'label' => 'Téléphone',  'sortable' => false],
            ['key' => 'solde_du',  'label' => 'Dû',         'sortable' => true],
            ['key' => 'is_active', 'label' => 'Statut',     'sortable' => true],
        ];
    }

    public function render()
    {
        $resellers = Reseller::when(
            $this->search,
            fn($q) =>
            $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('phone', 'like', '%' . $this->search . '%')
        )
            ->when($this->filterDebt === 'with_debt', fn($q) => $q->where('solde_du', '>', 0))
            ->when($this->filterDebt === 'no_debt',   fn($q) => $q->where('solde_du', 0))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(15);

        return view('livewire.resellers.index', [
            'resellers' => $resellers,
            'headers'   => $this->headers(),
        ])->layout('layouts.app', ['title' => 'Revendeurs']);
    }
}
