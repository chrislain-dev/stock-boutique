<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast, WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Formulaire
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = '';
    public bool $is_active = true;

    public function rules(): array
    {
        $passwordRules = $this->editingId
            ? 'nullable|min:8|confirmed'
            : 'required|min:8|confirmed';

        return [
            'name'     => 'required|min:2|max:100',
            'email'    => 'required|email|unique:users,email,' . ($this->editingId ?? 'NULL'),
            'password' => $passwordRules,
            'role'     => 'required|in:' . implode(',', array_column(UserRole::cases(), 'value')),
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermission('manage_users'), 403);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->editingId  = $id;
        $this->name       = $user->name;
        $this->email      = $user->email;
        $this->password   = '';
        $this->password_confirmation = '';
        $this->role       = $user->role->value;
        $this->is_active  = $user->is_active;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $isEditing = (bool) $this->editingId;

        $data = [
            'name'      => $this->name,
            'email'     => $this->email,
            'role'      => $this->role,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        if ($isEditing) {
            User::findOrFail($this->editingId)->update($data);
        } else {
            User::create($data);
        }

        $this->resetForm();
        $this->showModal = false;
        $this->success($isEditing ? 'Utilisateur modifié.' : 'Utilisateur créé.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $user = User::findOrFail($this->deletingId);

        if ($user->id === auth()->id()) {
            $this->error('Impossible de supprimer votre propre compte.');
            $this->showDeleteModal = false;
            return;
        }

        $user->delete();
        $this->showDeleteModal = false;
        $this->warning('Utilisateur supprimé.');
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            $this->error('Impossible de désactiver votre propre compte.');
            return;
        }

        $user->update(['is_active' => !$user->is_active]);
        $this->success($user->is_active ? 'Compte désactivé.' : 'Compte activé.');
    }

    private function resetForm(): void
    {
        $this->name                  = '';
        $this->email                 = '';
        $this->password              = '';
        $this->password_confirmation = '';
        $this->role                  = UserRole::VENDEUR->value;
        $this->is_active             = true;
        $this->editingId             = null;
        $this->resetErrorBag();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
            ->when(
                $this->search,
                fn($q) =>
                $q->where('name', 'ilike', "%{$this->search}%")
                    ->orWhere('email', 'ilike', "%{$this->search}%")
            )
            ->when(
                $this->roleFilter,
                fn($q) =>
                $q->where('role', $this->roleFilter)
            )
            ->orderBy('name')
            ->paginate(15);

        $roles = collect(UserRole::cases())->map(fn($r) => [
            'id'   => $r->value,
            'name' => $r->label(),
        ])->toArray();

        return view('livewire.users.index', [
            'users'         => $users,
            'roles'         => $roles,
            'activeCount'   => User::where('is_active', true)->count(),
            'inactiveCount' => User::where('is_active', false)->count(),
            'adminCount'    => User::where('role', \App\Enums\UserRole::ADMIN)->count(),
        ])->layout('layouts.app', ['title' => 'Utilisateurs']);
    }
}
