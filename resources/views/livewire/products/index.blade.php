<div>
    <x-mary-header title="Produits" icon="o-device-phone-mobile"
        :subtitle="$selectedState ? collect($states)->firstWhere('value', $selectedState)?->label() : (array_sum($stateCounts) . ' produit(s) au total')"
    >
        <x-slot:actions>
            @if($selectedState)
            <x-mary-button
                label="Retour"
                icon="o-arrow-left"
                class="btn-ghost btn-sm"
                wire:click="$set('selectedState', '')"
            />
            @endif
            @if(auth()->user()->isAdmin())
            <x-mary-button
                label="Ajouter"
                icon="o-plus"
                class="btn-primary btn-sm"
                link="{{ route('products.create') }}"
            />
            @endif
        </x-slot:actions>
    </x-mary-header>

    {{-- ── STEP 1 — Cartes par état ────────────────────────── --}}
    @if(!$selectedState)
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        @foreach($states as $state)
        @php
            $count = $stateCounts[$state->value] ?? 0;
            $stats = $stateStats[$state->value] ?? null;
        @endphp
        <div wire:click="selectState('{{ $state->value }}')" class="cursor-pointer">
            <x-mary-card class="hover:shadow-lg hover:border-primary border-2 border-transparent transition-all duration-200">
                <div class="flex flex-col gap-3">

                    {{-- Header carte --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-mary-badge
                                value="{{ $state->label() }}"
                                class="badge-{{ $state->color() }}"
                            />
                        </div>
                        <span class="text-3xl font-bold text-primary">{{ $count }}</span>
                    </div>

                    {{-- Stats admin --}}
                    @if(auth()->user()->isAdmin() && $stats)
                    <div class="divider my-0"></div>
                    <div class="grid grid-cols-1 gap-1">
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Investi</span>
                            <span class="font-semibold text-warning">
                                {{ number_format($stats['total_investment'] ?? 0, 0, ',', ' ') }}
                                {{ config('boutique.devise_symbole') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Valeur vente</span>
                            <span class="font-semibold text-info">
                                {{ number_format($stats['total_client_value'] ?? 0, 0, ',', ' ') }}
                                {{ config('boutique.devise_symbole') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Marge potentielle</span>
                            <span class="font-semibold {{ ($stats['total_margin'] ?? 0) >= 0 ? 'text-success' : 'text-error' }}">
                                {{ number_format($stats['total_margin'] ?? 0, 0, ',', ' ') }}
                                {{ config('boutique.devise_symbole') }}
                            </span>
                        </div>
                    </div>
                    @endif

                </div>
            </x-mary-card>
        </div>
        @endforeach
    </div>

    {{-- ── STEP 2 — Table filtrée ──────────────────────────── --}}
    @else
    <div class="mb-4 flex flex-wrap gap-2 items-center">
        <x-mary-input
            wire:model.live.debounce="search"
            placeholder="Rechercher IMEI, modèle..."
            icon="o-magnifying-glass"
            class="input-sm w-52"
            clearable
        />
        <x-mary-select
            wire:model.live="filterCategory"
            :options="collect($categories)->map(fn($c) => ['id' => $c->value, 'name' => $c->label()])->prepend(['id' => '', 'name' => 'Catégorie'])"
            class="select-sm w-36"
        />
        <x-mary-select
            wire:model.live="filterCondition"
            :options="collect($conditions)->map(fn($c) => ['id' => $c->value, 'name' => $c->label()])->prepend(['id' => '', 'name' => 'Condition'])"
            class="select-sm w-36"
        />
        <x-mary-select
            wire:model.live="filterLocation"
            :options="collect($locations)->map(fn($l) => ['id' => $l->value, 'name' => $l->label()])->prepend(['id' => '', 'name' => 'Localisation'])"
            class="select-sm w-40"
        />
    </div>

    <x-mary-card>
        <x-mary-table
            :headers="$headers"
            :rows="$products"
            :sort-by="$sortBy"
            wire:model="sortBy"
            with-pagination
        >
            @scope('cell_model_name', $product)
                <div>
                    <p class="font-medium text-sm">{{ $product->productModel->display_label }}</p>
                    <p class="text-xs text-gray-400">{{ $product->productModel->brand->name }}</p>
                </div>
            @endscope

            @scope('cell_identifier', $product)
                <span class="font-mono text-xs">{{ $product->identifier }}</span>
            @endscope

            @scope('cell_condition', $product)
                @if($product->condition)
                <x-mary-badge
                    value="{{ $product->condition->label() }}"
                    class="badge-sm badge-{{ $product->condition->color() }}"
                />
                @else
                <span class="text-xs text-gray-400">—</span>
                @endif
            @endscope

            @scope('cell_location', $product)
                <span class="text-xs">{{ $product->location->label() }}</span>
            @endscope

            @scope('cell_client_price', $product)
                <span class="font-medium text-sm">
                    {{ number_format($product->client_price, 0, ',', ' ') }}
                    {{ config('boutique.devise_symbole') }}
                </span>
            @endscope

            @scope('actions', $product)
                <div class="flex gap-1">
                    <x-mary-button
                        icon="o-eye"
                        class="btn-ghost btn-xs text-info"
                        link="{{ route('products.show', $product->id) }}"
                        tooltip="Détail"
                    />
                    @if(auth()->user()->isAdmin())
                    <x-mary-button
                        icon="o-pencil"
                        class="btn-ghost btn-xs text-warning"
                        link="{{ route('products.edit', $product->id) }}"
                        tooltip="Modifier"
                    />
                    <x-mary-button
                        icon="o-trash"
                        class="btn-ghost btn-xs text-error"
                        wire:click="confirmDelete({{ $product->id }})"
                        tooltip="Supprimer"
                    />
                    @endif
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>
    @endif

    {{-- Modal suppression --}}
    <x-mary-modal wire:model="showDeleteModal" title="Confirmer la suppression">
        <p class="text-gray-600">Êtes-vous sûr de vouloir supprimer ce produit ?</p>
        <x-slot:actions>
            <x-mary-button label="Annuler" wire:click="$set('showDeleteModal', false)" class="btn-ghost" />
            <x-mary-button label="Supprimer" wire:click="delete" class="btn-error" spinner="delete" />
        </x-slot:actions>
    </x-mary-modal>
</div>
