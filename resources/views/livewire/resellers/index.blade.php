<div x-data="resellers()" x-init="init()">

    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes rowIn {
            from { opacity: 0; transform: translateX(-5px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes badgePop {
            0%   { transform: scale(0.75); opacity: 0; }
            70%  { transform: scale(1.07); }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position: 400px 0; }
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes errorIn {
            from { opacity: 0; transform: translateY(-3px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .page-header { animation: slideUp .35s cubic-bezier(.22,1,.36,1) both; }
        .filter-bar  { animation: slideUp .35s cubic-bezier(.22,1,.36,1) .06s both; }
        .table-card  { animation: slideUp .35s cubic-bezier(.22,1,.36,1) .12s both; }

        .row-animate  { animation: rowIn .26s cubic-bezier(.22,1,.36,1) both; }
        .badge-pop    { animation: badgePop .22s cubic-bezier(.34,1.56,.64,1) both; }
        .error-msg    { animation: errorIn .18s ease both; }

        .shimmer-line {
            background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
            background-size: 400px 100%;
            animation: shimmer 1.3s infinite linear;
            border-radius: 6px;
        }

        tbody tr { transition: background-color .12s ease, box-shadow .12s ease; }
        tbody tr:hover { box-shadow: inset 3px 0 0 0 #d1d5db; }

        button:active:not(:disabled) { transform: scale(0.97); }
        input:focus, select:focus, textarea:focus { box-shadow: 0 0 0 3px rgba(24,24,27,.07); }

        .field-error input, .field-error textarea {
            border-color: #fca5a5 !important;
            background-color: #fff5f5 !important;
        }
        .field-error input:focus, .field-error textarea:focus {
            border-color: #f87171 !important;
            box-shadow: 0 0 0 3px rgba(239,68,68,.08) !important;
        }
        .field-ok input, .field-ok textarea { border-color: #bbf7d0 !important; }
    </style>

    {{-- ─── Header ───────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3 page-header">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Revendeurs</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $resellers->total() }} revendeur(s) enregistré(s)</p>
        </div>
        <button wire:click="openCreateModal"
                class="inline-flex items-center gap-2 h-9 px-4 rounded-xl bg-gray-900 text-white text-sm
                       hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md
                       transition-all duration-150">
            <x-heroicon-o-plus class="w-4 h-4"/>
            Nouveau revendeur
        </button>
    </div>

    {{-- ─── Barre de filtres ─────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-2xl px-4 py-3 mb-5 flex-wrap filter-bar
                transition-shadow duration-200 hover:shadow-sm">

        {{-- Recherche --}}
        <div class="relative flex-1 min-w-45" x-data="{ focused: false }">
            <x-heroicon-o-magnifying-glass
                class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none transition-colors duration-150"
                ::class="focused ? 'text-gray-600' : 'text-gray-400'"/>
            <svg wire:loading wire:target="search"
                 class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                 style="animation: spin .7s linear infinite" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
            <input wire:model.live.debounce="search"
                   placeholder="Nom, téléphone..."
                   @focus="focused = true" @blur="focused = false"
                   class="w-full h-8 pl-9 pr-8 text-sm border rounded-lg outline-none bg-transparent
                          transition-all duration-200 border-gray-200 focus:border-gray-400 focus:bg-white"/>
            @if($search)
            <button wire:click="$set('search', '')"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-600 transition-colors">
                <x-heroicon-o-x-mark class="w-3.5 h-3.5"/>
            </button>
            @endif
        </div>

        {{-- Filtre créances --}}
        <div class="flex gap-1.5">
            @foreach([
                ['id' => 'all',       'name' => 'Tous'],
                ['id' => 'with_debt', 'name' => 'Avec créances'],
                ['id' => 'no_debt',   'name' => 'Sans créance'],
            ] as $f)
            <button wire:click="$set('filterDebt', '{{ $f['id'] }}')"
                    class="h-8 px-3.5 rounded-lg text-sm transition-all border
                           {{ $filterDebt === $f['id']
                               ? 'bg-gray-900 text-white border-gray-900'
                               : 'bg-transparent text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                {{ $f['name'] }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- ─── Table ─────────────────────────────────────────────────────── --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden table-card">

        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <p class="text-sm font-medium">Liste des revendeurs</p>
            <div class="flex items-center gap-2">
                <span wire:loading wire:target="search, filterDebt, sortBy"
                      class="text-[11px] text-gray-400 flex items-center gap-1.5">
                    <svg class="w-3 h-3" style="animation: spin .7s linear infinite" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                    Filtrage…
                </span>
                <span wire:loading.remove wire:target="search, filterDebt, sortBy"
                      class="text-[11px] text-gray-400">
                    {{ $resellers->total() }} revendeur(s)
                </span>
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Revendeur</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Téléphone</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Adresse</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Créance</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Statut</th>
                    <th class="px-5 py-2.5 w-20"></th>
                </tr>
            </thead>
            <tbody>
                {{-- Skeleton --}}
                <tr wire:loading wire:target="search, filterDebt, sortBy">
                    <td colspan="6" class="px-5 py-0">
                        @for($s = 0; $s < 5; $s++)
                        <div class="flex items-center gap-4 py-3.5 border-b border-gray-50 last:border-none">
                            <div class="flex items-center gap-2 flex-1">
                                <div class="shimmer-line h-7 w-7 rounded-lg shrink-0"></div>
                                <div class="shimmer-line h-4 flex-1"></div>
                            </div>
                            <div class="shimmer-line h-4 w-28 shrink-0"></div>
                            <div class="shimmer-line h-4 w-32 shrink-0"></div>
                            <div class="shimmer-line h-5 w-20 shrink-0 rounded-md"></div>
                            <div class="shimmer-line h-5 w-12 shrink-0 rounded-md"></div>
                            <div class="shimmer-line h-4 w-12 shrink-0"></div>
                        </div>
                        @endfor
                    </td>
                </tr>

                @forelse($resellers as $i => $reseller)
                <tr class="border-b border-gray-100 last:border-none row-animate"
                    style="animation-delay: {{ min($i * 30, 240) }}ms">

                    {{-- Nom --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-violet-50 flex items-center justify-center text-[10px] font-bold text-violet-600 shrink-0">
                                {{ strtoupper(substr($reseller->name, 0, 2)) }}
                            </div>
                            <span class="text-sm font-medium text-gray-800">{{ $reseller->name }}</span>
                        </div>
                    </td>

                    {{-- Téléphone --}}
                    <td class="px-5 py-3.5">
                        <p class="text-sm text-gray-700">{{ $reseller->phone }}</p>
                        @if($reseller->phone_secondary)
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $reseller->phone_secondary }}</p>
                        @endif
                    </td>

                    {{-- Adresse --}}
                    <td class="px-5 py-3.5">
                        <span class="text-sm text-gray-600">{{ $reseller->address ?? '—' }}</span>
                    </td>

                    {{-- Solde dû --}}
                    <td class="px-5 py-3.5">
                        @if($reseller->solde_du > 0)
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium
                                     bg-red-50 text-red-700 ring-1 ring-red-100 badge-pop"
                              style="animation-delay: {{ min($i * 30 + 50, 290) }}ms">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                            {{ number_format($reseller->solde_du, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium
                                     bg-green-50 text-green-800 ring-1 ring-green-100 badge-pop"
                              style="animation-delay: {{ min($i * 30 + 50, 290) }}ms">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 shrink-0"></span>
                            Soldé
                        </span>
                        @endif
                    </td>

                    {{-- Statut --}}
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium ring-1 badge-pop
                                     {{ $reseller->is_active
                                         ? 'bg-green-50 text-green-800 ring-green-100'
                                         : 'bg-gray-100 text-gray-500 ring-gray-200' }}"
                              style="animation-delay: {{ min($i * 30 + 70, 310) }}ms">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $reseller->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                            {{ $reseller->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-1">
                            <button wire:click="openEditModal({{ $reseller->id }})"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                                           hover:bg-blue-50 hover:text-blue-600 transition-all duration-150"
                                    title="Modifier">
                                <x-heroicon-o-pencil class="w-3.5 h-3.5"/>
                            </button>
                            @if(auth()->user()->isAdmin())
                            <button wire:click="confirmDelete({{ $reseller->id }})"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                                           hover:bg-red-50 hover:text-red-500 transition-all duration-150"
                                    title="Supprimer">
                                <x-heroicon-o-trash class="w-3.5 h-3.5"/>
                            </button>
                            @endif
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <div x-data x-init="
                            $el.style.opacity=0; $el.style.transform='translateY(8px)';
                            requestAnimationFrame(() => {
                                $el.style.transition='opacity .3s ease, transform .3s ease';
                                $el.style.opacity=1; $el.style.transform='translateY(0)';
                            })">
                            <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                <x-heroicon-o-users class="w-5 h-5 text-gray-300"/>
                            </div>
                            <p class="text-sm text-gray-400 font-medium">Aucun revendeur trouvé</p>
                            @if($search || $filterDebt !== 'all')
                            <p class="text-xs text-gray-300 mt-1">Essayez de modifier vos filtres</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($resellers->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
            {{ $resellers->links() }}
        </div>
        @endif
    </div>


    {{-- ─── Modal création / édition ────────────────────────────────── --}}
    <div x-data="{ open: @entangle('showModal').live }"
         x-cloak
         @keydown.escape.window="$wire.set('showModal', false)">

        {{-- Backdrop --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/25 backdrop-blur-[3px]"
             wire:click="$set('showModal', false)">
        </div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">

            <div class="relative bg-white rounded-2xl shadow-2xl shadow-gray-200/80 border border-gray-100
                        w-full max-w-lg pointer-events-auto">

                {{-- Header modal --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">
                            {{ $editingId ? 'Modifier le revendeur' : 'Nouveau revendeur' }}
                        </h2>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $editingId ? 'Mettez à jour les informations' : 'Renseignez les informations du revendeur' }}
                        </p>
                    </div>
                    <button wire:click="$set('showModal', false)"
                            class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400
                                   hover:bg-gray-100 hover:text-gray-700 hover:rotate-90
                                   transition-all duration-200">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Nom --}}
                        <div class="md:col-span-2 {{ $errors->has('name') ? 'field-error' : ($name ? 'field-ok' : '') }}">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                Nom <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <x-heroicon-o-user class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                                <input wire:model="name" placeholder="Ex: Jean Dupont"
                                       class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                              hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                            </div>
                            @error('name')
                            <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                                <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Téléphone principal --}}
                        <div class="{{ $errors->has('phone') ? 'field-error' : ($phone ? 'field-ok' : '') }}">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                Téléphone principal <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <x-heroicon-o-phone class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                                <input wire:model="phone" placeholder="+229 01 XX XX XX"
                                       class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                              hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                            </div>
                            @error('phone')
                            <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                                <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Téléphone secondaire --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Téléphone secondaire</label>
                            <div class="relative">
                                <x-heroicon-o-phone class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                                <input wire:model="phone_secondary" placeholder="+229 01 XX XX XX"
                                       class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                              hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                            </div>
                        </div>

                        {{-- Adresse --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Adresse</label>
                            <div class="relative">
                                <x-heroicon-o-map-pin class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                                <input wire:model="address" placeholder="Adresse complète"
                                       class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                              hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes</label>
                            <textarea wire:model="notes" rows="3"
                                      placeholder="Informations supplémentaires..."
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                             hover:border-gray-300 focus:border-gray-400
                                             resize-none transition-all duration-150 leading-relaxed"></textarea>
                        </div>

                        {{-- Toggle actif --}}
                        <div class="md:col-span-2 flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Revendeur actif</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Désactivé = n'apparaît plus dans les ventes</p>
                            </div>
                            <button type="button"
                                    wire:click="$toggle('is_active')"
                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent
                                           transition-colors duration-200 ease-in-out focus:outline-none
                                           {{ $is_active ? 'bg-gray-900' : 'bg-gray-200' }}">
                                <span class="pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow
                                             transform transition duration-200 ease-in-out
                                             {{ $is_active ? 'translate-x-4' : 'translate-x-0' }}">
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                    <button wire:click="$set('showModal', false)"
                            class="h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600
                                   hover:bg-gray-50 hover:border-gray-300 transition-all duration-150">
                        Annuler
                    </button>
                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-wait"
                            class="h-9 px-4 rounded-xl bg-gray-900 text-white text-sm
                                   hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md
                                   disabled:opacity-75 disabled:cursor-wait disabled:translate-y-0
                                   transition-all duration-150 flex items-center gap-1.5">
                        <svg wire:loading wire:target="save"
                             class="w-3.5 h-3.5" style="animation: spin .7s linear infinite"
                             fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <x-heroicon-o-check wire:loading.remove wire:target="save" class="w-4 h-4"/>
                        Sauvegarder
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- ─── Modal suppression ─────────────────────────────────────────── --}}
    <div x-data="{ open: @entangle('showDeleteModal').live }"
         x-cloak
         @keydown.escape.window="$wire.set('showDeleteModal', false)">

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/25 backdrop-blur-[3px]"
             wire:click="$set('showDeleteModal', false)">
        </div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-3"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">

            <div class="relative bg-white rounded-2xl shadow-2xl shadow-gray-200/80 border border-gray-100
                        w-full max-w-sm pointer-events-auto">

                <div class="px-6 pt-6 pb-5 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-4">
                        <x-heroicon-o-trash class="w-5 h-5 text-red-500"/>
                    </div>
                    <h2 class="text-base font-semibold text-gray-900 mb-1">Supprimer ce revendeur ?</h2>
                    <p class="text-sm text-gray-500">Cette action est irréversible. Toutes les données associées seront perdues.</p>
                </div>

                <div class="flex items-center gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="flex-1 h-9 rounded-xl border border-gray-200 bg-white text-sm text-gray-600
                                   hover:bg-gray-50 transition-all duration-150">
                        Annuler
                    </button>
                    <button wire:click="delete"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-wait"
                            class="flex-1 h-9 rounded-xl bg-red-500 text-white text-sm
                                   hover:bg-red-600 hover:shadow-md hover:shadow-red-200/70
                                   disabled:opacity-75 disabled:cursor-wait
                                   transition-all duration-150 flex items-center justify-center gap-1.5">
                        <svg wire:loading wire:target="delete"
                             class="w-3.5 h-3.5" style="animation: spin .7s linear infinite"
                             fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <x-heroicon-o-trash wire:loading.remove wire:target="delete" class="w-3.5 h-3.5"/>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        function resellers() {
            return {
                init() {
                    Livewire.hook('morph.updated', () => {
                        setTimeout(() => {
                            document.querySelectorAll('tbody .row-animate').forEach((el, i) => {
                                el.style.animationName = 'none';
                                void el.offsetWidth;
                                el.style.animationName = '';
                                el.style.animationDelay = Math.min(i * 30, 240) + 'ms';
                            });
                        }, 50);
                    });
                }
            }
        }
    </script>
    @endscript
</div>
