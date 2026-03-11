<div>
    {{-- Header --}}
    <x-mary-header title="Marques" subtitle="{{ $brands->total() }} marque(s)" icon="o-tag">
        <x-slot:actions>
            <x-mary-input
                wire:model.live.debounce="search"
                placeholder="Rechercher..."
                icon="o-magnifying-glass"
                class="input-sm w-48"
                clearable
            />
            @if(auth()->user()->isAdmin())
            <x-mary-button
                label="Nouvelle marque"
                icon="o-plus"
                class="btn-primary btn-sm"
                wire:click="openCreateModal"
            />
            @endif
        </x-slot:actions>
    </x-mary-header>

    {{-- Table --}}
    <x-mary-card>
        <x-mary-table
            :headers="$headers"
            :rows="$brands"
            :sort-by="$sortBy"
            wire:model="sortBy"
            with-pagination
        >
            {{-- Colonne statut --}}
            @scope('cell_is_active', $brand)
                @if($brand->is_active)
                    <x-mary-badge value="Actif" class="badge-success badge-sm" />
                @else
                    <x-mary-badge value="Inactif" class="badge-error badge-sm" />
                @endif
            @endscope

            {{-- Colonne date --}}
            @scope('cell_created_at', $brand)
                {{ $brand->created_at->format('d/m/Y') }}
            @endscope

            {{-- Actions --}}
            @scope('actions', $brand)
                <div class="flex gap-1">
                    @if(auth()->user()->isAdmin())
                    <x-mary-button
                        icon="o-pencil"
                        class="btn-ghost btn-xs text-info"
                        wire:click="openEditModal({{ $brand->id }})"
                        tooltip="Modifier"
                    />
                    <x-mary-button
                        icon="o-trash"
                        class="btn-ghost btn-xs text-error"
                        wire:click="confirmDelete({{ $brand->id }})"
                        tooltip="Supprimer"
                    />
                    @endif
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>

    {{-- Modal création / édition --}}
    <x-mary-modal wire:model="showModal" :title="$editingId ? 'Modifier la marque' : 'Nouvelle marque'">
        <x-mary-form wire:submit="save">

            <x-mary-input
                label="Nom de la marque"
                wire:model="name"
                placeholder="Ex: Apple, Samsung..."
                icon="o-tag"
                required
            />

            <x-mary-input
                label="URL du logo"
                wire:model="logo_url"
                placeholder="https://..."
                icon="o-photo"
            />

            <x-mary-toggle
                label="Marque active"
                wire:model="is_active"
            />

            <x-slot:actions>
                <x-mary-button
                    label="Annuler"
                    wire:click="$set('showModal', false)"
                    class="btn-ghost"
                />
                <x-mary-button
                    label="Sauvegarder"
                    type="submit"
                    class="btn-primary"
                    spinner="save"
                />
            </x-slot:actions>

        </x-mary-form>
    </x-mary-modal>

    {{-- Modal confirmation suppression --}}
    <x-mary-modal wire:model="showDeleteModal" title="Confirmer la suppression">
        <p class="text-gray-600">
            Êtes-vous sûr de vouloir supprimer cette marque ?
            Cette action est irréversible.
        </p>

        <x-slot:actions>
            <x-mary-button
                label="Annuler"
                wire:click="$set('showDeleteModal', false)"
                class="btn-ghost"
            />
            <x-mary-button
                label="Supprimer"
                wire:click="delete"
                class="btn-error"
                spinner="delete"
            />
        </x-slot:actions>
    </x-mary-modal>

</div>
