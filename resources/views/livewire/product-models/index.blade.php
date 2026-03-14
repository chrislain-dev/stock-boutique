<div>
    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim-1 { animation: slideUp 0.35s cubic-bezier(.16,1,.3,1) 0.00s both; }
        .anim-2 { animation: slideUp 0.35s cubic-bezier(.16,1,.3,1) 0.05s both; }
        .anim-3 { animation: slideUp 0.35s cubic-bezier(.16,1,.3,1) 0.10s both; }
        .anim-4 { animation: slideUp 0.35s cubic-bezier(.16,1,.3,1) 0.15s both; }
        .anim-5 { animation: slideUp 0.35s cubic-bezier(.16,1,.3,1) 0.20s both; }
        .anim-6 { animation: slideUp 0.35s cubic-bezier(.16,1,.3,1) 0.25s both; }

        .cat-card {
            background: #fff;
            border: 1px solid #f4f4f5;
            border-radius: 14px;
            padding: 20px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .cat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,.08);
            border-color: var(--boutique-primary, #18181b);
        }
        .cat-card:hover .cat-icon {
            background: var(--boutique-primary, #18181b);
            color: #fff;
        }
        .cat-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: #f4f4f5;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s ease, color 0.2s ease;
            color: var(--boutique-primary, #18181b);
            flex-shrink: 0;
        }
        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
        }
        .stat-row + .stat-row {
            border-top: 1px solid #f9f9f9;
        }
    </style>

    {{-- Header --}}
    <div class="anim-1 flex items-end justify-between mb-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-1">Catalogue</p>
            <h1 class="text-3xl font-semibold tracking-tight text-zinc-900">Modèles de produits</h1>
            <p class="text-sm text-zinc-400 mt-1">
                {{ $selectedCategory
                    ? collect(\App\Enums\ProductCategory::cases())->firstWhere('value', $selectedCategory)?->label()
                    : 'Sélectionnez une catégorie' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin())
            <select wire:model.live="statsPeriod"
                class="text-xs font-medium text-zinc-600 bg-white border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:border-zinc-400 transition-colors">
                <option value="day">Aujourd'hui</option>
                <option value="week">Cette semaine</option>
                <option value="month">Ce mois</option>
                <option value="quarter">Ce trimestre</option>
                <option value="semester">Ce semestre</option>
                <option value="year">Cette année</option>
            </select>
            @endif
            @if($selectedCategory)
            <button wire:click="$set('selectedCategory', '')"
                class="flex items-center gap-1.5 text-xs font-medium text-zinc-500 hover:text-zinc-900 bg-white border border-zinc-200 rounded-lg px-3 py-2 transition-colors">
                <x-mary-icon name="o-arrow-left" class="w-3.5 h-3.5" />
                Retour
            </button>
            @endif
        </div>
    </div>

    {{-- ── STEP 1 — Catégories ─────────────────────────────── --}}
    @if(!$selectedCategory)
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($categories as $i => $cat)
        @php
            $stat = $categoryStats[$cat->value] ?? [];
            $anims = ['anim-1','anim-2','anim-3','anim-4','anim-5','anim-6'];
            $anim = $anims[$i % 6];
        @endphp
        <div class="{{ $anim }} cat-card" wire:click="$set('selectedCategory', '{{ $cat->value }}')">

            {{-- Icône + Titre --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="cat-icon">
                    <x-mary-icon name="o-{{ $cat->icon() }}" class="w-5 h-5" />
                </div>
                <div>
                    <p class="font-semibold text-sm text-zinc-900">{{ $cat->label() }}</p>
                    <p class="text-xs text-zinc-400">
                        {{ $stat['models_count'] ?? 0 }} modèle(s)
                    </p>
                </div>
            </div>

            {{-- Séparateur --}}
            <div class="border-t border-zinc-50 mb-3"></div>

            {{-- Badge stock --}}
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-zinc-400">En stock</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-lg
                    {{ ($stat['stock_count'] ?? 0) == 0 ? 'bg-zinc-100 text-zinc-400' : 'bg-emerald-50 text-emerald-700' }}">
                    {{ $stat['stock_count'] ?? 0 }} unité(s)
                </span>
            </div>

            {{-- Stats admin --}}
            @if(auth()->user()->isAdmin())
            <div>
                <div class="stat-row">
                    <span class="text-xs text-zinc-400">Investi</span>
                    <span class="text-xs font-semibold text-amber-600">
                        {{ number_format($stat['investment'] ?? 0, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
                <div class="stat-row">
                    <span class="text-xs text-zinc-400">CA période</span>
                    <span class="text-xs font-semibold text-blue-600">
                        {{ number_format($stat['revenue'] ?? 0, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
                <div class="stat-row">
                    <span class="text-xs text-zinc-400">Bénéfice</span>
                    <span class="text-xs font-semibold {{ ($stat['profit'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ number_format($stat['profit'] ?? 0, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
            </div>
            @else
            <div class="stat-row">
                <span class="text-xs text-zinc-400">Ventes période</span>
                <span class="text-xs font-semibold" style="color: var(--boutique-primary, #18181b);">
                    {{ $stat['sales_count'] ?? 0 }} vente(s)
                </span>
            </div>
            @endif

            {{-- Flèche --}}
            <div class="flex justify-end mt-3">
                <x-mary-icon name="o-arrow-right" class="w-3.5 h-3.5 text-zinc-300" />
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── STEP 2 — Table ──────────────────────────────────── --}}
    @else
    {{-- Toolbar --}}
    <div class="anim-2 flex items-center gap-3 mb-5 flex-wrap">
        <div class="relative">
            <x-mary-icon name="o-magnifying-glass" class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
                wire:model.live.debounce="search"
                type="text"
                placeholder="Rechercher un modèle..."
                class="pl-9 pr-4 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:border-zinc-400 transition-colors w-56"
            />
        </div>

        <select wire:model.live="filterBrand"
            class="text-sm text-zinc-600 bg-white border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:border-zinc-400 transition-colors">
            <option value="">Toutes marques</option>
            @foreach($brands as $brand)
            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
            @endforeach
        </select>

        @if(auth()->user()->isAdmin())
        <button wire:click="openCreateModal"
            class="ml-auto flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-md"
            style="background: var(--boutique-primary, #18181b);">
            <x-mary-icon name="o-plus" class="w-4 h-4" />
            Nouveau modèle
        </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="anim-3 bg-white rounded-xl border border-zinc-100 shadow-sm overflow-hidden">
        <x-mary-table
            :headers="$headers"
            :rows="$productModels"
            :sort-by="$sortBy"
            wire:model="sortBy"
            with-pagination
        >
            @scope('cell_full_name', $model)
            <div>
                <p class="text-sm font-medium text-zinc-900">{{ $model->full_name }}</p>
                @if($model->model_number)
                <p class="text-xs text-zinc-400 font-mono mt-0.5">{{ $model->model_number }}</p>
                @endif
            </div>
            @endscope

            @scope('cell_condition', $model)
            @if($model->condition)
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium
                    {{ $model->condition->value === 'sealed'      ? 'bg-emerald-50 text-emerald-700' :
                      ($model->condition->value === 'refurbished' ? 'bg-blue-50 text-blue-700'      : 'bg-zinc-100 text-zinc-600') }}">
                    {{ $model->condition->label() }}
                </span>
            @else
                <span class="text-zinc-300">—</span>
            @endif
            @endscope

            @scope('cell_category', $model)
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-zinc-100 text-zinc-600">
                {{ $model->category->label() }}
            </span>
            @endscope

            @scope('cell_stock', $model)
            @if($model->is_serialized)
                <span class="text-sm font-semibold text-zinc-900">{{ $model->available_stock }}</span>
                <span class="text-xs text-zinc-400 ml-1">unité(s)</span>
            @else
                <span class="text-sm font-semibold {{ $model->is_low_stock ? 'text-red-500' : 'text-zinc-900' }}">
                    {{ $model->quantity_stock }}
                </span>
                <span class="text-xs text-zinc-400 ml-1">/ min {{ $model->stock_minimum }}</span>
            @endif
            @endscope

            @scope('cell_is_active', $model)
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold
                {{ $model->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $model->is_active ? 'bg-emerald-500' : 'bg-zinc-400' }}"></span>
                {{ $model->is_active ? 'Actif' : 'Inactif' }}
            </span>
            @endscope

            @scope('actions', $model)
            <div class="flex items-center gap-1">
                @if(auth()->user()->isAdmin())
                <button wire:click="openEditModal({{ $model->id }})"
                    class="p-1.5 rounded-lg text-zinc-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                    title="Modifier">
                    <x-mary-icon name="o-pencil" class="w-3.5 h-3.5" />
                </button>
                <button wire:click="confirmDelete({{ $model->id }})"
                    class="p-1.5 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                    title="Supprimer">
                    <x-mary-icon name="o-trash" class="w-3.5 h-3.5" />
                </button>
                @endif
            </div>
            @endscope
        </x-mary-table>
    </div>
    @endif

    {{-- Modal création / édition --}}
    <x-mary-modal wire:model="showModal"
        :title="$editingId ? 'Modifier le modèle' : 'Nouveau modèle'"
        box-class="max-w-3xl"
    >
        <x-mary-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-mary-input label="Nom du modèle" wire:model="name" placeholder="Ex: iPhone 15 Pro" required />
                <x-mary-select label="Marque" wire:model="brand_id"
                    :options="$brands->map(fn($b) => ['id' => $b->id, 'name' => $b->name])"
                    placeholder="Choisir une marque" required />
                <x-mary-select label="Catégorie" wire:model.live="category"
                    :options="collect($categories)->map(fn($c) => ['id' => $c->value, 'name' => $c->label()])"
                    placeholder="Choisir une catégorie" required />
                @if(in_array($category, ['telephone', 'pc', 'tablet']))
                <x-mary-select label="Condition" wire:model="condition"
                    :options="[
                        ['id'=>'sealed','name'=>'Scellé (neuf)'],
                        ['id'=>'refurbished','name'=>'Reconditionné'],
                        ['id'=>'used','name'=>'Occasion'],
                    ]"
                    placeholder="Choisir la condition" required />
                @endif
                <x-mary-input label="Numéro de modèle" wire:model="model_number" placeholder="Ex: A2848" />
            </div>

            @if(in_array($category, ['telephone', 'pc', 'tablet']))
            <div class="flex items-center gap-2 my-4">
                <div class="flex-1 h-px bg-zinc-100"></div>
                <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Spécifications</span>
                <div class="flex-1 h-px bg-zinc-100"></div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-mary-input label="Couleur"       wire:model="color"      placeholder="Ex: Noir" />
                <x-mary-input label="RAM (GB)"      wire:model="ram_gb"     type="number" placeholder="8" />
                <x-mary-input label="Stockage (GB)" wire:model="storage_gb" type="number" placeholder="256" />
                @if(in_array($category, ['pc', 'tablet']))
                <x-mary-select label="Type stockage" wire:model="storage_type"
                    :options="[['id'=>'SSD','name'=>'SSD'],['id'=>'HDD','name'=>'HDD'],['id'=>'eMMC','name'=>'eMMC'],['id'=>'NVMe','name'=>'NVMe']]"
                    placeholder="Type" />
                @endif
            </div>
            @endif

            @if($category === 'telephone')
            <div class="grid grid-cols-3 gap-4 mt-2">
                <x-mary-select label="Réseau" wire:model="network"
                    :options="[['id'=>'5G','name'=>'5G'],['id'=>'4G','name'=>'4G'],['id'=>'3G','name'=>'3G']]"
                    placeholder="Réseau" />
                <x-mary-input label="Type SIM"     wire:model="sim_type"    placeholder="Nano / eSIM" />
                <x-mary-input label="Taille écran" wire:model="screen_size" placeholder='6.1"' />
            </div>
            @endif

            @if($category === 'pc')
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-2">
                <x-mary-input label="CPU"          wire:model="cpu"              placeholder="Intel i7-1355U" />
                <x-mary-input label="Génération"   wire:model="cpu_generation"   placeholder="13th Gen" />
                <x-mary-input label="GPU"          wire:model="gpu"              placeholder="RTX 4060 / Intégré" />
                <x-mary-input label="Taille écran" wire:model="screen_size_pc"   placeholder='15.6"' />
                <x-mary-select label="Résolution"  wire:model="screen_resolution"
                    :options="[['id'=>'FHD','name'=>'FHD'],['id'=>'QHD','name'=>'QHD'],['id'=>'4K','name'=>'4K']]"
                    placeholder="Résolution" />
                <x-mary-input label="OS"           wire:model="os"               placeholder="Windows 11" />
                <x-mary-input label="Batterie"     wire:model="battery"          placeholder="72Wh" />
                <x-mary-select label="Type PC"     wire:model="pc_type"
                    :options="[['id'=>'laptop','name'=>'Laptop'],['id'=>'desktop','name'=>'Desktop'],['id'=>'all_in_one','name'=>'All-in-One'],['id'=>'mini_pc','name'=>'Mini PC']]"
                    placeholder="Type" />
            </div>
            @endif

            @if($category === 'tablet')
            <div class="grid grid-cols-2 gap-4 mt-2">
                <x-mary-input label="Connectivité"   wire:model="connectivity"   placeholder="WiFi / WiFi+4G" />
                <x-mary-input label="Support stylet" wire:model="stylus_support" placeholder="Apple Pencil / S-Pen" />
            </div>
            @endif

            @if($category === 'accessory')
            <div class="flex items-center gap-2 my-4">
                <div class="flex-1 h-px bg-zinc-100"></div>
                <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Accessoire</span>
                <div class="flex-1 h-px bg-zinc-100"></div>
            </div>
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
            <div class="flex items-center gap-2 my-4">
                <div class="flex-1 h-px bg-zinc-100"></div>
                <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Prix de référence</span>
                <div class="flex-1 h-px bg-zinc-100"></div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <x-mary-input label="Prix d'achat"   wire:model="default_purchase_price" type="number" placeholder="0" :hint="config('boutique.devise')" />
                <x-mary-input label="Prix client"    wire:model="default_client_price"   type="number" placeholder="0" :hint="config('boutique.devise')" />
                <x-mary-input label="Prix revendeur" wire:model="default_reseller_price" type="number" placeholder="0" :hint="config('boutique.devise')" />
            </div>
            @endif

            <x-mary-textarea label="Description" wire:model="description" rows="2" class="mt-2" />
            <x-mary-toggle label="Modèle actif" wire:model="is_active" class="mt-2" />

            <x-slot:actions>
                <x-mary-button label="Annuler" wire:click="$set('showModal', false)" class="btn-ghost" />
                <x-mary-button label="Sauvegarder" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>

    {{-- Modal suppression --}}
    <x-mary-modal wire:model="showDeleteModal" title="Confirmer la suppression">
        <div class="flex items-start gap-4 py-2">
            <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center shrink-0">
                <x-mary-icon name="o-exclamation-triangle" class="w-5 h-5 text-red-500" />
            </div>
            <div>
                <p class="text-sm font-medium text-zinc-900">Supprimer ce modèle ?</p>
                <p class="text-sm text-zinc-400 mt-1">Cette action est irréversible. Les produits associés seront également affectés.</p>
            </div>
        </div>
        <x-slot:actions>
            <x-mary-button label="Annuler" wire:click="$set('showDeleteModal', false)" class="btn-ghost" />
            <x-mary-button label="Supprimer" wire:click="delete" class="btn-error" spinner="delete" />
        </x-slot:actions>
    </x-mary-modal>
</div>
