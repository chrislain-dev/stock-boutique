<div x-data x-init="
    document.querySelectorAll('.page-header, .kpi-card, .filter-bar, .table-card').forEach((el, i) => {
        el.style.opacity = 0;
        el.style.transform = 'translateY(10px)';
        setTimeout(() => {
            el.style.transition = 'opacity .35s cubic-bezier(.22,1,.36,1), transform .35s cubic-bezier(.22,1,.36,1)';
            el.style.opacity = 1;
            el.style.transform = 'translateY(0)';
        }, i * 60);
    });
">

    <style>
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

        .row-animate { animation: rowIn .26s cubic-bezier(.22,1,.36,1) both; }
        .badge-pop   { animation: badgePop .22s cubic-bezier(.34,1.56,.64,1) both; }

        .shimmer-line {
            background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
            background-size: 400px 100%;
            animation: shimmer 1.3s infinite linear;
            border-radius: 6px;
        }

        tbody tr {
            transition: background-color .12s ease, box-shadow .12s ease;
        }
        tbody tr:hover {
            box-shadow: inset 3px 0 0 0 #d1d5db;
        }

        button:active:not(:disabled) { transform: scale(0.97); }

        input:focus, select:focus {
            box-shadow: 0 0 0 3px rgba(24,24,27,.07);
        }
    </style>

    {{-- ─── Header ───────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3 page-header">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Achats fournisseurs</h1>
            <p class="text-sm text-gray-400 mt-0.5">Entrées de stock et suivi des commandes</p>
        </div>
        <a href="{{ route('purchases.create') }}"
           class="inline-flex items-center gap-2 h-9 px-4 rounded-xl bg-gray-900 text-white text-sm
                  hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md
                  transition-all duration-150">
            <x-heroicon-o-plus class="w-4 h-4"/>
            Nouvel achat
        </a>
    </div>

    {{-- ─── KPI Cards ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-3 mb-5">

        {{-- Achats ce mois --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5 kpi-card
                    transition-shadow duration-200 hover:shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                    <x-heroicon-o-calendar class="w-4 h-4 text-blue-600"/>
                </div>
                <span class="text-[11px] font-medium bg-blue-50 text-blue-700 px-2 py-0.5 rounded-md">Ce mois</span>
            </div>
            <p class="text-2xl font-semibold tracking-tight">{{ number_format($stats['total_month'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Achats · {{ config('boutique.devise_symbole') }}</p>
            <p class="text-[11px] text-gray-300 mt-2">{{ $stats['count_month'] }} commande(s) ce mois</p>
        </div>

        {{-- Commandes --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5 kpi-card
                    transition-shadow duration-200 hover:shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                    <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-violet-600"/>
                </div>
                <span class="text-[11px] font-medium bg-violet-50 text-violet-700 px-2 py-0.5 rounded-md">Commandes</span>
            </div>
            <p class="text-2xl font-semibold tracking-tight">{{ $stats['count_month'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Commandes ce mois</p>
            <p class="text-[11px] text-gray-300 mt-2">Entrées de stock enregistrées</p>
        </div>

        {{-- Dettes --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5 kpi-card
                    transition-shadow duration-200 hover:shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500"/>
                </div>
                <span class="text-[11px] font-medium bg-red-50 text-red-700 px-2 py-0.5 rounded-md">Attention</span>
            </div>
            <p class="text-2xl font-semibold tracking-tight">{{ number_format($stats['unpaid'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Dettes fournisseurs · {{ config('boutique.devise_symbole') }}</p>
            <p class="text-[11px] text-gray-300 mt-2">Solde impayé total</p>
        </div>
    </div>

    {{-- ─── Barre de filtres ─────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-2xl px-4 py-3 mb-5 flex-wrap filter-bar
                transition-shadow duration-200 hover:shadow-sm">

        {{-- Recherche --}}
        <div class="relative flex-1 min-w-[180px]" x-data="{ focused: false }">
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
                   placeholder="Référence, fournisseur..."
                   @focus="focused = true" @blur="focused = false"
                   class="w-full h-8 pl-9 pr-8 text-sm border rounded-lg outline-none bg-transparent
                          transition-all duration-200 border-gray-200 focus:border-gray-400 focus:bg-white"/>
            @if($search)
            <button wire:click="$set('search', '')"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-600 transition-colors duration-150">
                <x-heroicon-o-x-mark class="w-3.5 h-3.5"/>
            </button>
            @endif
        </div>

        {{-- Fournisseur --}}
        <select wire:model.live="supplierFilter"
                class="h-8 px-3 text-sm border border-gray-200 rounded-lg outline-none bg-white text-gray-600
                       hover:border-gray-300 focus:border-gray-400 transition-all duration-150 cursor-pointer">
            @foreach($suppliers as $s)
            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
            @endforeach
        </select>

        {{-- Statut paiement --}}
        <select wire:model.live="statusFilter"
                class="h-8 px-3 text-sm border border-gray-200 rounded-lg outline-none bg-white text-gray-600
                       hover:border-gray-300 focus:border-gray-400 transition-all duration-150 cursor-pointer">
            @foreach($statuses as $s)
            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
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
        @if($search || $statusFilter || $supplierFilter || $dateFrom || $dateTo)
        <button wire:click="$set('search',''); $set('statusFilter',''); $set('supplierFilter',''); $set('dateFrom',''); $set('dateTo','')"
                class="h-8 px-3 text-xs text-gray-400 hover:text-gray-700 border border-gray-200 rounded-lg
                       hover:bg-gray-50 hover:border-gray-300 transition-all duration-150 whitespace-nowrap">
            ✕ Réinitialiser
        </button>
        @endif
    </div>

    {{-- ─── Table ─────────────────────────────────────────────────────── --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden table-card">

        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <p class="text-sm font-medium">Commandes</p>
            <div class="flex items-center gap-2">
                <span wire:loading wire:target="search, statusFilter, supplierFilter, dateFrom, dateTo"
                      class="text-[11px] text-gray-400 flex items-center gap-1.5">
                    <svg class="w-3 h-3" style="animation: spin .7s linear infinite"
                         fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                    Filtrage…
                </span>
                <span wire:loading.remove wire:target="search, statusFilter, supplierFilter, dateFrom, dateTo"
                      class="text-[11px] text-gray-400">
                    {{ $purchases->total() }} commande(s)
                </span>
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Référence</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Fournisseur</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Date</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Articles</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Total</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Paiement</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Statut</th>
                    <th class="px-5 py-2.5 w-20"></th>
                </tr>
            </thead>
            <tbody>

                {{-- Skeleton --}}
                <tr wire:loading wire:target="search, statusFilter, supplierFilter, dateFrom, dateTo">
                    <td colspan="8" class="px-5 py-0">
                        @for($s = 0; $s < 6; $s++)
                        <div class="flex items-center gap-4 py-3.5 border-b border-gray-50 last:border-none">
                            <div class="shimmer-line h-4 w-24 shrink-0"></div>
                            <div class="shimmer-line h-4 w-28 shrink-0"></div>
                            <div class="shimmer-line h-4 w-20 shrink-0"></div>
                            <div class="shimmer-line h-4 w-12 shrink-0"></div>
                            <div class="shimmer-line h-4 w-24 shrink-0"></div>
                            <div class="shimmer-line h-5 w-16 shrink-0 rounded-md"></div>
                            <div class="shimmer-line h-5 w-16 shrink-0 rounded-md"></div>
                            <div class="shimmer-line h-4 w-12 shrink-0 ml-auto"></div>
                        </div>
                        @endfor
                    </td>
                </tr>

                {{-- Données --}}
                @forelse($purchases as $i => $purchase)
                <tr class="border-b border-gray-100 last:border-none row-animate"
                    style="animation-delay: {{ min($i * 30, 240) }}ms">

                    {{-- Référence --}}
                    <td class="px-5 py-3.5">
                        <span class="text-sm font-mono font-medium text-gray-800 tracking-wide">
                            {{ $purchase->reference }}
                        </span>
                    </td>

                    {{-- Fournisseur --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-violet-50 flex items-center justify-center text-[9px] font-bold text-violet-600 shrink-0">
                                {{ strtoupper(substr($purchase->supplier->name, 0, 2)) }}
                            </div>
                            <span class="text-sm text-gray-700">{{ $purchase->supplier->name }}</span>
                        </div>
                    </td>

                    {{-- Date --}}
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <p class="text-sm text-gray-700 font-medium">{{ $purchase->purchase_date->format('d/m/Y') }}</p>
                    </td>

                    {{-- Articles --}}
                    <td class="px-5 py-3.5">
                        <span class="text-sm text-gray-600">{{ $purchase->items->count() }}</span>
                        <span class="text-[11px] text-gray-400 ml-0.5">ligne(s)</span>
                    </td>

                    {{-- Total --}}
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="text-sm font-semibold text-gray-800">
                            {{ number_format($purchase->total_amount, 0, ',', ' ') }}
                        </span>
                        <span class="text-[11px] text-gray-400 ml-0.5">{{ config('boutique.devise_symbole') }}</span>
                    </td>

                    {{-- Badge paiement --}}
                    <td class="px-5 py-3.5">
                        @php
                            $payColor = match($purchase->payment_status->value ?? $purchase->payment_status) {
                                'paid'         => 'bg-green-50 text-green-800 ring-green-100',
                                'partial'      => 'bg-amber-50 text-amber-800 ring-amber-100',
                                'unpaid'       => 'bg-red-50 text-red-700 ring-red-100',
                                default        => 'bg-gray-100 text-gray-600 ring-gray-200',
                            };
                            $payDot = match($purchase->payment_status->value ?? $purchase->payment_status) {
                                'paid'    => 'bg-green-500',
                                'partial' => 'bg-amber-500',
                                'unpaid'  => 'bg-red-500',
                                default   => 'bg-gray-400',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium ring-1 badge-pop {{ $payColor }}"
                              style="animation-delay: {{ min($i * 30 + 50, 290) }}ms">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $payDot }}"></span>
                            {{ $purchase->payment_status->label() }}
                        </span>
                    </td>

                    {{-- Badge statut --}}
                    <td class="px-5 py-3.5">
                        @php
                            $stColor = match($purchase->status) {
                                'received'  => 'bg-green-50 text-green-800 ring-green-100',
                                'pending'   => 'bg-amber-50 text-amber-800 ring-amber-100',
                                'cancelled' => 'bg-red-50 text-red-700 ring-red-100',
                                default     => 'bg-gray-100 text-gray-600 ring-gray-200',
                            };
                            $stDot = match($purchase->status) {
                                'received'  => 'bg-green-500',
                                'pending'   => 'bg-amber-500',
                                'cancelled' => 'bg-red-500',
                                default     => 'bg-gray-400',
                            };
                            $stLabel = match($purchase->status) {
                                'received'  => 'Reçu',
                                'pending'   => 'En attente',
                                'cancelled' => 'Annulé',
                                default     => $purchase->status,
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium ring-1 badge-pop {{ $stColor }}"
                              style="animation-delay: {{ min($i * 30 + 80, 320) }}ms">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $stDot }}"></span>
                            {{ $stLabel }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('purchases.show', $purchase->id) }}"
                               class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                                      hover:bg-gray-100 hover:text-gray-700
                                      transition-all duration-150"
                               title="Voir">
                                <x-heroicon-o-eye class="w-3.5 h-3.5"/>
                            </a>
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('purchases.edit', $purchase->id) }}"
                               class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                                      hover:bg-gray-100 hover:text-gray-700
                                      transition-all duration-150"
                               title="Modifier">
                                <x-heroicon-o-pencil class="w-3.5 h-3.5"/>
                            </a>
                            @endif
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-16 text-center">
                        <div x-data
                             x-init="
                                $el.style.opacity = 0; $el.style.transform = 'translateY(8px)';
                                requestAnimationFrame(() => {
                                    $el.style.transition = 'opacity .3s ease, transform .3s ease';
                                    $el.style.opacity = 1; $el.style.transform = 'translateY(0)';
                                })">
                            <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                <x-heroicon-o-shopping-cart class="w-5 h-5 text-gray-300"/>
                            </div>
                            <p class="text-sm text-gray-400 font-medium">Aucune commande trouvée</p>
                            @if($search || $statusFilter || $supplierFilter || $dateFrom || $dateTo)
                            <p class="text-xs text-gray-300 mt-1">Essayez de modifier vos filtres</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($purchases->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>

    @script
    <script>
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
    </script>
    @endscript
</div>
