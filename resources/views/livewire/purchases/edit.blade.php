<div x-data x-init="
    $nextTick(() => {
        document.querySelectorAll('.page-header, .form-card').forEach((el, i) => {
            el.style.opacity = 0;
            el.style.transform = 'translateY(10px)';
            setTimeout(() => {
                el.style.transition = 'opacity .35s cubic-bezier(.22,1,.36,1), transform .35s cubic-bezier(.22,1,.36,1)';
                el.style.opacity = 1;
                el.style.transform = 'translateY(0)';
            }, i * 70);
        });
    })
">

    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        input:focus, select:focus, textarea:focus {
            box-shadow: 0 0 0 3px rgba(24,24,27,.07);
        }
        button:active:not(:disabled) { transform: scale(0.97); }

        .field-error input,
        .field-error select,
        .field-error textarea {
            border-color: #fca5a5 !important;
            background-color: #fff5f5 !important;
        }
        .field-error input:focus,
        .field-error select:focus,
        .field-error textarea:focus {
            border-color: #f87171 !important;
            box-shadow: 0 0 0 3px rgba(239,68,68,.08) !important;
        }
        .field-ok input,
        .field-ok select,
        .field-ok textarea {
            border-color: #bbf7d0 !important;
        }
        @keyframes errorIn {
            from { opacity: 0; transform: translateY(-3px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .error-msg { animation: errorIn .18s ease both; }
    </style>

    {{-- ─── Header ───────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3 page-header">
        <div>
            <div class="flex items-center gap-2.5 mb-0.5">
                <h1 class="text-xl font-semibold tracking-tight text-gray-900">Modifier</h1>
                <span class="text-xl font-semibold font-mono text-gray-400">{{ $purchase->reference }}</span>
            </div>
            <p class="text-sm text-gray-400">{{ $purchase->supplier->name }}</p>
        </div>
        <a href="{{ route('purchases.show', $purchase->id) }}"
           class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600
                  hover:bg-gray-50 hover:border-gray-300 transition-all duration-150">
            <x-heroicon-o-x-mark class="w-4 h-4"/>
            Annuler
        </a>
    </div>

    {{-- ─── Formulaire ───────────────────────────────────────────────── --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden form-card
                transition-shadow duration-200 hover:shadow-sm">

        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <p class="text-sm font-medium text-gray-800">Informations de l'achat</p>
            <p class="text-xs text-gray-400 mt-0.5">Modifiez les champs nécessaires puis enregistrez</p>
        </div>

        <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Fournisseur --}}
                <div class="{{ $errors->has('supplier_id') ? 'field-error' : ($supplier_id ? 'field-ok' : '') }}">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Fournisseur <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <x-heroicon-o-building-office class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10"/>
                        <select wire:model="supplier_id"
                                class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                       hover:border-gray-300 focus:border-gray-400 transition-all duration-150 cursor-pointer">
                            @foreach($suppliers as $s)
                            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('supplier_id')
                    <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Date d'achat --}}
                <div class="{{ $errors->has('purchase_date') ? 'field-error' : ($purchase_date ? 'field-ok' : '') }}">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Date d'achat <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <x-heroicon-o-calendar class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                        <input wire:model="purchase_date" type="date"
                               class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                      hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                    </div>
                    @error('purchase_date')
                    <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Statut commande --}}
                <div class="{{ $errors->has('status') ? 'field-error' : '' }}">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Statut commande <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <x-heroicon-o-clipboard-document-check class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10"/>
                        <select wire:model="status"
                                class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                       hover:border-gray-300 focus:border-gray-400 transition-all duration-150 cursor-pointer">
                            <option value="received">Reçu</option>
                            <option value="pending">En attente</option>
                            <option value="cancelled">Annulé</option>
                        </select>
                    </div>
                    @error('status')
                    <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Statut paiement --}}
                <div class="{{ $errors->has('payment_status') ? 'field-error' : '' }}">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Statut paiement <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <x-heroicon-o-banknotes class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10"/>
                        <select wire:model.live="payment_status"
                                class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                       hover:border-gray-300 focus:border-gray-400 transition-all duration-150 cursor-pointer">
                            @foreach($paymentStatuses as $ps)
                            <option value="{{ $ps['id'] }}">{{ $ps['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('payment_status')
                    <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Champs conditionnels paiement --}}
                @if($payment_status !== 'unpaid')

                {{-- Mode de paiement --}}
                <div x-data x-init="
                    $el.style.opacity=0; $el.style.transform='translateY(4px)';
                    requestAnimationFrame(() => {
                        $el.style.transition='opacity .2s ease, transform .2s ease';
                        $el.style.opacity=1; $el.style.transform='translateY(0)';
                    })">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Mode de paiement</label>
                    <div class="relative">
                        <x-heroicon-o-credit-card class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10"/>
                        <select wire:model="payment_method"
                                class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                       hover:border-gray-300 focus:border-gray-400 transition-all duration-150 cursor-pointer">
                            <option value="">— Sélectionner —</option>
                            @foreach($paymentMethods as $pm)
                            <option value="{{ $pm['id'] }}">{{ $pm['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Montant payé --}}
                <div class="{{ $errors->has('paid_amount') ? 'field-error' : '' }}"
                     x-data x-init="
                        $el.style.opacity=0; $el.style.transform='translateY(4px)';
                        requestAnimationFrame(() => {
                            $el.style.transition='opacity .2s ease .04s, transform .2s ease .04s';
                            $el.style.opacity=1; $el.style.transform='translateY(0)';
                        })">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Montant payé</label>
                    <div class="relative">
                        <x-heroicon-o-banknotes class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                        <input wire:model="paid_amount" type="number"
                               class="w-full h-9 pl-9 pr-14 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                      hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 select-none">
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    @error('paid_amount')
                    <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Référence transaction --}}
                <div x-data x-init="
                    $el.style.opacity=0; $el.style.transform='translateY(4px)';
                    requestAnimationFrame(() => {
                        $el.style.transition='opacity .2s ease .08s, transform .2s ease .08s';
                        $el.style.opacity=1; $el.style.transform='translateY(0)';
                    })">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Référence transaction</label>
                    <div class="relative">
                        <x-heroicon-o-hashtag class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                        <input wire:model="transaction_reference"
                               placeholder="Ex: TXN-123456"
                               class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                      hover:border-gray-300 focus:border-gray-400 transition-all duration-150 font-mono"/>
                    </div>
                </div>
                @endif

                {{-- Date limite si partiel / impayé --}}
                @if($payment_status === 'partial' || $payment_status === 'unpaid')
                <div class="{{ $errors->has('due_date') ? 'field-error' : '' }}"
                     x-data x-init="
                        $el.style.opacity=0; $el.style.transform='translateY(4px)';
                        requestAnimationFrame(() => {
                            $el.style.transition='opacity .2s ease, transform .2s ease';
                            $el.style.opacity=1; $el.style.transform='translateY(0)';
                        })">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Date limite paiement</label>
                    <div class="relative">
                        <x-heroicon-o-clock class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                        <input wire:model="due_date" type="date"
                               class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                      hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                    </div>
                    @error('due_date')
                    <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                        {{ $message }}
                    </p>
                    @enderror
                </div>
                @endif

                {{-- Notes --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes</label>
                    <textarea wire:model="notes"
                              rows="2"
                              placeholder="Remarques sur cet achat..."
                              class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                     hover:border-gray-300 focus:border-gray-400
                                     resize-none transition-all duration-150 leading-relaxed"></textarea>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/60">
            <a href="{{ route('purchases.show', $purchase->id) }}"
               class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600
                      hover:bg-gray-50 hover:border-gray-300 transition-all duration-150">
                Annuler
            </a>
            <button wire:click="save"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-75 cursor-wait"
                    class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-gray-900 text-white text-sm
                           hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md
                           disabled:opacity-75 disabled:cursor-wait disabled:translate-y-0
                           transition-all duration-150">
                <svg wire:loading wire:target="save"
                     class="w-3.5 h-3.5" style="animation: spin .7s linear infinite"
                     fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                <x-heroicon-o-check wire:loading.remove wire:target="save" class="w-4 h-4"/>
                Enregistrer
            </button>
        </div>
    </div>

</div>
