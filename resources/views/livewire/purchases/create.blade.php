<div x-data x-init="
    $nextTick(() => {
        document.querySelectorAll('.page-header, .stepper, .step-panel').forEach((el, i) => {
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
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideRight {
            from { opacity: 0; transform: translateX(18px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideLeft {
            from { opacity: 0; transform: translateX(-18px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes errorIn {
            0%   { opacity: 0; transform: translateY(-4px); max-height: 0; }
            100% { opacity: 1; transform: translateY(0);   max-height: 40px; }
        }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%     { transform: translateX(-4px); }
            40%     { transform: translateX(4px); }
            60%     { transform: translateX(-3px); }
            80%     { transform: translateX(3px); }
        }
        @keyframes lineIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes checkPop {
            0%   { transform: scale(0); opacity: 0; }
            60%  { transform: scale(1.15); }
            100% { transform: scale(1); opacity: 1; }
        }

        .step-panel  { animation: slideRight .3s cubic-bezier(.22,1,.36,1) both; }
        .error-msg   { animation: errorIn .2s ease both; overflow: hidden; }
        .line-item   { animation: lineIn .25s cubic-bezier(.22,1,.36,1) both; }
        .check-pop   { animation: checkPop .3s cubic-bezier(.34,1.56,.64,1) both; }

        /* Champs en erreur */
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

        /* Champs OK */
        .field-ok input,
        .field-ok select,
        .field-ok textarea {
            border-color: #bbf7d0 !important;
        }

        /* Focus général */
        input:focus, select:focus, textarea:focus {
            box-shadow: 0 0 0 3px rgba(24,24,27,.07);
        }

        button:active:not(:disabled) { transform: scale(0.97); }

        /* Connecteur stepper animé */
        .step-line { transition: background-color .4s ease; }

        /* Card hover */
        .form-card { transition: box-shadow .2s ease; }
        .form-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.05); }

        /* Ligne bon de commande hover */
        .bc-line { transition: background-color .12s ease, box-shadow .12s ease; }
        .bc-line:hover { background-color: #fafafa; box-shadow: inset 3px 0 0 0 #d1d5db; }
    </style>

    {{-- ─── Header ───────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3 page-header">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Nouvel achat</h1>
            <p class="text-sm text-gray-400 mt-0.5">Entrée de stock fournisseur</p>
        </div>
        <a href="{{ route('purchases.index') }}"
           class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600
                  hover:bg-gray-50 hover:border-gray-300 transition-all duration-150">
            <x-heroicon-o-x-mark class="w-4 h-4"/>
            Annuler
        </a>
    </div>

    {{-- ─── Stepper ──────────────────────────────────────────────────── --}}
    <div class="flex items-center mb-8 stepper">
        @foreach([1 => 'Informations', 2 => 'Produits', 3 => 'Récapitulatif'] as $num => $label)
        <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
            <div class="flex items-center gap-2.5">

                {{-- Cercle --}}
                <div class="relative w-8 h-8 shrink-0">
                    @if($step > $num)
                    {{-- Complété --}}
                    <div class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center check-pop">
                        <x-heroicon-o-check class="w-3.5 h-3.5 text-white"/>
                    </div>
                    @elseif($step === $num)
                    {{-- Actif --}}
                    <div class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center ring-4 ring-gray-900/10
                                transition-all duration-300">
                        <span class="text-xs font-bold text-white">{{ $num }}</span>
                    </div>
                    @else
                    {{-- Futur --}}
                    <div class="w-8 h-8 rounded-full bg-gray-100 border-2 border-gray-200 flex items-center justify-center
                                transition-all duration-300">
                        <span class="text-xs font-medium text-gray-400">{{ $num }}</span>
                    </div>
                    @endif
                </div>

                {{-- Label --}}
                <span class="text-sm font-medium transition-colors duration-200
                    {{ $step === $num ? 'text-gray-900' : ($step > $num ? 'text-gray-500' : 'text-gray-300') }}">
                    {{ $label }}
                </span>
            </div>

            {{-- Connecteur --}}
            @if(!$loop->last)
            <div class="flex-1 h-px mx-4 step-line {{ $step > $num ? 'bg-gray-900' : 'bg-gray-200' }}"></div>
            @endif
        </div>
        @endforeach
    </div>


    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- Étape 1 : Informations                                        --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @if($step === 1)
    <div class="step-panel">
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden form-card">

            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <p class="text-sm font-medium text-gray-800">Informations de l'achat</p>
                <p class="text-xs text-gray-400 mt-0.5">Renseignez les détails de la commande fournisseur</p>
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
                                <option value="">Sélectionner un fournisseur</option>
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

                    {{-- Statut paiement --}}
                    <div class="{{ $errors->has('payment_status') ? 'field-error' : ($payment_status ? 'field-ok' : '') }}">
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
                    <div x-data x-init="$el.style.animation='slideUp .25s ease both'">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Mode de paiement</label>
                        <div class="relative">
                            <x-heroicon-o-credit-card class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10"/>
                            <select wire:model="payment_method"
                                    class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                           hover:border-gray-300 focus:border-gray-400 transition-all duration-150 cursor-pointer">
                                @foreach($paymentMethods as $pm)
                                <option value="{{ $pm['id'] }}">{{ $pm['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Montant payé --}}
                    <div class="{{ $errors->has('paid_amount') ? 'field-error' : '' }}"
                         x-data x-init="$el.style.animation='slideUp .25s ease .04s both'; $el.style.opacity=0">
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

                    {{-- Réf transaction --}}
                    <div x-data x-init="$el.style.animation='slideUp .25s ease .08s both'; $el.style.opacity=0">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Référence transaction</label>
                        <div class="relative">
                            <x-heroicon-o-hashtag class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model="transaction_reference"
                                   placeholder="Ex: TXN-123456"
                                   class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                          hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                        </div>
                    </div>
                    @endif

                    {{-- Date limite si partiel / impayé --}}
                    @if($payment_status === 'partial' || $payment_status === 'unpaid')
                    <div class="{{ $errors->has('due_date') ? 'field-error' : '' }}"
                         x-data x-init="$el.style.animation='slideUp .25s ease both'; $el.style.opacity=0">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Date limite paiement</label>
                        <div class="relative">
                            <x-heroicon-o-clock class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model="due_date" type="date"
                                   class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                          hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Date à laquelle le solde doit être réglé</p>
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
                                  class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl outline-none
                                         hover:border-gray-300 focus:border-gray-400
                                         resize-none transition-all duration-150 leading-relaxed"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                <button wire:click="nextStep"
                        class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-gray-900 text-white text-sm
                               hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md
                               transition-all duration-150">
                    Suivant
                    <x-heroicon-o-arrow-right class="w-4 h-4"/>
                </button>
            </div>
        </div>
    </div>
    @endif


    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- Étape 2 : Produits                                            --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @if($step === 2)
    <div class="step-panel grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- ── Formulaire ajout ligne ─────────────────────────────── --}}
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden form-card h-fit">

            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <p class="text-sm font-medium text-gray-800">Ajouter un produit</p>
                <p class="text-xs text-gray-400 mt-0.5">Sélectionnez un modèle et renseignez les prix</p>
            </div>

            <div class="px-6 py-5 space-y-4">

                {{-- Modèle --}}
                <div class="{{ $errors->has('line_product_model_id') ? 'field-error' : ($line_product_model_id ? 'field-ok' : '') }}">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Modèle <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <x-heroicon-o-device-phone-mobile class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10"/>
                        <select wire:model.live="line_product_model_id"
                                class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                       hover:border-gray-300 focus:border-gray-400 transition-all duration-150 cursor-pointer">
                            <option value="">Choisir un modèle...</option>
                            @foreach($productModels as $pm)
                            <option value="{{ $pm['id'] }}">{{ $pm['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('line_product_model_id')
                    <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                @if($line_product_model_id)
                <div x-data x-init="
                    $el.style.opacity = 0; $el.style.transform = 'translateY(6px)';
                    requestAnimationFrame(() => {
                        $el.style.transition = 'opacity .25s ease, transform .25s ease';
                        $el.style.opacity = 1; $el.style.transform = 'translateY(0)';
                    })">

                    {{-- Prix --}}
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        @foreach([
                            ['field' => 'line_unit_purchase_price', 'label' => 'Prix achat'],
                            ['field' => 'line_unit_client_price',   'label' => 'Prix client'],
                            ['field' => 'line_unit_reseller_price', 'label' => 'Prix revendeur'],
                        ] as $price)
                        <div class="{{ $errors->has($price['field']) ? 'field-error' : '' }}">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                {{ $price['label'] }} <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <input wire:model="{{ $price['field'] }}" type="number"
                                       class="w-full h-9 pl-3 pr-8 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                              hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 select-none">
                                    {{ config('boutique.devise_symbole') }}
                                </span>
                            </div>
                            @error($price['field'])
                            <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                                <x-heroicon-o-exclamation-circle class="w-3 h-3 shrink-0"/>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                        @endforeach
                    </div>

                    {{-- Condition --}}
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Condition</label>
                        <select wire:model="line_condition"
                                class="w-full h-9 px-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                       hover:border-gray-300 focus:border-gray-400 transition-all duration-150 cursor-pointer">
                            @foreach($conditions as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sérialisé --}}
                    @if($line_is_serialized)
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-600 mb-2">Mode de saisie des identifiants</label>
                        <div class="flex gap-4 mb-3">
                            @foreach(['manual' => 'Manuel', 'bulk' => 'Copier-coller'] as $val => $lbl)
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <div class="relative w-4 h-4 shrink-0">
                                    <input type="radio" wire:model.live="line_imei_mode" value="{{ $val }}"
                                           class="sr-only peer"/>
                                    <div class="w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-gray-900 peer-checked:bg-gray-900
                                                transition-all duration-150 flex items-center justify-center">
                                        <div class="w-1.5 h-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-600 group-hover:text-gray-800 transition-colors">{{ $lbl }}</span>
                            </label>
                            @endforeach
                        </div>

                        @if($line_imei_mode === 'manual')
                        <div class="space-y-2">
                            @foreach($line_imei_list as $idx => $val)
                            <div class="flex gap-2" style="animation: lineIn .2s ease {{ $idx * 30 }}ms both">
                                <input wire:model="line_imei_list.{{ $idx }}"
                                       placeholder="IMEI ou numéro de série"
                                       class="flex-1 h-9 px-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                              hover:border-gray-300 focus:border-gray-400 font-mono transition-all duration-150"/>
                                @if(count($line_imei_list) > 1)
                                <button wire:click="removeImeiField({{ $idx }})"
                                        class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-300
                                               hover:bg-red-50 hover:text-red-500 transition-all duration-150 shrink-0">
                                    <x-heroicon-o-trash class="w-4 h-4"/>
                                </button>
                                @endif
                            </div>
                            @endforeach
                            <button wire:click="addImeiField"
                                    class="h-8 px-3 text-xs text-gray-500 hover:text-gray-800 border border-dashed border-gray-200
                                           hover:border-gray-400 rounded-xl hover:bg-gray-50 transition-all duration-150 w-full">
                                + Ajouter un champ
                            </button>
                        </div>
                        @else
                        <textarea wire:model="line_imei_bulk"
                                  rows="5"
                                  placeholder="Coller les IMEI / numéros de série (un par ligne)"
                                  class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                         hover:border-gray-300 focus:border-gray-400 font-mono
                                         resize-none transition-all duration-150 leading-relaxed"></textarea>
                        <p class="text-[11px] text-gray-400 mt-1">Un identifiant par ligne</p>
                        @endif
                    </div>
                    @else
                    {{-- Quantité --}}
                    <div class="{{ $errors->has('line_quantity') ? 'field-error' : '' }} mb-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Quantité <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <x-heroicon-o-hashtag class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model="line_quantity" type="number" min="1"
                                   class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                          hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                        </div>
                        @error('line_quantity')
                        <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                    @endif

                    {{-- Notes ligne --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes ligne</label>
                        <div class="relative">
                            <x-heroicon-o-chat-bubble-left class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model="line_notes" placeholder="Optionnel"
                                   class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white
                                          hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                <button wire:click="addLine"
                        class="inline-flex items-center justify-center gap-2 w-full h-9 rounded-xl bg-gray-900 text-white text-sm
                               hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md
                               transition-all duration-150">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Ajouter au bon de commande
                </button>
            </div>
        </div>

        {{-- ── Bon de commande ────────────────────────────────────── --}}
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden form-card">

            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-800">Bon de commande</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ count($items) }} ligne(s) ajoutée(s)</p>
                </div>
                @if(count($items) > 0)
                <span class="text-sm font-semibold text-gray-900">
                    {{ number_format(collect($items)->sum('line_total'), 0, ',', ' ') }}
                    <span class="text-xs font-normal text-gray-400">{{ config('boutique.devise_symbole') }}</span>
                </span>
                @endif
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($items as $index => $item)
                <div class="bc-line px-6 py-4" style="animation: lineIn .25s ease {{ $index * 40 }}ms both">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $item['model_name'] }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">
                                {{ $item['quantity'] }} unité(s) ×
                                <span class="font-medium">{{ number_format($item['unit_purchase_price'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
                            </p>
                            @if($item['is_serialized'] && !empty($item['identifiers']))
                            <div class="mt-1.5 flex flex-wrap gap-1">
                                @foreach($item['identifiers'] as $id)
                                <span class="bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded text-[10px] font-mono border border-gray-200">
                                    {{ $id }}
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-sm font-semibold text-gray-900">
                                {{ number_format($item['line_total'], 0, ',', ' ') }}
                                <span class="text-[11px] font-normal text-gray-400">{{ config('boutique.devise_symbole') }}</span>
                            </span>
                            <button wire:click="removeLine({{ $index }})"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300
                                           hover:bg-red-50 hover:text-red-500 transition-all duration-150">
                                <x-heroicon-o-trash class="w-3.5 h-3.5"/>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-16 px-6">
                    <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                        <x-heroicon-o-shopping-cart class="w-5 h-5 text-gray-300"/>
                    </div>
                    <p class="text-sm text-gray-400 font-medium">Aucune ligne ajoutée</p>
                    <p class="text-xs text-gray-300 mt-1">Sélectionnez un modèle et cliquez sur Ajouter</p>
                </div>
                @endforelse
            </div>

            @if(count($items) > 0)
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Total commande</span>
                    <span class="text-base font-bold text-gray-900">
                        {{ number_format(collect($items)->sum('line_total'), 0, ',', ' ') }}
                        {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Navigation étape 2 --}}
    <div class="flex items-center justify-between mt-5">
        <button wire:click="prevStep"
                class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600
                       hover:bg-gray-50 hover:border-gray-300 transition-all duration-150">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </button>
        <button wire:click="nextStep"
                @if(empty($items)) disabled @endif
                class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-gray-900 text-white text-sm
                       hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md
                       disabled:opacity-40 disabled:cursor-not-allowed disabled:translate-y-0 disabled:shadow-none
                       transition-all duration-150">
            Récapitulatif
            <x-heroicon-o-arrow-right class="w-4 h-4"/>
        </button>
    </div>
    @endif


    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- Étape 3 : Récapitulatif                                       --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @if($step === 3)
    <div class="step-panel space-y-4">

        {{-- Infos générales --}}
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden form-card">

            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <p class="text-sm font-medium text-gray-800">Récapitulatif de l'achat</p>
                <p class="text-xs text-gray-400 mt-0.5">Vérifiez les informations avant de valider</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-0 divide-x divide-gray-100">
                @php
                    $recap = [
                        ['label' => 'Fournisseur',       'value' => \App\Models\Supplier::find($supplier_id)?->name ?? '—'],
                        ['label' => 'Date',              'value' => \Carbon\Carbon::parse($purchase_date)->format('d/m/Y')],
                        ['label' => 'Statut paiement',   'value' => \App\Enums\PaymentStatus::from($payment_status)->label()],
                        ['label' => 'Total',             'value' => number_format(collect($items)->sum('line_total'), 0, ',', ' ') . ' ' . config('boutique.devise_symbole'), 'bold' => true],
                    ];
                @endphp
                @foreach($recap as $i => $r)
                <div class="px-6 py-5" style="animation: slideUp .25s ease {{ $i * 50 }}ms both">
                    <p class="text-[11px] text-gray-400 uppercase tracking-wider mb-1">{{ $r['label'] }}</p>
                    <p class="text-sm {{ isset($r['bold']) ? 'text-lg font-bold text-gray-900' : 'font-medium text-gray-800' }}">
                        {{ $r['value'] }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Lignes produits --}}
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden form-card">

            <div class="px-6 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <p class="text-sm font-medium text-gray-800">Lignes produits</p>
                <span class="text-[11px] text-gray-400">{{ count($items) }} ligne(s)</span>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($items as $i => $item)
                <div class="flex items-start justify-between gap-4 px-6 py-4 bc-line"
                     style="animation: lineIn .25s ease {{ $i * 40 }}ms both">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">{{ $item['model_name'] }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">
                            {{ $item['quantity'] }} unité(s) ·
                            Achat <span class="font-medium">{{ number_format($item['unit_purchase_price'], 0, ',', ' ') }}</span> ·
                            Client <span class="font-medium">{{ number_format($item['unit_client_price'], 0, ',', ' ') }}</span>
                            {{ config('boutique.devise_symbole') }}
                        </p>
                        @if(!empty($item['identifiers']))
                        <p class="text-[11px] text-gray-400 font-mono mt-1 truncate">
                            {{ implode(' · ', $item['identifiers']) }}
                        </p>
                        @endif
                    </div>
                    <p class="text-sm font-semibold text-gray-900 shrink-0">
                        {{ number_format($item['line_total'], 0, ',', ' ') }}
                        <span class="text-[11px] font-normal text-gray-400">{{ config('boutique.devise_symbole') }}</span>
                    </p>
                </div>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Total général</span>
                    <span class="text-base font-bold text-gray-900">
                        {{ number_format(collect($items)->sum('line_total'), 0, ',', ' ') }}
                        {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="flex items-center justify-between">
            <button wire:click="prevStep"
                    class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600
                           hover:bg-gray-50 hover:border-gray-300 transition-all duration-150">
                <x-heroicon-o-arrow-left class="w-4 h-4"/>
                Modifier
            </button>

            <button wire:click="save"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-75 cursor-wait"
                    class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-green-600 text-white text-sm
                           hover:bg-green-700 hover:-translate-y-0.5 hover:shadow-md hover:shadow-green-200/70
                           disabled:opacity-75 disabled:cursor-wait disabled:translate-y-0
                           transition-all duration-150">
                <svg wire:loading wire:target="save"
                     class="w-4 h-4" style="animation: spin .7s linear infinite"
                     fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                <x-heroicon-o-check wire:loading.remove wire:target="save" class="w-4 h-4"/>
                Valider l'achat
            </button>
        </div>
    </div>
    @endif

</div>
