<div>
    <x-mary-header title="Revendeurs" subtitle="{{ $resellers->total() }} revendeur(s)" icon="o-users">
        <x-slot:actions>
            <x-mary-input
                wire:model.live.debounce="search"
                placeholder="Rechercher..."
                icon="o-magnifying-glass"
                class="input-sm w-48"
                clearable
            />
            <x-mary-select
                wire:model.live="filterDebt"
                :options="[
                    ['id' => 'all',       'name' => 'Tous'],
                    ['id' => 'with_debt', 'name' => 'Avec créances'],
                    ['id' => 'no_debt',   'name' => 'Sans créance'],
                ]"
                class="select-sm w-36"
            />
            <x-mary-button
                label="Nouveau revendeur"
                icon="o-plus"
                class="btn-primary btn-sm"
                wire:click="openCreateModal"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table
            :headers="$headers"
            :rows="$resellers"
            :sort-by="$sortBy"
            wire:model="sortBy"
            with-pagination
        >
            {{-- Solde dû --}}
            @scope('cell_solde_du', $reseller)
                @if($reseller->solde_du > 0)
                    <x-mary-badge
                        value="{{ number_format($reseller->solde_du, 0, ',', ' ') }} {{ config('boutique.devise') }}"
                        class="badge-error badge-sm"
                    />
                @else
                    <x-mary-badge value="Soldé" class="badge-success badge-sm" />
                @endif
            @endscope

            {{-- Statut --}}
            @scope('cell_is_active', $reseller)
                @if($reseller->is_active)
                    <x-mary-badge value="Actif" class="badge-success badge-sm" />
                @else
                    <x-mary-badge value="Inactif" class="badge-error badge-sm" />
                @endif
            @endscope

            {{-- Actions --}}
            @scope('actions', $reseller)
                <div class="flex gap-1">
                    <x-mary-button
                        icon="o-pencil"
                        class="btn-ghost btn-xs text-info"
                        wire:click="openEditModal({{ $reseller->id }})"
                        tooltip="Modifier"
                    />
                    @if(auth()->user()->isAdmin())
                    <x-mary-button
                        icon="o-trash"
                        class="btn-ghost btn-xs text-error"
                        wire:click="confirmDelete({{ $reseller->id }})"
                        tooltip="Supprimer"
                    />
                    @endif
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>

    {{-- Modal création / édition --}}
    <x-mary-modal wire:model="showModal"
        :title="$editingId ? 'Modifier le revendeur' : 'Nouveau revendeur'"
        box-class="max-w-2xl"
    >
        <x-mary-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-mary-input
                    label="Nom"
                    wire:model="name"
                    placeholder="Ex: Jean Dupont"
                    icon="o-user"
                    required
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
                label="Revendeur actif"
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
        <p class="text-gray-600">Êtes-vous sûr de vouloir supprimer ce revendeur ?</p>
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
