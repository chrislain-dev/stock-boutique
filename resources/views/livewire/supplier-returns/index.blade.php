<div x-data="supplierReturns()" x-init="init()">

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

        tbody tr { transition: background-color .12s ease, box-shadow .12s ease; }
        tbody tr:hover { box-shadow: inset 3px 0 0 0 #d1d5db; }
        button:active:not(:disabled) { transform: scale(0.97); }
        input:focus, select:focus, textarea:focus { box-shadow: 0 0 0 3px rgba(24,24,27,.07); }
        .field-error textarea { border-color:#fca5a5!important; background:#fff5f5!important; }
    </style>

    {{-- ─── Header ─────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3 page-header">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Retours fournisseur</h1>
            <p class="text-sm text-gray-400 mt-0.5">Produits défectueux retournés par les clients</p>
        </div>
    </div>

    {{-- ─── Filtre statut ───────────────────────────────────────── --}}
    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-2xl px-4 py-3 mb-5 filter-bar">
        <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Statut</span>
        <div class="flex gap-1.5">
            @foreach([
                ['id' => 'pending',              'name' => 'En attente'],
                ['id' => 'sent_to_supplier',     'name' => 'Envoyés'],
                ['id' => 'replacement_received', 'name' => 'Remplacés'],
                ['id' => 'all',                  'name' => 'Tous'],
            ] as $f)
            <button wire:click="$set('statusFilter','{{ $f['id'] }}')"
                    class="h-8 px-3.5 rounded-lg text-sm transition-all border
                           {{ $statusFilter === $f['id']
                               ? 'bg-gray-900 text-white border-gray-900'
                               : 'bg-transparent text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                {{ $f['name'] }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- ─── Table ───────────────────────────────────────────────── --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden table-card">

        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <p class="text-sm font-medium">Retours</p>
            <span class="text-[11px] text-gray-400">{{ $returns->total() }} retour(s)</span>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Produit</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Vente</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Raison</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Statut</th>
                    <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Déclaré le</th>
                    <th class="px-5 py-2.5 text-right text-[11px] font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $i => $return)
                <tr class="border-b border-gray-100 last:border-none row-animate"
                    style="animation-delay:{{ min($i*30,240) }}ms">

                    {{-- Produit --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500"/>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $return->product->productModel->display_label }}
                                </p>
                                <p class="text-[11px] font-mono text-gray-400 mt-0.5">
                                    {{ $return->product->imei ?? $return->product->serial_number ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </td>

                    {{-- Vente --}}
                    <td class="px-5 py-4">
                        <a href="{{ route('sales.show', $return->sale_id) }}"
                           class="text-sm font-mono text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                            {{ $return->sale->reference }}
                        </a>
                    </td>

                    {{-- Raison --}}
                    <td class="px-5 py-4 max-w-50">
                        <span class="text-xs text-gray-600 leading-relaxed line-clamp-2">{{ $return->reason }}</span>
                    </td>

                    {{-- Statut --}}
                    <td class="px-5 py-4">
                        @php
                            [$stColor, $stDot, $stLabel] = match($return->status) {
                                'pending'              => ['bg-amber-50 text-amber-800 ring-amber-100', 'bg-amber-500', 'En attente'],
                                'sent_to_supplier'     => ['bg-blue-50 text-blue-800 ring-blue-100',   'bg-blue-500',  'Envoyé'],
                                'replacement_received' => ['bg-green-50 text-green-800 ring-green-100','bg-green-500', 'Remplacé'],
                                default                => ['bg-gray-100 text-gray-600 ring-gray-200',  'bg-gray-400',  $return->status],
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium ring-1 {{ $stColor }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $stDot }}"></span>
                            {{ $stLabel }}
                        </span>
                    </td>

                    {{-- Date --}}
                    <td class="px-5 py-4 whitespace-nowrap">
                        <p class="text-sm text-gray-700">{{ $return->created_at->format('d/m/Y') }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $return->declaredBy?->name ?? '—' }}</p>
                    </td>

                    {{-- Actions --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($return->isPending())
                            <button wire:click="openSentModal({{ $return->id }})"
                                    class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg text-[11px] font-medium
                                           bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-100 transition-all duration-150">
                                <x-heroicon-o-paper-airplane class="w-3.5 h-3.5"/>
                                Marquer envoyé
                            </button>
                            @elseif($return->isSent())
                            <button wire:click="openReplaceModal({{ $return->id }})"
                                    class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg text-[11px] font-medium
                                           bg-green-50 text-green-700 hover:bg-green-100 border border-green-100 transition-all duration-150">
                                <x-heroicon-o-arrow-path class="w-3.5 h-3.5"/>
                                Remplacement reçu
                            </button>
                            @else
                            <div class="flex items-center gap-1.5">
                                <span class="text-[11px] text-green-600 font-medium">✓ Remplacé le</span>
                                <span class="text-[11px] text-gray-400">{{ $return->replaced_at?->format('d/m/Y') }}</span>
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center">
                        <div x-data x-init="$el.style.opacity=0;$el.style.transform='translateY(8px)';requestAnimationFrame(()=>{$el.style.transition='opacity .3s ease,transform .3s ease';$el.style.opacity=1;$el.style.transform='translateY(0)'})">
                            <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                <x-heroicon-o-check-circle class="w-5 h-5 text-gray-300"/>
                            </div>
                            <p class="text-sm text-gray-400 font-medium">Aucun retour
                                {{ $statusFilter === 'pending' ? 'en attente' : ($statusFilter === 'sent_to_supplier' ? 'envoyé' : ($statusFilter === 'replacement_received' ? 'remplacé' : '')) }}
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($returns->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
            {{ $returns->links() }}
        </div>
        @endif
    </div>


    {{-- ─── Modal : Marquer comme envoyé ──────────────────────── --}}
    <div x-data="{ open: @entangle('showSentModal').live }" x-cloak @keydown.escape.window="$wire.set('showSentModal',false)">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/25 backdrop-blur-[3px]" wire:click="$set('showSentModal',false)"></div>
        <div x-show="open"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
            <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-sm pointer-events-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Marquer comme envoyé</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Confirmer l'envoi au fournisseur</p>
                    </div>
                    <button wire:click="$set('showSentModal',false)" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:rotate-90 transition-all duration-200">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                    </button>
                </div>
                <div class="px-6 py-5">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes (optionnel)</label>
                    <textarea wire:model="sent_notes" rows="3" placeholder="Numéro de suivi, transporteur..."
                              class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl outline-none hover:border-gray-300 focus:border-gray-400 resize-none transition-all duration-150 leading-relaxed"></textarea>
                </div>
                <div class="flex items-center gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                    <button wire:click="$set('showSentModal',false)" class="flex-1 h-9 rounded-xl border border-gray-200 bg-white text-sm text-gray-600 hover:bg-gray-50 transition-all duration-150">Annuler</button>
                    <button wire:click="markAsSent" wire:loading.attr="disabled"
                            class="flex-1 h-9 rounded-xl bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-75 transition-all duration-150 flex items-center justify-center gap-1.5">
                        <svg wire:loading wire:target="markAsSent" class="w-3.5 h-3.5" style="animation:spin .7s linear infinite" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <x-heroicon-o-paper-airplane wire:loading.remove wire:target="markAsSent" class="w-4 h-4"/>
                        Confirmer l'envoi
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- ─── Modal : Remplacement reçu ──────────────────────────── --}}
    <div x-data="{ open: @entangle('showReplaceModal').live }" x-cloak @keydown.escape.window="$wire.set('showReplaceModal',false)">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/25 backdrop-blur-[3px]" wire:click="$set('showReplaceModal',false)"></div>
        <div x-show="open"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
            <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md pointer-events-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Remplacement reçu</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Scanner le nouveau produit envoyé par le fournisseur</p>
                    </div>
                    <button wire:click="$set('showReplaceModal',false)" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:rotate-90 transition-all duration-200">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">

                    {{-- IMEI remplacement --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            IMEI du produit de remplacement <span class="text-red-400">*</span>
                        </label>

                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <x-heroicon-o-qr-code class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                                <input wire:model="replace_imei"
                                    wire:keydown.enter="searchReplacement"
                                    placeholder="Scanner ou saisir l'IMEI..."
                                    class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none
                                            bg-white hover:border-gray-300 focus:border-gray-400 transition-all duration-150 font-mono"/>
                            </div>
                            <button wire:click="searchReplacement"
                                    class="h-9 px-3 rounded-xl bg-gray-900 text-white text-sm flex items-center gap-1.5 hover:bg-gray-800 transition-colors shrink-0"
                                    title="Rechercher">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4"/>
                            </button>
                            <button wire:click="openCreateReplacementModal"
                                    class="h-9 px-3 rounded-xl bg-green-600 text-white text-sm flex items-center gap-1.5 hover:bg-green-700 transition-colors shrink-0"
                                    title="Créer le produit de remplacement">
                                <x-heroicon-o-plus class="w-4 h-4"/>
                            </button>
                        </div>

                        @if($replace_search_error)
                        <p class="error-msg flex items-center gap-1.5 text-[11px] text-amber-600 mt-1.5">
                            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                            {{ $replace_search_error }}
                            <button wire:click="openCreateReplacementModal"
                                    class="ml-1 underline text-green-600 hover:text-green-800 font-medium">
                                Créer le produit →
                            </button>
                        </p>
                        @endif

                        @if($replaceProduct)
                        <div class="mt-2 flex items-center gap-2 p-2.5 bg-green-50 border border-green-100 rounded-xl">
                            <x-heroicon-o-check-circle class="w-4 h-4 text-green-600 shrink-0"/>
                            <span class="text-sm font-medium text-green-800">{{ $replaceProduct->productModel->display_label }}</span>
                            <span class="text-green-500 font-mono text-[11px] ml-auto">{{ $replaceProduct->imei ?? $replaceProduct->serial_number }}</span>
                        </div>
                        @endif

                        @error('replace_product_id')
                        <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>{{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes (optionnel)</label>
                        <textarea wire:model="replace_notes" rows="2" placeholder="Remarques sur le remplacement..."
                                  class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl outline-none hover:border-gray-300 focus:border-gray-400 resize-none transition-all duration-150 leading-relaxed"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                    <button wire:click="$set('showReplaceModal',false)" class="h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600 hover:bg-gray-50 transition-all duration-150">Annuler</button>
                    <button wire:click="receiveReplacement" wire:loading.attr="disabled"
                            class="h-9 px-5 rounded-xl bg-green-600 text-white text-sm hover:bg-green-700 hover:-translate-y-0.5 hover:shadow-md hover:shadow-green-200/60 disabled:opacity-75 transition-all duration-150 flex items-center gap-1.5">
                        <svg wire:loading wire:target="receiveReplacement" class="w-3.5 h-3.5" style="animation:spin .7s linear infinite" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <x-heroicon-o-check wire:loading.remove wire:target="receiveReplacement" class="w-4 h-4"/>
                        Valider le remplacement
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Modal : Créer produit de remplacement ─────────────── --}}
    <div x-data="{ open: @entangle('showCreateReplacementModal').live }" x-cloak
        @keydown.escape.window="$wire.set('showCreateReplacementModal', false)">
        <div x-show="open"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-60 bg-black/25 backdrop-blur-[3px]"
            wire:click="$set('showCreateReplacementModal', false)"></div>

        <div x-show="open"
            x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="fixed inset-0 z-70 flex items-center justify-center p-4 pointer-events-none">
            <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md pointer-events-auto">

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Nouveau produit de remplacement</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Enregistrer le produit envoyé par le fournisseur</p>
                    </div>
                    <button wire:click="$set('showCreateReplacementModal', false)"
                            class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:rotate-90 transition-all duration-200">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">

                    {{-- Modèle --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Modèle <span class="text-red-400">*</span>
                        </label>
                        <select wire:model="new_product_model_id"
                                class="w-full h-9 px-3 text-sm border border-gray-200 rounded-xl outline-none
                                    bg-white hover:border-gray-300 focus:border-gray-400 transition-all cursor-pointer">
                            <option value="">— Sélectionner un modèle</option>
                            @foreach($productModels as $model)
                            <option value="{{ $model->id }}">{{ $model->display_label }}</option>
                            @endforeach
                        </select>
                        @error('new_product_model_id')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- IMEI --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            IMEI <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <x-heroicon-o-qr-code class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model="new_product_imei"
                                placeholder="IMEI du nouveau produit"
                                class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none
                                        bg-white hover:border-gray-300 focus:border-gray-400 transition-all font-mono"/>
                        </div>
                        @error('new_product_imei')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                    <button wire:click="$set('showCreateReplacementModal', false)"
                            class="h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600 hover:bg-gray-50 transition-all">
                        Annuler
                    </button>
                    <button wire:click="createReplacementProduct"
                            wire:loading.attr="disabled"
                            class="h-9 px-5 rounded-xl bg-green-600 text-white text-sm hover:bg-green-700
                                disabled:opacity-75 transition-all flex items-center gap-1.5">
                        <svg wire:loading wire:target="createReplacementProduct"
                            class="w-3.5 h-3.5" style="animation:spin .7s linear infinite"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <x-heroicon-o-check wire:loading.remove wire:target="createReplacementProduct" class="w-4 h-4"/>
                        Créer et associer
                    </button>
                </div>

            </div>
        </div>
    </div>

    @script
    <script>
        function supplierReturns() {
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
