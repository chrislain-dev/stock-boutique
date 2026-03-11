<div>
    <x-mary-header
        title="Modèles de produits"
        icon="o-rectangle-stack"
        :subtitle="$selectedCategory ? collect(\App\Enums\ProductCategory::cases())->firstWhere('value', $selectedCategory)?->label() : 'Toutes catégories'"
    >
        <x-slot:actions>
            {{-- Filtre période (admin uniquement) --}}
            @if(auth()->user()->isAdmin())
            <x-mary-select
                wire:model.live="statsPeriod"
                :options="[
                    ['id' => 'day',      'name' => 'Aujourd\'hui'],
                    ['id' => 'week',     'name' => 'Cette semaine'],
                    ['id' => 'month',    'name' => 'Ce mois'],
                    ['id' => 'quarter',  'name' => 'Ce trimestre'],
                    ['id' => 'semester', 'name' => 'Ce semestre'],
                    ['id' => 'year',     'name' => 'Cette année'],
                ]"
                class="select-sm w-40"
            />
            @endif

            @if($selectedCategory)
            <x-mary-button
                label="Retour"
                icon="o-arrow-left"
                class="btn-ghost btn-sm"
                wire:click="$set('selectedCategory', '')"
            />
            @endif
        </x-slot:actions>
    </x-mary-header>

    {{-- ── STEP 1 — Choix catégorie ───────────────────────── --}}
    @if(!$selectedCategory)
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach($categories as $cat)
        @php $stat = $categoryStats[$cat->value] ?? []; @endphp
        <div
            wire:click="$set('selectedCategory', '{{ $cat->value }}')"
            class="cursor-pointer"
        >
            <x-mary-card class="hover:shadow-lg hover:border-primary transition-all duration-200 border-2 border-transparent">
                <div class="flex flex-col gap-3">

                    {{-- Icône + Nom --}}
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                            <x-mary-icon name="o-{{ $cat->icon() }}" class="w-6 h-6 text-primary" />
                        </div>
                        <div>
                            <p class="font-bold">{{ $cat->label() }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $stat['models_count'] ?? 0 }} modèle(s) •
                                {{ $stat['stock_count'] ?? 0 }} en stock
                            </p>
                        </div>
                    </div>

                    <div class="divider my-0"></div>

                    {{-- Stats admin --}}
                    @if(auth()->user()->isAdmin())
                    <div class="grid grid-cols-1 gap-1">
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Investi</span>
                            <span class="font-semibold text-warning">
                                {{ number_format($stat['investment'] ?? 0, 0, ',', ' ') }}
                                {{ config('boutique.devise_symbole') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">CA période</span>
                            <span class="font-semibold text-info">
                                {{ number_format($stat['revenue'] ?? 0, 0, ',', ' ') }}
                                {{ config('boutique.devise_symbole') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Bénéfice</span>
                            <span class="font-semibold {{ ($stat['profit'] ?? 0) >= 0 ? 'text-success' : 'text-error' }}">
                                {{ number_format($stat['profit'] ?? 0, 0, ',', ' ') }}
                                {{ config('boutique.devise_symbole') }}
                            </span>
                        </div>
                    </div>
                    @else
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Ventes période</span>
                        <span class="font-semibold text-primary">
                            {{ $stat['sales_count'] ?? 0 }} vente(s)
                        </span>
                    </div>
                    @endif

                </div>
            </x-mary-card>
        </div>
        @endforeach
    </div>

    {{-- ── STEP 2 — Table filtrée ──────────────────────────── --}}
    @else
    <div class="mb-4 flex flex-wrap gap-2">
        <x-mary-input
            wire:model.live.debounce="search"
            placeholder="Rechercher..."
            icon="o-magnifying-glass"
            class="input-sm w-48"
            clearable
        />
        <x-mary-select
            wire:model.live="filterBrand"
            :options="$brands->map(fn($b) => ['id' => $b->id, 'name' => $b->name])->prepend(['id' => '', 'name' => 'Toutes marques'])"
            class="select-sm w-36"
        />
        @if(auth()->user()->isAdmin())
        <x-mary-button
            label="Nouveau modèle"
            icon="o-plus"
            class="btn-primary btn-sm ml-auto"
            wire:click="openCreateModal"
        />
        @endif
    </div>

    <x-mary-card>
        <x-mary-table
            :headers="$headers"
            :rows="$productModels"
            :sort-by="$sortBy"
            wire:model="sortBy"
            with-pagination
        >
            @scope('cell_full_name', $model)
                <div>
                    <p class="font-medium text-sm">{{ $model->full_name }}</p>
                    @if($model->model_number)
                        <p class="text-xs text-gray-400">{{ $model->model_number }}</p>
                    @endif
                </div>
            @endscope

            @scope('cell_category', $model)
                <x-mary-badge value="{{ $model->category->label() }}" class="badge-outline badge-sm" />
            @endscope

            @scope('cell_stock', $model)
                @if($model->is_serialized)
                    <span class="text-sm font-medium">{{ $model->available_stock }}</span>
                    <span class="text-xs text-gray-400 ml-1">unité(s)</span>
                @else
                    <span class="text-sm font-medium {{ $model->is_low_stock ? 'text-error' : '' }}">
                        {{ $model->quantity_stock }}
                    </span>
                    <span class="text-xs text-gray-400 ml-1">/ min {{ $model->stock_minimum }}</span>
                @endif
            @endscope

            @scope('cell_is_active', $model)
                @if($model->is_active)
                    <x-mary-badge value="Actif" class="badge-success badge-sm" />
                @else
                    <x-mary-badge value="Inactif" class="badge-error badge-sm" />
                @endif
            @endscope

            @scope('actions', $model)
                <div class="flex gap-1">
                    @if(auth()->user()->isAdmin())
                    <x-mary-button
                        icon="o-pencil"
                        class="btn-ghost btn-xs text-info"
                        wire:click="openEditModal({{ $model->id }})"
                        tooltip="Modifier"
                    />
                    <x-mary-button
                        icon="o-trash"
                        class="btn-ghost btn-xs text-error"
                        wire:click="confirmDelete({{ $model->id }})"
                        tooltip="Supprimer"
                    />
                    @endif
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>
    @endif

    {{-- Modal création / édition --}}
    <x-mary-modal wire:model="showModal"
        :title="$editingId ? 'Modifier le modèle' : 'Nouveau modèle'"
        box-class="max-w-3xl"
    >
        <x-mary-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-mary-input label="Nom du modèle" wire:model="name" placeholder="Ex: iPhone 15 Pro" required />
                <x-mary-select
                    label="Marque"
                    wire:model="brand_id"
                    :options="$brands->map(fn($b) => ['id' => $b->id, 'name' => $b->name])"
                    placeholder="Choisir une marque"
                    required
                />
                <x-mary-select
                    label="Catégorie"
                    wire:model.live="category"
                    :options="collect($categories)->map(fn($c) => ['id' => $c->value, 'name' => $c->label()])"
                    placeholder="Choisir une catégorie"
                    required
                />
                <x-mary-input label="Numéro de modèle" wire:model="model_number" placeholder="Ex: A2848" />
            </div>

            @if(in_array($category, ['telephone', 'pc', 'tablet']))
            <div class="divider text-xs">Spécifications</div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-mary-input label="Couleur"       wire:model="color"      placeholder="Ex: Noir" />
                <x-mary-input label="RAM (GB)"      wire:model="ram_gb"     type="number" placeholder="8" />
                <x-mary-input label="Stockage (GB)" wire:model="storage_gb" type="number" placeholder="256" />
                @if(in_array($category, ['pc', 'tablet']))
                <x-mary-select label="Type stockage" wire:model="storage_type"
                    :options="[
                        ['id'=>'SSD','name'=>'SSD'],['id'=>'HDD','name'=>'HDD'],
                        ['id'=>'eMMC','name'=>'eMMC'],['id'=>'NVMe','name'=>'NVMe'],
                    ]"
                    placeholder="Type"
                />
                @endif
            </div>
            @endif

            @if($category === 'telephone')
            <div class="grid grid-cols-3 gap-4">
                <x-mary-select label="Réseau" wire:model="network"
                    :options="[['id'=>'5G','name'=>'5G'],['id'=>'4G','name'=>'4G'],['id'=>'3G','name'=>'3G']]"
                    placeholder="Réseau"
                />
                <x-mary-input label="Type SIM"     wire:model="sim_type"    placeholder="Nano / eSIM" />
                <x-mary-input label="Taille écran" wire:model="screen_size" placeholder='6.1"' />
            </div>
            @endif

            @if($category === 'pc')
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <x-mary-input label="CPU"          wire:model="cpu"              placeholder="Intel i7-1355U" />
                <x-mary-input label="Génération"   wire:model="cpu_generation"   placeholder="13th Gen" />
                <x-mary-input label="GPU"          wire:model="gpu"              placeholder="RTX 4060 / Intégré" />
                <x-mary-input label="Taille écran" wire:model="screen_size_pc"   placeholder='15.6"' />
                <x-mary-select label="Résolution"  wire:model="screen_resolution"
                    :options="[['id'=>'FHD','name'=>'FHD'],['id'=>'QHD','name'=>'QHD'],['id'=>'4K','name'=>'4K']]"
                    placeholder="Résolution"
                />
                <x-mary-input label="OS"           wire:model="os"               placeholder="Windows 11" />
                <x-mary-input label="Batterie"     wire:model="battery"          placeholder="72Wh" />
                <x-mary-select label="Type PC"     wire:model="pc_type"
                    :options="[
                        ['id'=>'laptop','name'=>'Laptop'],['id'=>'desktop','name'=>'Desktop'],
                        ['id'=>'all_in_one','name'=>'All-in-One'],['id'=>'mini_pc','name'=>'Mini PC'],
                    ]"
                    placeholder="Type"
                />
            </div>
            @endif

            @if($category === 'tablet')
            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="Connectivité"   wire:model="connectivity"   placeholder="WiFi / WiFi+4G" />
                <x-mary-input label="Support stylet" wire:model="stylus_support" placeholder="Apple Pencil / S-Pen" />
            </div>
            @endif

            @if($category === 'accessory')
            <div class="divider text-xs">Accessoire</div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <x-mary-input label="Type d'accessoire" wire:model="accessory_type" placeholder="Chargeur, Coque..." required />
                <x-mary-input label="Compatibilité"     wire:model="compatibility"  placeholder="iPhone 15, Universel..." />
                <x-mary-input label="Connecteur"        wire:model="connector_type" placeholder="USB-C, Lightning..." />
                <x-mary-input label="Couleur"           wire:model="color"          placeholder="Noir, Blanc..." />
                <x-mary-input label="Stock actuel"      wire:model="quantity_stock" type="number" />
                <x-mary-input label="Stock minimum"     wire:model="stock_minimum"  type="number" />
            </div>
            @endif

            @if(auth()->user()->isAdmin())
            <div class="divider text-xs">Prix de référence</div>
            <div class="grid grid-cols-3 gap-4">
                <x-mary-input label="Prix d'achat"   wire:model="default_purchase_price" type="number" placeholder="0" :hint="config('boutique.devise')" />
                <x-mary-input label="Prix client"    wire:model="default_client_price"   type="number" placeholder="0" :hint="config('boutique.devise')" />
                <x-mary-input label="Prix revendeur" wire:model="default_reseller_price" type="number" placeholder="0" :hint="config('boutique.devise')" />
            </div>
            @endif

            <x-mary-textarea label="Description" wire:model="description" rows="2" />
            <x-mary-toggle label="Modèle actif" wire:model="is_active" />

            <x-slot:actions>
                <x-mary-button label="Annuler" wire:click="$set('showModal', false)" class="btn-ghost" />
                <x-mary-button label="Sauvegarder" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>

    {{-- Modal suppression --}}
    <x-mary-modal wire:model="showDeleteModal" title="Confirmer la suppression">
        <p class="text-gray-600">Êtes-vous sûr de vouloir supprimer ce modèle ?</p>
        <x-slot:actions>
            <x-mary-button label="Annuler" wire:click="$set('showDeleteModal', false)" class="btn-ghost" />
            <x-mary-button label="Supprimer" wire:click="delete" class="btn-error" spinner="delete" />
        </x-slot:actions>
    </x-mary-modal>

</div>
