<div>
    <x-mary-header title="Utilisateurs" subtitle="Gestion des comptes" icon="o-users">
        <x-slot:actions>
            <x-mary-button
                label="Nouvel utilisateur"
                icon="o-plus"
                class="btn-primary btn-sm"
                wire:click="openCreate"
            />
        </x-slot:actions>
    </x-mary-header>

    {{-- Filtres --}}
    <div class="flex gap-3 mb-4">
        <x-mary-input
            wire:model.live.debounce="search"
            placeholder="Rechercher..."
            icon="o-magnifying-glass"
            class="flex-1"
            clearable
        />
        <x-mary-select
            wire:model.live="roleFilter"
            :options="[['id'=>'','name'=>'Tous les rôles'], ['id'=>'admin','name'=>'Admin'], ['id'=>'vendeur','name'=>'Vendeur']]"
            option-value="id"
            option-label="name"
            class="w-40"
        />
    </div>

    {{-- Table --}}
    <x-mary-card>
        <x-mary-table :headers="[
            ['key'=>'name',       'label'=>'Nom'],
            ['key'=>'email',      'label'=>'Email'],
            ['key'=>'role',       'label'=>'Rôle'],
            ['key'=>'status',     'label'=>'Statut'],
            ['key'=>'created_at', 'label'=>'Créé le'],
            ['key'=>'actions',    'label'=>''],
        ]" :rows="$users" with-pagination>

            @scope('cell_role', $user)
                <x-mary-badge
                    value="{{ $user->role->label() }}"
                    class="badge-sm {{ $user->role === \App\Enums\UserRole::ADMIN ? 'badge-primary' : 'badge-ghost' }}"
                />
            @endscope

            @scope('cell_status', $user)
                <x-mary-badge
                    value="{{ $user->is_active ? 'Actif' : 'Inactif' }}"
                    class="badge-sm {{ $user->is_active ? 'badge-success' : 'badge-error' }}"
                />
            @endscope

            @scope('cell_created_at', $user)
                {{ $user->created_at->format('d/m/Y') }}
            @endscope

            @scope('cell_actions', $user)
                <div class="flex gap-2 justify-end">
                    <x-mary-button
                        icon="{{ $user->is_active ? 'o-lock-closed' : 'o-lock-open' }}"
                        class="btn-ghost btn-xs"
                        wire:click="toggleActive({{ $user->id }})"
                        tooltip="{{ $user->is_active ? 'Désactiver' : 'Activer' }}"
                    />
                    <x-mary-button
                        icon="o-pencil"
                        class="btn-ghost btn-xs"
                        wire:click="openEdit({{ $user->id }})"
                        tooltip="Modifier"
                    />
                    @if($user->id !== auth()->id())
                    <x-mary-button
                        icon="o-trash"
                        class="btn-ghost btn-xs text-error"
                        wire:click="confirmDelete({{ $user->id }})"
                        tooltip="Supprimer"
                    />
                    @endif
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>

    {{-- Modal formulaire --}}
    <x-mary-modal wire:model="showModal" :title="$editingId ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur'">
        <div class="space-y-4">
            <x-mary-input label="Nom" wire:model="name" placeholder="Prénom Nom" icon="o-user" />
            <x-mary-input label="Email" wire:model="email" type="email" placeholder="email@example.com" icon="o-envelope" />
            <x-mary-select
                label="Rôle"
                wire:model="role"
                :options="$roles"
                option-value="id"
                option-label="name"
                icon="o-shield-check"
            />
            <x-mary-input
                label="{{ $editingId ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe' }}"
                wire:model="password"
                type="password"
                icon="o-key"
            />
            <x-mary-input
                label="Confirmer le mot de passe"
                wire:model="password_confirmation"
                type="password"
                icon="o-key"
            />
            @if($editingId)
            <x-mary-toggle label="Compte actif" wire:model="is_active" />
            @endif
        </div>
        <x-slot:actions>
            <x-mary-button label="Annuler" wire:click="$set('showModal', false)" class="btn-ghost" />
            <x-mary-button label="Enregistrer" wire:click="save" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Modal confirmation suppression --}}
    <x-mary-modal wire:model="showDeleteModal" title="Confirmer la suppression">
        <p class="text-gray-600">Cette action est irréversible. Confirmer ?</p>
        <x-slot:actions>
            <x-mary-button label="Annuler" wire:click="$set('showDeleteModal', false)" class="btn-ghost" />
            <x-mary-button label="Supprimer" wire:click="delete" class="btn-error" />
        </x-slot:actions>
    </x-mary-modal>
</div>
