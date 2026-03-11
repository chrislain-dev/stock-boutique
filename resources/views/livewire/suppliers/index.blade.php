<div>
    <x-mary-header title="Fournisseurs" subtitle="{{ $suppliers->total() }} fournisseur(s)" icon="o-truck">
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
                label="Nouveau fournisseur"
                icon="o-plus"
                class="btn-primary btn-sm"
                wire:click="openCreateModal"
            />
            @endif
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table
            :headers="$headers"
            :rows="$suppliers"
            :sort-by="$sortBy"
            wire:model="sortBy"
            with-pagination
        >
            @scope('cell_is_active', $supplier)
                @if($supplier->is_active)
                    <x-mary-badge value="Actif" class="badge-success badge-sm" />
                @else
                    <x-mary-badge value="Inactif" class="badge-error badge-sm" />
                @endif
            @endscope

            @scope('actions', $supplier)
                <div class="flex gap-1">
                    @if(auth()->user()->isAdmin())
                    <x-mary-button
                        icon="o-pencil"
                        class="btn-ghost btn-xs text-info"
                        wire:click="openEditModal({{ $supplier->id }})"
                        tooltip="Modifier"
                    />
                    <x-mary-button
                        icon="o-trash"
                        class="btn-ghost btn-xs text-error"
                        wire:click="confirmDelete({{ $supplier->id }})"
                        tooltip="Supprimer"
                    />
                    @endif
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>

    {{-- Modal création / édition --}}
    <x-mary-modal wire:model="showModal"
        :title="$editingId ? 'Modifier le fournisseur' : 'Nouveau fournisseur'"
        box-class="max-w-2xl"
    >
        <x-mary-form wire:submit="save">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-mary-input
                    label="Nom"
                    wire:model="name"
                    placeholder="Ex: TechImport Cotonou"
                    icon="o-building-office"
                    required
                />
                <x-mary-input
                    label="Pays"
                    wire:model="country"
                    placeholder="Ex: Bénin, Togo..."
                    icon="o-globe-alt"
                />
                <x-mary-input
                    label="Téléphone principal"
                    wire:model="phone"
                    placeholder="+229 01 XX XX XX"
                    icon="o-phone"
                    required
                />
                <x-mary-input
                    label="Téléphone secondaire"
                    wire:model="phone_secondary"
                    placeholder="+229 01 XX XX XX"
                    icon="o-phone"
                />
                <x-mary-input
                    label="Email"
                    wire:model="email"
                    type="email"
                    placeholder="contact@..."
                    icon="o-envelope"
                />
                <x-mary-input
                    label="Adresse"
                    wire:model="address"
                    placeholder="Adresse complète"
                    icon="o-map-pin"
                />
            </div>

            <x-mary-textarea
                label="Notes"
                wire:model="notes"
                placeholder="Informations supplémentaires..."
                rows="3"
            />

            <x-mary-toggle
                label="Fournisseur actif"
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

    {{-- Modal suppression --}}
    <x-mary-modal wire:model="showDeleteModal" title="Confirmer la suppression">
        <p class="text-gray-600">Êtes-vous sûr de vouloir supprimer ce fournisseur ?</p>
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
