<div x-data="reprises()" x-init="init()">

    <style>
        @keyframes slideUp { from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);} }
        @keyframes rowIn   { from{opacity:0;transform:translateX(-5px);}to{opacity:1;transform:translateX(0);} }
        @keyframes spin    { to{transform:rotate(360deg);} }
        @keyframes errorIn { from{opacity:0;transform:translateY(-3px);}to{opacity:1;transform:translateY(0);} }
        .page-header { animation: slideUp .35s cubic-bezier(.22,1,.36,1) both; }
        .filter-bar  { animation: slideUp .35s cubic-bezier(.22,1,.36,1) .06s both; }
        .table-card  { animation: slideUp .35s cubic-bezier(.22,1,.36,1) .12s both; }
        .row-animate { animation: rowIn .26s cubic-bezier(.22,1,.36,1) both; }
        .error-msg   { animation: errorIn .18s ease both; }
        tbody tr { transition: background-color .12s ease; cursor: pointer; }
        tbody tr:hover { background-color: #fafafa; }
        button:active:not(:disabled) { transform: scale(0.97); }
        input:focus, select:focus, textarea:focus { box-shadow: 0 0 0 3px rgba(24,24,27,.07); }
        .field-error input, .field-error select { border-color:#fca5a5!important; background:#fff5f5!important; }
    </style>

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3 page-header">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Reprises / Troc</h1>
            <p class="text-sm text-gray-400 mt-0.5">Appareils repris à classifier avant remise en vente</p>
        </div>
        <div class="inline-flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-xl px-3 py-1.5 text-xs font-medium text-amber-800">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
            {{ $reprises->total() }} en attente de classification
        </div>
    </div>

    {{-- Filtre --}}
    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-2xl px-4 py-3 mb-4 filter-bar">
        <div class="relative flex-1">
            <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"/>
            <input wire:model.live.debounce="search"
                   placeholder="Modèle, IMEI, notes..."
                   class="w-full h-8 pl-9 pr-3 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 focus:bg-white transition-colors"/>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden table-card">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <p class="text-sm font-medium">Appareils en reprise</p>
            <span class="text-[11px] text-gray-400">{{ $reprises->total() }} appareil(s)</span>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Appareil</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Valeur reprise</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Notes / État</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Reçu le</th>
                    <th class="text-right text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reprises as $i => $product)
                <tr class="border-b border-gray-100 last:border-none row-animate"
                    style="animation-delay:{{ min($i*30,240) }}ms"
                    wire:click="openDetailModal({{ $product->id }})">

                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                                <x-heroicon-o-device-phone-mobile class="w-4 h-4 text-amber-700"/>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $product->productModel->display_label }}</p>
                                @if($product->imei)
                                <p class="text-[11px] font-mono text-gray-400 mt-0.5">{{ $product->imei }}</p>
                                @else
                                <p class="text-[11px] text-gray-300 mt-0.5">Pas d'IMEI</p>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4 whitespace-nowrap">
                        <span class="text-sm font-semibold text-amber-700">{{ number_format($product->purchase_price, 0, ',', ' ') }}</span>
                        <span class="text-[11px] text-gray-400 ml-0.5">{{ config('boutique.devise_symbole') }}</span>
                    </td>

                    <td class="px-5 py-4 max-w-45">
                        <span class="text-xs text-gray-500 leading-relaxed line-clamp-2">{{ $product->notes ?? '—' }}</span>
                    </td>

                    <td class="px-5 py-4 whitespace-nowrap">
                        <p class="text-sm text-gray-700">{{ $product->created_at->format('d/m/Y') }}</p>
                        <p class="text-[11px] font-mono text-gray-400 mt-0.5">{{ $product->created_at->format('H:i') }}</p>
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1.5" @click.stop>
                            <button wire:click="openDetailModal({{ $product->id }})"
                                    class="w-7 h-7 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center hover:bg-gray-200 transition-colors"
                                    title="Voir détails">
                                <x-heroicon-o-information-circle class="w-3.5 h-3.5 text-gray-500"/>
                            </button>
                            <button wire:click="openStoreModal({{ $product->id }})"
                                    class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-lg text-[11px] font-medium bg-green-50 text-green-800 border border-green-200 hover:bg-green-100 transition-colors">
                                <x-heroicon-o-shopping-bag class="w-3 h-3"/>
                                Boutique
                            </button>
                            <button wire:click="openRepairModal({{ $product->id }})"
                                    class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-lg text-[11px] font-medium bg-blue-50 text-blue-800 border border-blue-200 hover:bg-blue-100 transition-colors">
                                <x-heroicon-o-wrench-screwdriver class="w-3 h-3"/>
                                Répar.
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-16 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-3">
                            <x-heroicon-o-arrow-path class="w-5 h-5 text-amber-300"/>
                        </div>
                        <p class="text-sm text-gray-400 font-medium">Aucune reprise en attente</p>
                        <p class="text-xs text-gray-300 mt-1">Les appareils repris lors de trocs apparaîtront ici</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($reprises->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
            {{ $reprises->links() }}
        </div>
        @endif
    </div>


    {{-- Modal : Détail --}}
    <div x-data="{ open: @entangle('showDetailModal').live }" x-cloak
         @keydown.escape.window="$wire.set('showDetailModal', false)">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/25 backdrop-blur-[3px]"
             wire:click="$set('showDetailModal', false)"></div>
        <div x-show="open"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
            <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md pointer-events-auto overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Détail de la reprise</h2>
                    <button wire:click="$set('showDetailModal', false)"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:rotate-90 transition-all duration-200">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                    </button>
                </div>
                @if($detailProduct)
                <div class="flex items-center gap-3.5 px-5 py-4 border-b border-gray-100">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center shrink-0">
                        <x-heroicon-o-device-phone-mobile class="w-6 h-6 text-amber-700"/>
                    </div>
                    <div>
                        <p class="text-[15px] font-semibold text-gray-900 tracking-tight">{{ $detailProduct->productModel->display_label }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Reprise client · {{ $detailProduct->created_at->format('d/m/Y à H\hi') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 divide-x divide-y divide-gray-100 border-b border-gray-100">
                    <div class="px-5 py-3.5">
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">IMEI</p>
                        <p class="text-[12px] font-mono text-gray-800">{{ $detailProduct->imei ?? '—' }}</p>
                    </div>
                    <div class="px-5 py-3.5">
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Valeur reprise</p>
                        <p class="text-sm font-semibold text-amber-700">{{ number_format($detailProduct->purchase_price, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</p>
                    </div>
                    <div class="px-5 py-3.5">
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Prix client</p>
                        <p class="text-sm text-gray-700">{{ $detailProduct->client_price ? number_format($detailProduct->client_price, 0, ',', ' ') . ' ' . config('boutique.devise_symbole') : '—' }}</p>
                    </div>
                    <div class="px-5 py-3.5">
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Prix revendeur</p>
                        <p class="text-sm text-gray-700">{{ $detailProduct->reseller_price ? number_format($detailProduct->reseller_price, 0, ',', ' ') . ' ' . config('boutique.devise_symbole') : '—' }}</p>
                    </div>
                    <div class="px-5 py-3.5">
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Localisation</p>
                        <p class="text-sm text-gray-700">Reprise (en attente)</p>
                    </div>
                    <div class="px-5 py-3.5">
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Enregistré par</p>
                        <p class="text-sm text-gray-700">{{ $detailProduct->createdBy?->name ?? '—' }}</p>
                    </div>
                </div>
                @if($detailProduct->notes)
                <div class="px-5 py-4 bg-gray-50 border-b border-gray-100">
                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1.5">Notes / État observé</p>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $detailProduct->notes }}</p>
                </div>
                @endif
                <div class="flex items-center gap-2 px-5 py-4">
                    <button wire:click="openStoreFromDetail"
                            class="flex-1 h-10 bg-gray-900 text-white rounded-xl text-sm font-medium hover:opacity-85 transition-opacity flex items-center justify-center gap-2">
                        <x-heroicon-o-shopping-bag class="w-4 h-4"/>
                        Mettre en boutique
                    </button>
                    <button wire:click="openRepairFromDetail"
                            class="flex-1 h-10 bg-blue-50 text-blue-800 border border-blue-200 rounded-xl text-sm font-medium hover:bg-blue-100 transition-colors flex items-center justify-center gap-2">
                        <x-heroicon-o-wrench-screwdriver class="w-4 h-4"/>
                        Envoyer en réparation
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>


    {{-- Modal : Mettre en boutique --}}
    <div x-data="{ open: @entangle('showStoreModal').live }" x-cloak
         @keydown.escape.window="$wire.set('showStoreModal', false)">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/25 backdrop-blur-[3px]"
             wire:click="$set('showStoreModal', false)"></div>
        <div x-show="open"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
            <div class="relative bg-white rounded-2xl shadow-2xl shadow-gray-200/80 border border-gray-100 w-full max-w-md pointer-events-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Mettre en boutique</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Définir les prix de revente</p>
                    </div>
                    <button wire:click="$set('showStoreModal', false)"
                            class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:rotate-90 transition-all duration-200">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Condition</label>
                        <select wire:model="store_condition"
                                class="w-full h-9 px-3 text-sm border border-gray-200 rounded-xl outline-none bg-white hover:border-gray-300 focus:border-gray-400 transition-all cursor-pointer">
                            @foreach($conditions as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="{{ $errors->has('store_client_price') ? 'field-error' : '' }}">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Prix client <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input wire:model="store_client_price" type="number" min="0"
                                   class="w-full h-9 pl-3 pr-14 text-sm border border-gray-200 rounded-xl outline-none bg-white hover:border-gray-300 focus:border-gray-400 transition-all"/>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 select-none">{{ config('boutique.devise_symbole') }}</span>
                        </div>
                        @error('store_client_price')
                        <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>{{ $message }}
                        </p>
                        @enderror
                    </div>
                    <div class="{{ $errors->has('store_reseller_price') ? 'field-error' : '' }}">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Prix revendeur <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input wire:model="store_reseller_price" type="number" min="0"
                                   class="w-full h-9 pl-3 pr-14 text-sm border border-gray-200 rounded-xl outline-none bg-white hover:border-gray-300 focus:border-gray-400 transition-all"/>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 select-none">{{ config('boutique.devise_symbole') }}</span>
                        </div>
                        @error('store_reseller_price')
                        <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>{{ $message }}
                        </p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes</label>
                        <textarea wire:model="store_notes" rows="2"
                                  placeholder="Description du produit pour la vente..."
                                  class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl outline-none hover:border-gray-300 focus:border-gray-400 resize-none transition-all leading-relaxed"></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                    <button wire:click="$set('showStoreModal', false)"
                            class="h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600 hover:bg-gray-50 transition-all">
                        Annuler
                    </button>
                    <button wire:click="putInStore" wire:loading.attr="disabled"
                            class="h-9 px-4 rounded-xl bg-green-600 text-white text-sm hover:bg-green-700 disabled:opacity-75 transition-all flex items-center gap-1.5">
                        <svg wire:loading wire:target="putInStore" class="w-3.5 h-3.5" style="animation:spin .7s linear infinite" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <x-heroicon-o-check wire:loading.remove wire:target="putInStore" class="w-4 h-4"/>
                        Mettre en boutique
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- Modal : Envoyer en réparation --}}
    <div x-data="{ open: @entangle('showRepairModal').live }" x-cloak
         @keydown.escape.window="$wire.set('showRepairModal', false)">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/25 backdrop-blur-[3px]"
             wire:click="$set('showRepairModal', false)"></div>
        <div x-show="open"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
            <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-sm pointer-events-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Envoyer en réparation</h2>
                        <p class="text-xs text-gray-400 mt-0.5">L'appareil sera déplacé vers le réparateur</p>
                    </div>
                    <button wire:click="$set('showRepairModal', false)"
                            class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:rotate-90 transition-all duration-200">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                    </button>
                </div>
                <div class="px-6 py-5">
                    <div class="{{ $errors->has('repair_notes') ? 'field-error' : '' }}">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Raison / Défaut observé <span class="text-red-400">*</span>
                        </label>
                        <textarea wire:model="repair_notes" rows="3"
                                  placeholder="Ex: Écran fissuré, batterie défaillante, micro HS..."
                                  class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl outline-none hover:border-gray-300 focus:border-gray-400 resize-none transition-all leading-relaxed"></textarea>
                        @error('repair_notes')
                        <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>{{ $message }}
                        </p>
                        @enderror
                    </div>
                </div>
                <div class="flex items-center gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                    <button wire:click="$set('showRepairModal', false)"
                            class="flex-1 h-9 rounded-xl border border-gray-200 bg-white text-sm text-gray-600 hover:bg-gray-50 transition-all">
                        Annuler
                    </button>
                    <button wire:click="sendToRepair" wire:loading.attr="disabled"
                            class="flex-1 h-9 rounded-xl bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-75 transition-all flex items-center justify-center gap-1.5">
                        <svg wire:loading wire:target="sendToRepair" class="w-3.5 h-3.5" style="animation:spin .7s linear infinite" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <x-heroicon-o-wrench-screwdriver wire:loading.remove wire:target="sendToRepair" class="w-4 h-4"/>
                        Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        function reprises() {
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
