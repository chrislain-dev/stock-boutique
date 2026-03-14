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

        .state-card {
            background: #fff;
            border: 1px solid #f4f4f5;
            border-radius: 14px;
            padding: 20px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .state-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,.08);
            border-color: var(--boutique-primary, #18181b);
        }
        .state-card:hover .state-arrow { opacity: 1; transform: translateX(2px); }
        .state-arrow {
            opacity: 0;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
        }
        .stat-row + .stat-row { border-top: 1px solid #f9f9f9; }
    </style>

    {{-- Header --}}
    <div class="anim-1 flex items-end justify-between mb-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-1">Catalogue</p>
            <h1 class="text-3xl font-semibold tracking-tight text-zinc-900">Produits</h1>
            <p class="text-sm text-zinc-400 mt-1">
                @if($selectedState)
                    {{ collect($states)->firstWhere('value', $selectedState)?->label() }}
                @else
                    {{ array_sum($stateCounts) }} produit(s) au total
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if($selectedState)
            <button wire:click="$set('selectedState', '')"
                class="flex items-center gap-1.5 text-xs font-medium text-zinc-500 hover:text-zinc-900 bg-white border border-zinc-200 rounded-lg px-3 py-2 transition-colors">
                <x-mary-icon name="o-arrow-left" class="w-3.5 h-3.5" />
                Retour
            </button>
            @endif
            @if(auth()->user()->isAdmin())
            <a href="{{ route('products.create') }}"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-md"
                style="background: var(--boutique-primary, #18181b);">
                <x-mary-icon name="o-plus" class="w-4 h-4" />
                Ajouter
            </a>
            @endif
        </div>
    </div>

    {{-- ── STEP 1 — Cartes état ────────────────────────────── --}}
    @if(!$selectedState)
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($states as $i => $state)
        @php
            $count = $stateCounts[$state->value] ?? 0;
            $stats = $stateStats[$state->value] ?? null;
            $anims = ['anim-1','anim-2','anim-3','anim-4','anim-5','anim-6'];
            $anim  = $anims[$i % 6];

            $colorMap = [
                'available'   => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
                'sold'        => ['bg' => 'bg-zinc-100',   'text' => 'text-zinc-600',    'dot' => 'bg-zinc-400'],
                'reserved'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'dot' => 'bg-blue-500'],
                'in_repair'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'dot' => 'bg-amber-500'],
                'defective'   => ['bg' => 'bg-red-50',     'text' => 'text-red-600',     'dot' => 'bg-red-500'],
                'transferred' => ['bg' => 'bg-purple-50',  'text' => 'text-purple-700',  'dot' => 'bg-purple-500'],
            ];
            $colors = $colorMap[$state->value] ?? ['bg' => 'bg-zinc-100', 'text' => 'text-zinc-600', 'dot' => 'bg-zinc-400'];
        @endphp
        <div class="{{ $anim }} state-card" wire:click="selectState('{{ $state->value }}')">

            {{-- Header --}}
            <div class="flex items-start justify-between mb-4">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $colors['bg'] }} {{ $colors['text'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $colors['dot'] }}"></span>
                    {{ $state->label() }}
                </span>
                <span class="text-3xl font-bold tracking-tight text-zinc-900">{{ $count }}</span>
            </div>

            {{-- Stats admin --}}
            @if(auth()->user()->isAdmin() && $stats)
            <div class="border-t border-zinc-50 pt-3 mt-1">
                <div class="stat-row">
                    <span class="text-xs text-zinc-400">Investi</span>
                    <span class="text-xs font-semibold text-amber-600">
                        {{ number_format($stats['total_investment'] ?? 0, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
                <div class="stat-row">
                    <span class="text-xs text-zinc-400">Valeur vente</span>
                    <span class="text-xs font-semibold text-blue-600">
                        {{ number_format($stats['total_client_value'] ?? 0, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
                <div class="stat-row">
                    <span class="text-xs text-zinc-400">Marge potentielle</span>
                    <span class="text-xs font-semibold {{ ($stats['total_margin'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ number_format($stats['total_margin'] ?? 0, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
            </div>
            @endif

            <div class="flex justify-end mt-3">
                <x-mary-icon name="o-arrow-right" class="w-3.5 h-3.5 text-zinc-300 state-arrow" />
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
                placeholder="Rechercher IMEI, modèle..."
                class="pl-9 pr-4 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:border-zinc-400 transition-colors w-60"
            />
        </div>

        <select wire:model.live="filterCategory"
            class="text-sm text-zinc-600 bg-white border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:border-zinc-400 transition-colors">
            <option value="">Catégorie</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterCondition"
            class="text-sm text-zinc-600 bg-white border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:border-zinc-400 transition-colors">
            <option value="">Condition</option>
            @foreach($conditions as $cond)
            <option value="{{ $cond->value }}">{{ $cond->label() }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterLocation"
            class="text-sm text-zinc-600 bg-white border border-zinc-200 rounded-lg px-3 py-2 focus:outline-none focus:border-zinc-400 transition-colors">
            <option value="">Localisation</option>
            @foreach($locations as $loc)
            <option value="{{ $loc->value }}">{{ $loc->label() }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="anim-3 bg-white rounded-xl border border-zinc-100 shadow-sm overflow-hidden">
        <x-mary-table
            :headers="$headers"
            :rows="$products"
            :sort-by="$sortBy"
            wire:model="sortBy"
            with-pagination
        >
            @scope('cell_model_name', $product)
            <div>
                <p class="text-sm font-medium text-zinc-900">{{ $product->productModel->display_label }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">{{ $product->productModel->brand->name }}</p>
            </div>
            @endscope

            @scope('cell_identifier', $product)
            <span class="font-mono text-xs text-zinc-500 bg-zinc-50 px-2 py-1 rounded-md">
                {{ $product->identifier }}
            </span>
            @endscope

            @scope('cell_condition', $product)
            @if($product->condition)
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium
                    {{ $product->condition->value === 'sealed'      ? 'bg-emerald-50 text-emerald-700' :
                      ($product->condition->value === 'refurbished' ? 'bg-blue-50 text-blue-700'      : 'bg-zinc-100 text-zinc-600') }}">
                    {{ $product->condition->label() }}
                </span>
            @else
                <span class="text-zinc-300">—</span>
            @endif
            @endscope

            @scope('cell_location', $product)
            <span class="text-xs text-zinc-500">{{ $product->location->label() }}</span>
            @endscope

            @scope('cell_client_price', $product)
            <span class="text-sm font-semibold text-zinc-900 font-mono">
                {{ number_format($product->client_price, 0, ',', ' ') }}
                <span class="text-xs font-normal text-zinc-400">{{ config('boutique.devise_symbole') }}</span>
            </span>
            @endscope

            @scope('actions', $product)
            <div class="flex items-center gap-1">
                <a href="{{ route('products.show', $product->id) }}"
                    class="p-1.5 rounded-lg text-zinc-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                    title="Voir">
                    <x-mary-icon name="o-eye" class="w-3.5 h-3.5" />
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('products.edit', $product->id) }}"
                    class="p-1.5 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                    title="Modifier">
                    <x-mary-icon name="o-pencil" class="w-3.5 h-3.5" />
                </a>
                <button wire:click="confirmDelete({{ $product->id }})"
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

    {{-- Modal suppression --}}
    <x-mary-modal wire:model="showDeleteModal" title="Confirmer la suppression">
        <div class="flex items-start gap-4 py-2">
            <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center shrink-0">
                <x-mary-icon name="o-exclamation-triangle" class="w-5 h-5 text-red-500" />
            </div>
            <div>
                <p class="text-sm font-medium text-zinc-900">Supprimer ce produit ?</p>
                <p class="text-sm text-zinc-400 mt-1">Cette action est irréversible.</p>
            </div>
        </div>
        <x-slot:actions>
            <x-mary-button label="Annuler" wire:click="$set('showDeleteModal', false)" class="btn-ghost" />
            <x-mary-button label="Supprimer" wire:click="delete" class="btn-error" spinner="delete" />
        </x-slot:actions>
    </x-mary-modal>
</div>
