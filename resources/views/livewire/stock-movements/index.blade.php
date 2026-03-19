<div x-data="stockMovements()" x-init="init()">

    {{-- ─── Styles & keyframes ───────────────────────────────────────── --}}
    <style>
        /* Entrée page */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Lignes table en cascade */
        @keyframes rowIn {
            from { opacity: 0; transform: translateX(-6px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* Shimmer skeleton */
        @keyframes shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position: 400px 0; }
        }

        /* Badge pop */
        @keyframes badgePop {
            0%   { transform: scale(0.7); opacity: 0; }
            70%  { transform: scale(1.08); }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Shake erreur */
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%     { transform: translateX(-5px); }
            40%     { transform: translateX(5px); }
            60%     { transform: translateX(-3px); }
            80%     { transform: translateX(3px); }
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Animations de page ── */
        .page-header { animation: slideUp .35s cubic-bezier(.22,1,.36,1) both; }
        .filter-bar  { animation: slideUp .35s cubic-bezier(.22,1,.36,1) .06s both; }
        .table-card  { animation: slideUp .35s cubic-bezier(.22,1,.36,1) .12s both; }

        /* ── Lignes ── */
        .row-animate {
            animation: rowIn .28s cubic-bezier(.22,1,.36,1) both;
        }

        /* ── Badge ── */
        .badge-animate { animation: badgePop .25s cubic-bezier(.34,1.56,.64,1) both; }

        /* ── Erreur ── */
        .error-shake { animation: shake .35s ease both; }

        /* ── Skeleton ── */
        .shimmer-line {
            background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
            background-size: 400px 100%;
            animation: shimmer 1.3s infinite linear;
            border-radius: 6px;
        }

        /* ── Hover ligne: bordure latérale glissante ── */
        tbody tr {
            transition: background-color .12s ease, box-shadow .12s ease;
        }
        tbody tr:hover {
            box-shadow: inset 3px 0 0 0 #d1d5db;
        }

        /* ── Location badges ── */
        .loc-badge {
            transition: background-color .15s ease, color .15s ease, border-color .15s ease;
        }
        .loc-badge:hover {
            background-color: #e5e7eb;
            color: #374151;
            border-color: #d1d5db;
        }

        /* ── Avatar hover ── */
        .avatar-chip { transition: transform .15s ease; }
        .avatar-chip:hover { transform: scale(1.06); }

        /* ── Press global ── */
        button:active:not(:disabled) { transform: scale(0.97); }

        /* ── Focus ring ── */
        input:focus, select:focus, textarea:focus {
            box-shadow: 0 0 0 3px rgba(24,24,27,.07);
        }
    </style>

    {{-- ─── Header ───────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3 page-header">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Mouvements de stock</h1>
            <p class="text-sm text-gray-400 mt-0.5">Historique complet des entrées et sorties</p>
        </div>
        @if(auth()->user()->hasPermission('adjust_stock'))
        <button wire:click="openAdjustModal"
                class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-amber-500 bg-amber-500 text-white text-sm
                       hover:bg-amber-600 hover:shadow-md hover:shadow-amber-200 hover:-translate-y-0.5
                       transition-all duration-150">
            <x-heroicon-o-wrench-screwdriver class="w-4 h-4"/>
            Ajustement manuel
        </button>
        @endif
    </div>

    {{-- ─── Barre de filtres ─────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-2xl px-4 py-3 mb-5 flex-wrap filter-bar
                transition-shadow duration-200 hover:shadow-sm">

        {{-- Recherche --}}
        <div class="relative flex-1 min-w-[180px]" x-data="{ focused: false }">
            <x-heroicon-o-magnifying-glass
                class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none transition-colors duration-150"
                ::class="focused ? 'text-gray-600' : 'text-gray-400'"/>

            {{-- Spinner chargement --}}
            <svg wire:loading wire:target="search"
                 class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                 style="animation: spin .7s linear infinite"
                 fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>

            <input wire:model.live.debounce="search"
                   placeholder="IMEI, modèle..."
                   @focus="focused = true" @blur="focused = false"
                   class="w-full h-8 pl-9 pr-8 text-sm border rounded-lg outline-none bg-transparent
                          transition-all duration-200
                          border-gray-200 focus:border-gray-400 focus:bg-white"/>

            @if($search)
            <button wire:click="$set('search', '')"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-50"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-600 transition-colors duration-150">
                <x-heroicon-o-x-mark class="w-3.5 h-3.5"/>
            </button>
            @endif
        </div>

        {{-- Type --}}
        <select wire:model.live="typeFilter"
                class="h-8 px-3 text-sm border border-gray-200 rounded-lg outline-none bg-white text-gray-600
                       hover:border-gray-300 focus:border-gray-400
                       transition-all duration-150 cursor-pointer">
            <option value="">Tous les types</option>
            @foreach($types as $type)
                @if($type['id'] !== '')
                <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                @endif
            @endforeach
        </select>

        {{-- Dates --}}
        <div class="flex items-center gap-2">
            <input wire:model.live="dateFrom" type="date"
                   class="h-8 px-3 text-sm border border-gray-200 rounded-lg outline-none bg-white
                          hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
            <span class="text-gray-300 select-none">→</span>
            <input wire:model.live="dateTo" type="date"
                   class="h-8 px-3 text-sm border border-gray-200 rounded-lg outline-none bg-white
                          hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
        </div>

        {{-- Reset --}}
        @if($search || $typeFilter || $dateFrom || $dateTo)
        <button wire:click="$set('search', ''); $set('typeFilter', ''); $set('dateFrom', ''); $set('dateTo', '')"
                class="h-8 px-3 text-xs text-gray-400 hover:text-gray-700 border border-gray-200 rounded-lg
                       hover:bg-gray-50 hover:border-gray-300
                       transition-all duration-150 whitespace-nowrap">
            ✕ Réinitialiser
        </button>
        @endif
    </div>

    {{-- ─── Table ─────────────────────────────────────────────────────── --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden table-card">

        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <p class="text-sm font-medium">Historique</p>
            <div class="flex items-center gap-2">
                <span wire:loading wire:target="search, typeFilter, dateFrom, dateTo"
                      class="text-[11px] text-gray-400 flex items-center gap-1.5">
                    <svg class="w-3 h-3" style="animation: spin .7s linear infinite"
                         fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                    Filtrage…
                </span>
                <span wire:loading.remove wire:target="search, typeFilter, dateFrom, dateTo"
                      class="text-[11px] text-gray-400">
                    {{ $movements->total() }} mouvement(s)
                </span>
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5 w-32">Type</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Produit</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Mouvement</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5 max-w-50">Notes</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Par</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Date</th>
                </tr>
            </thead>
            <tbody>
                {{-- Skeleton pendant filtrage --}}
                <tr wire:loading wire:target="search, typeFilter, dateFrom, dateTo">
                    <td colspan="6" class="px-5 py-0">
                        @for($s = 0; $s < 6; $s++)
                        <div class="flex items-center gap-4 py-3.5 border-b border-gray-50 last:border-none">
                            <div class="shimmer-line h-5 w-20 shrink-0"></div>
                            <div class="shimmer-line h-4 flex-1"></div>
                            <div class="shimmer-line h-4 w-24 shrink-0"></div>
                            <div class="shimmer-line h-4 w-32 shrink-0"></div>
                            <div class="shimmer-line h-4 w-20 shrink-0"></div>
                            <div class="shimmer-line h-4 w-16 shrink-0"></div>
                        </div>
                        @endfor
                    </td>
                </tr>

                {{-- Données --}}
                @forelse($movements as $i => $movement)
                <tr class="border-b border-gray-100 last:border-none row-animate"
                    style="animation-delay: {{ min($i * 30, 240) }}ms">

                    {{-- Badge type --}}
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium badge-animate
                                     {{ $movement->type->isPositive()
                                         ? 'bg-green-50 text-green-800 ring-1 ring-green-100'
                                         : 'bg-red-50 text-red-700 ring-1 ring-red-100' }}"
                              style="animation-delay: {{ min($i * 30 + 50, 290) }}ms">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $movement->type->isPositive() ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            {{ $movement->type->label() }}
                        </span>
                    </td>

                    {{-- Produit --}}
                    <td class="px-5 py-3.5">
                        @if($movement->product)
                            <p class="text-sm font-medium text-gray-800 leading-snug">
                                {{ $movement->product->productModel->display_label }}
                            </p>
                            <p class="text-[11px] font-mono text-gray-400 mt-0.5 tracking-wide">
                                {{ $movement->product->imei ?? $movement->product->serial_number ?? '—' }}
                            </p>
                        @elseif($movement->productModel)
                            <p class="text-sm text-gray-700">{{ $movement->productModel->display_label }}</p>
                        @else
                            <span class="text-gray-300 text-sm">—</span>
                        @endif
                    </td>

                    {{-- Mouvement --}}
                    <td class="px-5 py-3.5">
                        @if($movement->location_from || $movement->location_to)
                        <div class="flex items-center gap-1.5">
                            @if($movement->location_from)
                                <span class="loc-badge bg-gray-100 text-gray-500 px-2 py-0.5 rounded-md text-[11px] border border-gray-200 cursor-default">
                                    {{ $movement->location_from }}
                                </span>
                            @endif
                            @if($movement->location_from && $movement->location_to)
                                <x-heroicon-o-arrow-right class="w-3 h-3 text-gray-300 shrink-0"/>
                            @endif
                            @if($movement->location_to)
                                <span class="loc-badge bg-gray-100 text-gray-500 px-2 py-0.5 rounded-md text-[11px] border border-gray-200 cursor-default">
                                    {{ $movement->location_to }}
                                </span>
                            @endif
                        </div>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Notes --}}
                    <td class="px-5 py-3.5 max-w-[200px]">
                        <span class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ $movement->notes ?? '—' }}</span>
                    </td>

                    {{-- Créé par --}}
                    <td class="px-5 py-3.5">
                        @if($movement->createdBy)
                        <div class="flex items-center gap-2 avatar-chip w-fit">
                            <div class="w-6 h-6 rounded-lg bg-gray-900 flex items-center justify-center text-[9px] font-bold text-white shrink-0 ring-2 ring-white">
                                {{ strtoupper(substr($movement->createdBy->name, 0, 2)) }}
                            </div>
                            <span class="text-sm text-gray-700 whitespace-nowrap">{{ $movement->createdBy->name }}</span>
                        </div>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Date --}}
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <p class="text-sm text-gray-700 font-medium">{{ $movement->created_at->format('d/m/Y') }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5 tabular-nums">{{ $movement->created_at->format('H:i') }}</p>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <div x-data
                             x-init="
                                $el.style.opacity = 0;
                                $el.style.transform = 'translateY(8px)';
                                requestAnimationFrame(() => {
                                    $el.style.transition = 'opacity .3s ease, transform .3s ease';
                                    $el.style.opacity = 1;
                                    $el.style.transform = 'translateY(0)';
                                })">
                            <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                <x-heroicon-o-arrows-right-left class="w-5 h-5 text-gray-300"/>
                            </div>
                            <p class="text-sm text-gray-400 font-medium">Aucun mouvement trouvé</p>
                            @if($search || $typeFilter || $dateFrom || $dateTo)
                            <p class="text-xs text-gray-300 mt-1">Essayez de modifier vos filtres</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($movements->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
            {{ $movements->links() }}
        </div>
        @endif
    </div>


    {{-- ─── Modal ajustement ─────────────────────────────────────────── --}}
    @if(auth()->user()->hasPermission('adjust_stock'))
    <div x-data="{ open: @entangle('showAdjustModal').live }"
         x-cloak
         @keydown.escape.window="$wire.set('showAdjustModal', false)">

        {{-- Backdrop --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/25 backdrop-blur-[3px]"
             wire:click="$set('showAdjustModal', false)">
        </div>

        {{-- Panel --}}
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

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Ajustement manuel</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Enregistrer un mouvement hors vente</p>
                    </div>
                    <button wire:click="$set('showAdjustModal', false)"
                            class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400
                                   hover:bg-gray-100 hover:text-gray-700 hover:rotate-90
                                   transition-all duration-200">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5 space-y-4">

                    {{-- Produit --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Produit concerné</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <x-heroicon-o-qr-code class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                                <input wire:model="adjust_imei"
                                       placeholder="IMEI ou numéro de série"
                                       wire:keydown.enter="searchAdjustProduct"
                                       class="w-full h-9 pl-9 pr-3 text-sm border rounded-xl outline-none
                                              transition-all duration-200
                                              {{ $adjust_error ? 'border-red-300 bg-red-50/40 focus:border-red-400' : 'border-gray-200 focus:border-gray-400' }}"/>
                            </div>
                            <button wire:click="searchAdjustProduct"
                                    wire:loading.attr="disabled"
                                    class="h-9 px-4 rounded-xl bg-gray-900 text-white text-sm
                                           hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md
                                           disabled:opacity-50 disabled:cursor-not-allowed
                                           transition-all duration-150 flex items-center gap-1.5">
                                <svg wire:loading wire:target="searchAdjustProduct"
                                     class="w-3.5 h-3.5" style="animation: spin .7s linear infinite"
                                     fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                                </svg>
                                <x-heroicon-o-magnifying-glass wire:loading.remove wire:target="searchAdjustProduct" class="w-3.5 h-3.5"/>
                                Chercher
                            </button>
                        </div>

                        @if($adjust_error)
                        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1 error-shake">
                            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                            {{ $adjust_error }}
                        </p>
                        @endif

                        @if($adjust_product_id)
                            @php $p = \App\Models\Product::with('productModel')->find($adjust_product_id); @endphp
                            @if($p)
                            <div class="mt-2 flex items-center gap-2 p-2.5 bg-green-50 border border-green-100 rounded-xl"
                                 x-data
                                 x-init="
                                    $el.style.opacity = 0; $el.style.transform = 'translateY(4px)';
                                    requestAnimationFrame(() => {
                                        $el.style.transition = 'opacity .2s ease, transform .2s ease';
                                        $el.style.opacity = 1; $el.style.transform = 'translateY(0)';
                                    })">
                                <x-heroicon-o-check-circle class="w-4 h-4 text-green-600 shrink-0"/>
                                <span class="text-sm font-medium text-green-800">{{ $p->productModel->display_label }}</span>
                                <span class="text-green-500 font-mono text-[11px] ml-auto">{{ $p->imei ?? $p->serial_number ?? '' }}</span>
                            </div>
                            @endif
                        @endif
                    </div>

                    {{-- Type --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Type de mouvement <span class="text-red-400">*</span>
                        </label>
                        <select wire:model="adjust_type"
                                class="w-full h-9 px-3 text-sm border border-gray-200 rounded-xl outline-none
                                       bg-white hover:border-gray-300 focus:border-gray-400
                                       transition-all duration-150 cursor-pointer">
                            @foreach($adjustTypes as $type)
                            <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Localisation --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">De</label>
                            <select wire:model="adjust_location_from"
                                    class="w-full h-9 px-3 text-sm border border-gray-200 rounded-xl outline-none
                                           bg-white text-gray-600 hover:border-gray-300 focus:border-gray-400
                                           transition-all duration-150 cursor-pointer">
                                <option value="">Optionnel</option>
                                @foreach($locations as $loc)
                                <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Vers</label>
                            <select wire:model="adjust_location_to"
                                    class="w-full h-9 px-3 text-sm border border-gray-200 rounded-xl outline-none
                                           bg-white text-gray-600 hover:border-gray-300 focus:border-gray-400
                                           transition-all duration-150 cursor-pointer">
                                <option value="">Optionnel</option>
                                @foreach($locations as $loc)
                                <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Motif / Notes <span class="text-red-400">*</span>
                        </label>
                        <textarea wire:model="adjust_notes"
                                  rows="3"
                                  placeholder="Ex: Produit perdu, transfert vers dépôt, retour fournisseur..."
                                  class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl outline-none
                                         hover:border-gray-300 focus:border-gray-400
                                         resize-none transition-all duration-150 leading-relaxed"></textarea>
                        <p class="text-[11px] text-gray-400 mt-1">Obligatoire pour la traçabilité</p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                    <button wire:click="$set('showAdjustModal', false)"
                            class="h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600
                                   hover:bg-gray-50 hover:border-gray-300
                                   transition-all duration-150">
                        Annuler
                    </button>
                    <button wire:click="saveAdjustment"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-wait"
                            class="h-9 px-4 rounded-xl bg-amber-500 text-white text-sm
                                   hover:bg-amber-600 hover:shadow-md hover:shadow-amber-200/70 hover:-translate-y-0.5
                                   disabled:opacity-75 disabled:cursor-wait
                                   transition-all duration-150 flex items-center gap-1.5">
                        <svg wire:loading wire:target="saveAdjustment"
                             class="w-3.5 h-3.5" style="animation: spin .7s linear infinite"
                             fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <x-heroicon-o-check wire:loading.remove wire:target="saveAdjustment" class="w-4 h-4"/>
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ─── Script ────────────────────────────────────────────────────── --}}
    @script
    <script>
        function stockMovements() {
            return {
                init() {
                    // Ré-anime les lignes après chaque update Livewire
                    Livewire.hook('morph.updated', () => {
                        this.$nextTick(() => {
                            document.querySelectorAll('tbody .row-animate').forEach((el, i) => {
                                el.style.animationName = 'none';
                                void el.offsetWidth; // force reflow
                                el.style.animationName = '';
                                el.style.animationDelay = Math.min(i * 30, 240) + 'ms';
                            });
                        });
                    });
                }
            }
        }
    </script>
    @endscript
</div>
