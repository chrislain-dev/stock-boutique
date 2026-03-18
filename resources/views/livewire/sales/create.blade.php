<div>
    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim-1 { animation: slideUp 0.35s cubic-bezier(.16,1,.3,1) 0.00s both; }
        .anim-2 { animation: slideUp 0.35s cubic-bezier(.16,1,.3,1) 0.06s both; }
        .anim-3 { animation: slideUp 0.35s cubic-bezier(.16,1,.3,1) 0.12s both; }

        .step-line { height: 2px; background: #f4f4f5; flex: 1; margin: 0 12px; transition: background 0.3s ease; }
        .step-line.done { background: var(--boutique-primary, #18181b); }

        .customer-option {
            flex: 1; padding: 16px; border: 1.5px solid #f4f4f5; border-radius: 12px;
            cursor: pointer; transition: all 0.15s ease; background: #fff;
        }
        .customer-option:hover { border-color: #d4d4d8; }
        .customer-option.active {
            border-color: var(--boutique-primary, #18181b);
            background: color-mix(in srgb, var(--boutique-primary, #18181b) 4%, white);
        }

        .cart-item {
            background: #fafafa; border: 1px solid #f4f4f5; border-radius: 10px;
            padding: 14px; transition: border-color 0.15s ease;
        }
        .cart-item:hover { border-color: #e4e4e7; }

        .recap-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; }
        .recap-row + .recap-row { border-top: 1px solid #f9f9f9; }

        .section-divider {
            display: flex; align-items: center; gap: 12px; margin: 16px 0;
        }
        .section-divider::before, .section-divider::after {
            content: ''; flex: 1; height: 1px; background: #f4f4f5;
        }
    </style>

    {{-- Header --}}
    <div class="anim-1 flex items-end justify-between mb-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-1">Ventes</p>
            <h1 class="text-3xl font-semibold tracking-tight text-zinc-900">Nouvelle vente</h1>
        </div>
        <a href="{{ route('sales.index') }}"
            class="flex items-center gap-1.5 text-xs font-medium text-zinc-500 hover:text-zinc-900 bg-white border border-zinc-200 rounded-lg px-3 py-2 transition-colors">
            <x-mary-icon name="o-x-mark" class="w-3.5 h-3.5" />
            Annuler
        </a>
    </div>

    {{-- Stepper --}}
    <div class="anim-2 flex items-center mb-10">
        @foreach([1 => 'Client', 2 => 'Produits', 3 => 'Paiement', 4 => 'Récapitulatif'] as $num => $label)
        <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
            <div class="flex items-center gap-2.5 shrink-0">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300
                    {{ $step > $num
                        ? 'bg-emerald-500 text-white'
                        : ($step === $num
                            ? 'text-white'
                            : 'bg-zinc-100 text-zinc-400') }}"
                    style="{{ $step === $num ? 'background: var(--boutique-primary, #18181b);' : '' }}">
                    @if($step > $num)
                        <x-mary-icon name="o-check" class="w-3.5 h-3.5" />
                    @else
                        {{ $num }}
                    @endif
                </div>
                <span class="text-sm font-medium transition-colors duration-300
                    {{ $step === $num ? 'text-zinc-900' : ($step > $num ? 'text-emerald-600' : 'text-zinc-400') }}">
                    {{ $label }}
                </span>
            </div>
            @if(!$loop->last)
            <div class="step-line {{ $step > $num ? 'done' : '' }}"></div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ── Étape 1 : Client ────────────────────────────────── --}}
    @if($step === 1)
    <div class="anim-3 max-w-2xl">
        <div class="bg-white rounded-xl border border-zinc-100 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-zinc-900 mb-5">Type de client</h2>

            <div class="flex gap-3 mb-5">
                <label class="customer-option {{ $customer_type === 'client' ? 'active' : '' }}">
                    <input type="radio" wire:model.live="customer_type" value="client" class="hidden" />
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-zinc-100 flex items-center justify-center shrink-0">
                            <x-mary-icon name="o-user" class="w-4 h-4 text-zinc-600" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">Client particulier</p>
                            <p class="text-xs text-zinc-400 mt-0.5">Walk-in ou client enregistré</p>
                        </div>
                    </div>
                </label>
                <label class="customer-option {{ $customer_type === 'reseller' ? 'active' : '' }}">
                    <input type="radio" wire:model.live="customer_type" value="reseller" class="hidden" />
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-zinc-100 flex items-center justify-center shrink-0">
                            <x-mary-icon name="o-building-storefront" class="w-4 h-4 text-zinc-600" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">Revendeur</p>
                            <p class="text-xs text-zinc-400 mt-0.5">Prix revendeur appliqué</p>
                        </div>
                    </div>
                </label>
            </div>

            @if($customer_type === 'reseller')
            <x-mary-select
                label="Revendeur"
                wire:model="reseller_id"
                :options="$resellers"
                option-value="id"
                option-label="name"
                placeholder="Sélectionner un revendeur"
                icon="o-building-storefront"
            />
            @else
            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="Nom du client" wire:model="customer_name" placeholder="Optionnel" icon="o-user" />
                <x-mary-input label="Téléphone" wire:model="customer_phone" placeholder="Optionnel" icon="o-phone" />
            </div>
            @endif

            <div class="flex justify-end mt-6">
                <button wire:click="nextStep"
                    class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-md"
                    style="background: var(--boutique-primary, #18181b);">
                    Suivant
                    <x-mary-icon name="o-arrow-right" class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Étape 2 : Produits ──────────────────────────────── --}}
    @if($step === 2)
    <div class="anim-3 grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Recherche --}}
        <div class="bg-white rounded-xl border border-zinc-100 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-zinc-900 mb-5">Ajouter un produit</h2>

            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-2">Par IMEI / Série</p>
            <div class="flex gap-2 mb-1">
                <div class="relative flex-1">
                    <x-mary-icon name="o-qr-code" class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input
                        wire:model="search_imei"
                        wire:keydown.enter="searchByImei"
                        type="text"
                        placeholder="Scanner ou saisir IMEI..."
                        class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:border-zinc-400 transition-colors"
                    />
                </div>
                <button wire:click="searchByImei"
                    class="px-3 py-2 text-white rounded-lg text-sm transition-all hover:opacity-90"
                    style="background: var(--boutique-primary, #18181b);">
                    <x-mary-icon name="o-magnifying-glass" class="w-4 h-4" />
                </button>
            </div>
            @error('search_imei')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            @if($search_error)
                <p class="text-red-500 text-xs mt-1">{{ $search_error }}</p>
            @endif

            <div class="section-divider my-5">
                <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">OU</span>
            </div>

            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-2">Choisir dans la liste</p>
            <x-mary-select
                wire:model.live="search_product_id"
                :options="$availableProducts"
                option-value="id"
                option-label="name"
                placeholder="Sélectionner un produit..."
                searchable
            />
        </div>

        {{-- Panier --}}
        <div class="bg-white rounded-xl border border-zinc-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-semibold text-zinc-900">Panier</h2>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-600">
                    {{ count($items) }} article(s)
                </span>
            </div>

            @forelse($items as $index => $item)
            <div class="cart-item mb-2">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-sm font-medium text-zinc-900">{{ $item['name'] }}</p>
                        <p class="text-xs font-mono text-zinc-400 mt-0.5">{{ $item['identifier'] }}</p>
                    </div>
                    <button wire:click="removeItem({{ $index }})"
                        class="p-1 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                        <x-mary-icon name="o-trash" class="w-3.5 h-3.5" />
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <p class="text-xs text-zinc-400 mb-1">Prix unitaire</p>
                        <input type="number"
                            class="w-full px-2 py-1.5 text-xs bg-white border border-zinc-200 rounded-lg focus:outline-none focus:border-zinc-400"
                            value="{{ $item['unit_price'] }}"
                            wire:change="updateItemPrice({{ $index }}, 'unit_price', $event.target.value)" />
                    </div>
                    <div>
                        <p class="text-xs text-zinc-400 mb-1">Remise</p>
                        <input type="number"
                            class="w-full px-2 py-1.5 text-xs bg-white border border-zinc-200 rounded-lg focus:outline-none focus:border-zinc-400"
                            value="{{ $item['discount'] }}"
                            wire:change="updateItemPrice({{ $index }}, 'discount', $event.target.value)" />
                    </div>
                    <div>
                        <p class="text-xs text-zinc-400 mb-1">Total ligne</p>
                        <p class="text-sm font-bold text-zinc-900 pt-1">
                            {{ number_format($item['line_total'], 0, ',', ' ') }}
                        </p>
                    </div>
                </div>
            </div>
            @empty
            <div class="py-12 text-center">
                <x-mary-icon name="o-shopping-cart" class="w-8 h-8 text-zinc-200 mx-auto mb-2" />
                <p class="text-sm text-zinc-400">Aucun produit ajouté.</p>
            </div>
            @endforelse

            @if(count($items) > 0)
            <div class="border-t border-zinc-100 mt-3 pt-3 flex justify-between items-center">
                <span class="text-sm font-medium text-zinc-600">Total</span>
                <span class="text-lg font-bold text-zinc-900">
                    {{ number_format(collect($items)->sum('line_total'), 0, ',', ' ') }}
                    <span class="text-sm font-normal text-zinc-400">{{ config('boutique.devise_symbole') }}</span>
                </span>
            </div>
            @endif
        </div>
    </div>

    <div class="flex justify-between mt-5">
        <button wire:click="prevStep"
            class="flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900 bg-white border border-zinc-200 rounded-lg px-4 py-2 transition-colors">
            <x-mary-icon name="o-arrow-left" class="w-4 h-4" />
            Retour
        </button>
        <button wire:click="nextStep" @if(empty($items)) disabled @endif
            class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-md disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:translate-y-0"
            style="background: var(--boutique-primary, #18181b);">
            Paiement
            <x-mary-icon name="o-arrow-right" class="w-4 h-4" />
        </button>
    </div>
    @endif

    {{-- ── Étape 3 : Paiement ──────────────────────────────── --}}
    @if($step === 3)
    <div class="anim-3 max-w-2xl">
        <div class="bg-white rounded-xl border border-zinc-100 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-zinc-900 mb-5">Paiement</h2>

            {{-- Total --}}
            <div class="flex items-center justify-between p-4 rounded-xl mb-5"
                style="background: color-mix(in srgb, var(--boutique-primary, #18181b) 5%, white); border: 1px solid color-mix(in srgb, var(--boutique-primary, #18181b) 15%, white);">
                <span class="text-sm font-medium text-zinc-700">Total à payer</span>
                <span class="text-2xl font-bold tracking-tight" style="color: var(--boutique-primary, #18181b);">
                    {{ number_format($this->getTotal(), 0, ',', ' ') }}
                    <span class="text-sm font-normal text-zinc-400">{{ config('boutique.devise_symbole') }}</span>
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-mary-select
                    label="Mode de paiement"
                    wire:model.live="payment_method"
                    :options="$paymentMethods"
                    option-value="id"
                    option-label="name"
                    icon="o-credit-card"
                />
                <x-mary-input
                    label="Montant payé"
                    wire:model.live="paid_amount"
                    type="number"
                    icon="o-banknotes"
                    :suffix="config('boutique.devise_symbole')"
                />

                @if($payment_method === 'mobile_money')
                <x-mary-input label="Numéro Mobile Money" wire:model="mobile_number" icon="o-device-phone-mobile" />
                <x-mary-input label="Référence transaction" wire:model="transaction_reference" icon="o-hashtag" />
                @endif

                @if(in_array($payment_method, ['bank_transfer', 'cheque']))
                <x-mary-input label="Banque" wire:model="bank_name" icon="o-building-library" />
                <x-mary-input label="Référence" wire:model="transaction_reference" icon="o-hashtag" />
                @endif

                @php $remaining = $this->getTotal() - (float)$paid_amount; @endphp
                @if($remaining > 0)
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-100 rounded-lg mb-3">
                        <x-mary-icon name="o-clock" class="w-4 h-4 text-amber-500 shrink-0" />
                        <p class="text-sm text-amber-700">
                            Reliquat : <span class="font-bold">{{ number_format($remaining, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
                        </p>
                    </div>
                    <x-mary-input label="Date limite de solde" wire:model="due_date" type="date" icon="o-calendar" />
                </div>
                @endif
            </div>

            {{-- Troc --}}
            <div class="border-t border-zinc-100 mt-5 pt-5">
                <label class="flex items-center gap-3 cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" wire:model.live="is_trade_in" class="sr-only" />
                        <div class="w-10 h-6 rounded-full transition-colors {{ $is_trade_in ? '' : 'bg-zinc-200' }}"
                            style="{{ $is_trade_in ? 'background: var(--boutique-primary, #18181b);' : '' }}">
                            <div class="w-4 h-4 bg-white rounded-full shadow transition-transform mt-1
                                {{ $is_trade_in ? 'translate-x-5' : 'translate-x-1' }}"></div>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-zinc-900">Vente avec troc</p>
                        <p class="text-xs text-zinc-400">Produit repris en échange</p>
                    </div>
                </label>

                @if($is_trade_in)
                <div class="mt-4 p-4 bg-amber-50 border border-amber-100 rounded-xl space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Produit repris</p>
                    <div class="flex gap-2">
                        <input wire:model="trade_in_imei" type="text"
                            placeholder="IMEI du produit repris"
                            class="flex-1 px-3 py-2 text-sm bg-white border border-amber-200 rounded-lg focus:outline-none focus:border-amber-400" />
                        <button wire:click="searchTradeIn"
                            class="px-3 py-2 bg-amber-500 text-white rounded-lg text-sm hover:bg-amber-600 transition-colors">
                            <x-mary-icon name="o-magnifying-glass" class="w-4 h-4" />
                        </button>
                    </div>
                    @error('trade_in_imei')
                        <p class="text-red-500 text-xs">{{ $message }}</p>
                    @enderror
                    <div class="grid grid-cols-2 gap-3">
                        <x-mary-input label="Valeur estimée" wire:model.live="trade_in_value" type="number" :suffix="config('boutique.devise_symbole')" />
                        <x-mary-input label="État du produit" wire:model="trade_in_notes" placeholder="Ex: écran fissuré..." />
                    </div>
                </div>
                @endif
            </div>

            <x-mary-textarea label="Notes" wire:model="notes" rows="2" placeholder="Remarques sur cette vente..." class="mt-4" />

            <div class="flex justify-between mt-6">
                <button wire:click="prevStep"
                    class="flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900 bg-white border border-zinc-200 rounded-lg px-4 py-2 transition-colors">
                    <x-mary-icon name="o-arrow-left" class="w-4 h-4" />
                    Retour
                </button>
                <button wire:click="nextStep"
                    class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-md"
                    style="background: var(--boutique-primary, #18181b);">
                    Récapitulatif
                    <x-mary-icon name="o-arrow-right" class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Étape 4 : Récapitulatif ─────────────────────────── --}}
    @if($step === 4)
    <div class="anim-3 max-w-2xl">
        <div class="bg-white rounded-xl border border-zinc-100 shadow-sm overflow-hidden">

            {{-- Header recap --}}
            <div class="px-6 py-5 border-b border-zinc-50">
                <div class="section-header flex items-center gap-2">
                    <span class="w-1 h-5 rounded-full" style="background: var(--boutique-primary, #18181b);"></span>
                    <h2 class="text-sm font-semibold text-zinc-900">Récapitulatif de la vente</h2>
                </div>
            </div>

            {{-- Infos client / paiement --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-0">
                @php
                    $recapItems = [
                        ['label' => 'Client', 'value' => $customer_type === 'reseller'
                            ? (\App\Models\Reseller::find($reseller_id)?->name ?? '—')
                            : ($customer_name ?: 'Anonyme')],
                        ['label' => 'Paiement', 'value' => \App\Enums\PaymentMethod::from($payment_method)->label()],
                        ['label' => 'Montant payé', 'value' => number_format((float)$paid_amount, 0, ',', ' ') . ' ' . config('boutique.devise_symbole')],
                        ['label' => 'Total net', 'value' => number_format($this->getTotal(), 0, ',', ' ') . ' ' . config('boutique.devise_symbole')],
                    ];
                @endphp
                @foreach($recapItems as $i => $ri)
                <div class="p-4 {{ $i < 3 ? 'border-r border-zinc-50' : '' }} border-b border-zinc-50">
                    <p class="text-xs text-zinc-400 mb-1">{{ $ri['label'] }}</p>
                    <p class="text-sm font-semibold text-zinc-900">{{ $ri['value'] }}</p>
                </div>
                @endforeach
            </div>

            {{-- Produits --}}
            <div class="px-6 py-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-3">Produits</p>
                @foreach($items as $item)
                <div class="recap-row">
                    <div>
                        <p class="text-sm font-medium text-zinc-900">{{ $item['name'] }}</p>
                        <p class="text-xs font-mono text-zinc-400">{{ $item['identifier'] }}</p>
                        @if($item['discount'] > 0)
                        <p class="text-xs text-amber-600 mt-0.5">
                            Remise : {{ number_format($item['discount'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                        </p>
                        @endif
                    </div>
                    <span class="text-sm font-bold text-zinc-900">
                        {{ number_format($item['line_total'], 0, ',', ' ') }}
                        <span class="text-xs font-normal text-zinc-400">{{ config('boutique.devise_symbole') }}</span>
                    </span>
                </div>
                @endforeach

                @if($is_trade_in && (float)$trade_in_value > 0)
                <div class="recap-row">
                    <span class="text-sm text-amber-600 font-medium">Troc déduit</span>
                    <span class="text-sm font-bold text-amber-600">
                        - {{ number_format((float)$trade_in_value, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
                @endif

                <div class="flex justify-between items-center pt-3 mt-2 border-t border-zinc-100">
                    <span class="text-sm font-semibold text-zinc-700">Total</span>
                    <span class="text-xl font-bold tracking-tight" style="color: var(--boutique-primary, #18181b);">
                        {{ number_format($this->getTotal(), 0, ',', ' ') }}
                        <span class="text-sm font-normal text-zinc-400">{{ config('boutique.devise_symbole') }}</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="flex justify-between mt-5">
            <button wire:click="prevStep"
                class="flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900 bg-white border border-zinc-200 rounded-lg px-4 py-2 transition-colors">
                <x-mary-icon name="o-arrow-left" class="w-4 h-4" />
                Modifier
            </button>
            <button wire:click="save" wire:loading.attr="disabled"
                class="flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white rounded-lg transition-all hover:-translate-y-0.5 hover:shadow-lg bg-emerald-600 hover:bg-emerald-700">
                <x-mary-icon name="o-check" class="w-4 h-4" />
                Valider la vente
                <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
            </button>
        </div>
    </div>
    @endif
</div>
