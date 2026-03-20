<div x-data="saleCreate()" x-init="init()">

    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes popIn {
            0%   { opacity: 0; transform: scale(0.94) translateY(6px); }
            60%  { transform: scale(1.01) translateY(0); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        @keyframes cartItemIn {
            from { opacity: 0; transform: translateX(10px) scale(0.98); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }
        @keyframes checkPop {
            0%   { transform: scale(0); }
            60%  { transform: scale(1.15); }
            100% { transform: scale(1); }
        }
        @keyframes errorShake {
            0%,100% { transform: translateX(0); }
            20%     { transform: translateX(-4px); }
            40%     { transform: translateX(4px); }
            60%     { transform: translateX(-3px); }
            80%     { transform: translateX(3px); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes pulseRing {
            0%   { box-shadow: 0 0 0 0 rgba(24,24,27,.15); }
            70%  { box-shadow: 0 0 0 6px rgba(24,24,27,0); }
            100% { box-shadow: 0 0 0 0 rgba(24,24,27,0); }
        }

        .anim-header { animation: slideUp .4s cubic-bezier(.22,1,.36,1) both; }
        .anim-stepper { animation: slideUp .4s cubic-bezier(.22,1,.36,1) .07s both; }
        .anim-panel-right { animation: slideInRight .35s cubic-bezier(.22,1,.36,1) both; }
        .anim-panel-left  { animation: slideInLeft  .35s cubic-bezier(.22,1,.36,1) both; }
        .anim-panel-up    { animation: slideUp      .35s cubic-bezier(.22,1,.36,1) both; }

        .cart-item-enter { animation: cartItemIn .25s cubic-bezier(.22,1,.36,1) both; }
        .check-pop       { animation: checkPop .3s cubic-bezier(.34,1.56,.64,1) both; }
        .error-shake     { animation: errorShake .35s ease both; }

        /* Step line */
        .step-connector {
            flex: 1; height: 1.5px; margin: 0 10px;
            background: #e4e4e7;
            transition: background .5s ease;
            position: relative; overflow: hidden;
        }
        .step-connector.done { background: #18181b; }
        .step-connector.done::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.4), transparent);
            animation: shimmerPass .6s ease forwards;
        }
        @keyframes shimmerPass {
            from { transform: translateX(-100%); }
            to   { transform: translateX(100%); }
        }

        /* Customer option cards */
        .c-option {
            flex: 1; padding: 14px 16px;
            border: 1.5px solid #f0ede9; border-radius: 14px;
            cursor: pointer; transition: all .18s ease; background: #fff;
        }
        .c-option:hover { border-color: #d4d4d8; background: #fafafa; }
        .c-option.active {
            border-color: #18181b;
            background: #fafafa;
            box-shadow: 0 0 0 3px rgba(24,24,27,.06);
        }

        /* Cart items */
        .cart-item {
            background: #fafafa; border: 1px solid #f0ede9;
            border-radius: 12px; padding: 14px 16px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .cart-item:hover { border-color: #e4e4e7; box-shadow: 0 2px 8px rgba(0,0,0,.04); }

        /* Mini input cart */
        .cart-input {
            width: 100%; padding: 6px 10px; font-size: 12px;
            border: 1px solid #e4e4e7; border-radius: 8px;
            background: #fff; outline: none; transition: border-color .15s ease;
        }
        .cart-input:focus { border-color: #a1a1aa; box-shadow: 0 0 0 2px rgba(24,24,27,.06); }

        /* Form inputs */
        .f-input, .f-select {
            width: 100%; height: 38px; padding: 0 12px;
            font-size: 14px; border: 1.5px solid #e4e4e7; border-radius: 10px;
            background: #fff; outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .f-input.pl { padding-left: 38px; }
        .f-input.pr { padding-right: 44px; }
        .f-input:focus, .f-select:focus {
            border-color: #18181b;
            box-shadow: 0 0 0 3px rgba(24,24,27,.07);
        }
        .f-label { display: block; font-size: 11px; font-weight: 600; color: #71717a; margin-bottom: 6px; letter-spacing: .02em; }
        .f-label span { color: #ef4444; }

        .f-error { font-size: 11px; color: #ef4444; margin-top: 4px; display: flex; align-items: center; gap: 4px; }

        /* Total bar */
        .total-pill {
            display: inline-flex; align-items: center; gap: 8px;
            background: #18181b; color: #fff;
            border-radius: 12px; padding: 10px 18px;
        }

        /* Recap rows */
        .rrow { display: flex; justify-content: space-between; align-items: center; padding: 11px 0; }
        .rrow + .rrow { border-top: 1px solid #f9f9f9; }

        /* Section label */
        .sec-label {
            font-size: 10px; font-weight: 700; letter-spacing: .08em;
            text-transform: uppercase; color: #a1a1aa; margin-bottom: 12px;
        }

        button:active:not(:disabled) { transform: scale(0.97) !important; }

        /* Scrollbar fine dans le panier */
        .cart-scroll { max-height: 300px; overflow-y: auto; }
        .cart-scroll::-webkit-scrollbar { width: 3px; }
        .cart-scroll::-webkit-scrollbar-track { background: transparent; }
        .cart-scroll::-webkit-scrollbar-thumb { background: #e4e4e7; border-radius: 99px; }
        @media (min-width: 640px) {
            .cart-scroll { max-height: 420px; }
        }
    </style>

    {{-- ─── Header ───────────────────────────────────────────────────── --}}
    <div class="anim-header flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6 sm:mb-8">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-1.5">Ventes</p>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Nouvelle vente</h1>
        </div>
        <a href="{{ route('sales.index') }}"
        class="inline-flex items-center gap-1.5 h-9 text-sm font-medium text-gray-500
                hover:text-gray-900 bg-white border border-gray-200 rounded-xl px-4
                hover:border-gray-300 hover:bg-gray-50 transition-all duration-150
                self-start sm:self-auto shrink-0">
            <x-heroicon-o-x-mark class="w-3.5 h-3.5 shrink-0"/>
            Annuler
        </a>
    </div>

    {{-- ─── Stepper ──────────────────────────────────────────────────── --}}
    <div class="anim-stepper flex items-center mb-8 sm:mb-10">
        @foreach([1 => 'Client', 2 => 'Produits', 3 => 'Paiement', 4 => 'Récapitulatif'] as $num => $label)
        <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
            <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                <div class="relative w-7 h-7 sm:w-8 sm:h-8 shrink-0">
                    @if($step > $num)
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gray-900 flex items-center justify-center check-pop">
                        <x-heroicon-o-check class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-white"/>
                    </div>
                    @elseif($step === $num)
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gray-900 flex items-center justify-center ring-4 ring-gray-900/10 transition-all duration-300">
                        <span class="text-xs font-bold text-white">{{ $num }}</span>
                    </div>
                    @else
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gray-100 border-2 border-gray-200 flex items-center justify-center transition-all duration-300">
                        <span class="text-xs font-medium text-gray-400">{{ $num }}</span>
                    </div>
                    @endif
                </div>

                {{-- Label : caché sur xs, visible sm+ --}}
                <span class="hidden sm:inline text-sm font-medium transition-colors duration-200
                    {{ $step === $num ? 'text-gray-900' : ($step > $num ? 'text-gray-400' : 'text-gray-300') }}">
                    {{ $label }}
                </span>

                {{-- Label court sur mobile : seulement pour l'étape active --}}
                @if($step === $num)
                <span class="sm:hidden text-xs font-semibold text-gray-900">{{ $label }}</span>
                @endif
            </div>

            @if(!$loop->last)
            <div class="step-connector {{ $step > $num ? 'done' : '' }}"></div>
            @endif
        </div>
        @endforeach
    </div>


    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- Étape 1 : Client                                                  --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    @if($step === 1)
    <div class="anim-panel-up max-w-xl">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            <div class="px-6 py-5 border-b border-gray-50">
                <p class="text-sm font-semibold text-gray-900">Type de client</p>
                <p class="text-xs text-gray-400 mt-0.5">Sélectionnez le type pour appliquer les bons tarifs</p>
            </div>

            <div class="px-6 py-5 space-y-5">

                {{-- Sélection type --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <label class="c-option {{ $customer_type === 'client' ? 'active' : '' }}">
                        <input type="radio" wire:model.live="customer_type" value="client" class="sr-only"/>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl {{ $customer_type === 'client' ? 'bg-gray-900' : 'bg-gray-100' }} flex items-center justify-center shrink-0 transition-colors duration-200">
                                <x-heroicon-o-user class="w-4 h-4 {{ $customer_type === 'client' ? 'text-white' : 'text-gray-500' }}"/>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Client particulier</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Prix public standard</p>
                            </div>
                        </div>
                    </label>

                    <label class="c-option {{ $customer_type === 'reseller' ? 'active' : '' }}">
                        <input type="radio" wire:model.live="customer_type" value="reseller" class="sr-only"/>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl {{ $customer_type === 'reseller' ? 'bg-violet-600' : 'bg-gray-100' }} flex items-center justify-center shrink-0 transition-colors duration-200">
                                <x-heroicon-o-building-storefront class="w-4 h-4 {{ $customer_type === 'reseller' ? 'text-white' : 'text-gray-500' }}"/>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Revendeur</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Prix revendeur appliqué</p>
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Champs selon type --}}
                @if($customer_type === 'reseller')
                <div x-data x-init="
                    $el.style.opacity=0; $el.style.transform='translateY(4px)';
                    requestAnimationFrame(() => {
                        $el.style.transition='opacity .2s ease, transform .2s ease';
                        $el.style.opacity=1; $el.style.transform='translateY(0)';
                    })">
                    <label class="f-label">Revendeur <span>*</span></label>
                    <div class="relative">
                        <x-heroicon-o-building-storefront class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10"/>
                        <select wire:model="reseller_id" class="f-select" style="padding-left: 38px;">
                            <option value="">Sélectionner un revendeur...</option>
                            @foreach($resellers as $r)
                            <option value="{{ $r['id'] }}">{{ $r['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('reseller_id')
                    <p class="f-error">
                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                @else
                <div x-data x-init="
                    $el.style.opacity=0; $el.style.transform='translateY(4px)';
                    requestAnimationFrame(() => {
                        $el.style.transition='opacity .2s ease, transform .2s ease';
                        $el.style.opacity=1; $el.style.transform='translateY(0)';
                    })" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="f-label">Nom du client</label>
                        <div class="relative">
                            <x-heroicon-o-user class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model="customer_name" placeholder="Optionnel" class="f-input pl"/>
                        </div>
                    </div>
                    <div>
                        <label class="f-label">Téléphone</label>
                        <div class="relative">
                            <x-heroicon-o-phone class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model="customer_phone" placeholder="Optionnel" class="f-input pl font-mono"/>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t border-gray-50 flex justify-end">
                <button wire:click="nextStep"
                        class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-gray-900 text-white text-sm font-medium
                               hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md transition-all duration-150">
                    Suivant
                    <x-heroicon-o-arrow-right class="w-4 h-4"/>
                </button>
            </div>
        </div>
    </div>
    @endif


    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- Étape 2 : Produits                                                --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    @if($step === 2)
    <div class="anim-panel-up grid grid-cols-1 lg:grid-cols-5 gap-4">

        {{-- ── Recherche (2/5) ──────────────────────────────────────── --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-fit">

            <div class="px-5 py-4 border-b border-gray-50">
                <p class="text-sm font-semibold text-gray-900">Ajouter un produit</p>
                <p class="text-xs text-gray-400 mt-0.5">Scan IMEI ou sélection dans la liste</p>
            </div>

            <div class="px-5 py-5 space-y-5">

                {{-- IMEI --}}
                <div>
                    <p class="f-label">Scanner / Saisir IMEI</p>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <x-heroicon-o-qr-code class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <svg wire:loading wire:target="searchByImei"
                                 class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                                 style="animation: spin .7s linear infinite" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                            </svg>
                            <input wire:model="search_imei"
                                   wire:keydown.enter="searchByImei"
                                   type="text"
                                   placeholder="Ex: 35xxxxxxxxxx"
                                   class="f-input pl font-mono"
                                   autofocus/>
                        </div>
                        <button wire:click="searchByImei"
                                class="h-9.5 w-10 rounded-xl bg-gray-900 text-white flex items-center justify-center
                                       hover:bg-gray-800 transition-colors duration-150 shrink-0">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4"/>
                        </button>
                    </div>
                    @error('search_imei')
                    <p class="f-error error-shake">
                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                        {{ $message }}
                    </p>
                    @enderror
                    @if($search_error)
                    <p class="f-error">
                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>
                        {{ $search_error }}
                    </p>
                    @endif
                    <p class="text-[10px] text-gray-300 mt-1.5">Appuyez sur Entrée pour rechercher</p>
                </div>

                {{-- Séparateur --}}
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-100"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-white px-3 text-[11px] font-bold uppercase tracking-widest text-gray-300">ou</span>
                    </div>
                </div>

                {{-- Sélection liste --}}
                <div>
                    <label class="f-label">Choisir dans la liste</label>
                    <select wire:model.live="search_product_id" class="f-select">
                        <option value="">Sélectionner un produit...</option>
                        @foreach($availableProducts as $p)
                        <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ── Panier (3/5) ─────────────────────────────────────────── --}}
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">

            <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Panier</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ count($items) }} article(s) sélectionné(s)</p>
                </div>
                @if(count($items) > 0)
                <div class="total-pill">
                    <span class="text-[11px] text-gray-400">Total</span>
                    <span class="text-sm font-bold">
                        {{ number_format(collect($items)->sum('line_total'), 0, ',', ' ') }}
                        <span class="text-xs font-normal text-gray-500">{{ config('boutique.devise_symbole') }}</span>
                    </span>
                </div>
                @endif
            </div>

            <div class="flex-1 cart-scroll p-5">
                @forelse($items as $index => $item)
                <div class="cart-item mb-2.5 cart-item-enter"
                     style="animation-delay: {{ $index * 40 }}ms">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0 pr-2">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</p>
                            <p class="text-[11px] font-mono text-gray-400 mt-0.5">{{ $item['identifier'] }}</p>
                        </div>
                        <button wire:click="removeItem({{ $index }})"
                                class="w-6 h-6 rounded-lg flex items-center justify-center text-gray-300
                                       hover:bg-red-50 hover:text-red-500 transition-all duration-150 shrink-0 mt-0.5">
                            <x-heroicon-o-x-mark class="w-3.5 h-3.5"/>
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Prix</p>
                            <div class="relative">
                                <input type="number" class="cart-input pr-6"
                                       value="{{ $item['unit_price'] }}"
                                       wire:change="updateItemPrice({{ $index }}, 'unit_price', $event.target.value)"/>
                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">{{ config('boutique.devise_symbole') }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Remise</p>
                            <div class="relative">
                                <input type="number" class="cart-input pr-6"
                                       value="{{ $item['discount'] }}"
                                       wire:change="updateItemPrice({{ $index }}, 'discount', $event.target.value)"/>
                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">{{ config('boutique.devise_symbole') }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Total</p>
                            <p class="text-sm font-bold text-gray-900 pt-1">
                                {{ number_format($item['line_total'], 0, ',', ' ') }}
                                <span class="text-[10px] font-normal text-gray-400">{{ config('boutique.devise_symbole') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                        <x-heroicon-o-shopping-cart class="w-6 h-6 text-gray-300"/>
                    </div>
                    <p class="text-sm font-medium text-gray-400">Panier vide</p>
                    <p class="text-xs text-gray-300 mt-1">Scannez un IMEI ou choisissez dans la liste</p>
                </div>
                @endforelse
            </div>

            @if(count($items) > 0)
            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/60">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500">Total panier</span>
                    <span class="text-lg font-bold tracking-tight text-gray-900">
                        {{ number_format(collect($items)->sum('line_total'), 0, ',', ' ') }}
                        <span class="text-sm font-normal text-gray-400">{{ config('boutique.devise_symbole') }}</span>
                    </span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="flex items-center justify-between mt-5 gap-3">
        <button wire:click="prevStep"
                class="inline-flex items-center gap-1.5 h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600
                    hover:bg-gray-50 hover:border-gray-300 transition-all duration-150">
            <x-heroicon-o-arrow-left class="w-4 h-4"/>
            Retour
        </button>
        <button wire:click="nextStep"
                @if(empty($items)) disabled @endif
                class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-gray-900 text-white text-sm font-medium
                    hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md
                    disabled:opacity-35 disabled:cursor-not-allowed disabled:translate-y-0 disabled:shadow-none
                    transition-all duration-150">
            Paiement
            <x-heroicon-o-arrow-right class="w-4 h-4"/>
        </button>
    </div>
    @endif


    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- Étape 3 : Paiement                                                --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    @if($step === 3)
    <div class="anim-panel-up max-w-xl">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            {{-- Total banner --}}
            <div class="px-6 py-5 border-b border-gray-50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Paiement</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ count($items) }} article(s)</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-0.5">Total à payer</p>
                        <p class="text-2xl font-bold tracking-tight text-gray-900">
                            {{ number_format($this->getTotal(), 0, ',', ' ') }}
                            <span class="text-sm font-normal text-gray-400">{{ config('boutique.devise_symbole') }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5 space-y-4">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Mode de paiement --}}
                    <div>
                        <label class="f-label">Mode de paiement</label>
                        <div class="relative">
                            <x-heroicon-o-credit-card class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10"/>
                            <select wire:model.live="payment_method" class="f-select" style="padding-left: 38px;">
                                @foreach($paymentMethods as $pm)
                                <option value="{{ $pm['id'] }}">{{ $pm['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Montant payé --}}
                    <div>
                        <label class="f-label">Montant payé</label>
                        <div class="relative">
                            <x-heroicon-o-banknotes class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model.live="paid_amount" type="number"
                                   class="f-input pl pr" placeholder="0"/>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 select-none">
                                {{ config('boutique.devise_symbole') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Champs Mobile Money --}}
                @if($payment_method === 'mobile_money')
                <div x-data x-init="
                    $el.style.opacity=0; $el.style.transform='translateY(4px)';
                    requestAnimationFrame(() => {
                        $el.style.transition='opacity .2s ease, transform .2s ease';
                        $el.style.opacity=1; $el.style.transform='translateY(0)';
                    })" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="f-label">Numéro Mobile Money</label>
                        <div class="relative">
                            <x-heroicon-o-device-phone-mobile class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model="mobile_number" class="f-input pl font-mono" placeholder="+229 01 XX XX XX"/>
                        </div>
                    </div>
                    <div>
                        <label class="f-label">Référence transaction</label>
                        <div class="relative">
                            <x-heroicon-o-hashtag class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model="transaction_reference" class="f-input pl font-mono"/>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Champs virement / chèque --}}
                @if(in_array($payment_method, ['bank_transfer', 'cheque']))
                <div x-data x-init="
                    $el.style.opacity=0; $el.style.transform='translateY(4px)';
                    requestAnimationFrame(() => {
                        $el.style.transition='opacity .2s ease, transform .2s ease';
                        $el.style.opacity=1; $el.style.transform='translateY(0)';
                    })" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="f-label">Banque</label>
                        <div class="relative">
                            <x-heroicon-o-building-library class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model="bank_name" class="f-input pl"/>
                        </div>
                    </div>
                    <div>
                        <label class="f-label">Référence</label>
                        <div class="relative">
                            <x-heroicon-o-hashtag class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model="transaction_reference" class="f-input pl font-mono"/>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Reliquat --}}
                @php $remaining = $this->getTotal() - (int)$paid_amount; @endphp
                @if($remaining > 0)
                <div x-data x-init="
                    $el.style.opacity=0; $el.style.transform='translateY(4px)';
                    requestAnimationFrame(() => {
                        $el.style.transition='opacity .2s ease, transform .2s ease';
                        $el.style.opacity=1; $el.style.transform='translateY(0)';
                    })">
                    <div class="flex items-center gap-3 p-3.5 bg-amber-50 border border-amber-100 rounded-xl mb-3">
                        <x-heroicon-o-clock class="w-4 h-4 text-amber-500 shrink-0"/>
                        <div class="flex-1">
                            <p class="text-sm text-amber-800 font-medium">
                                Reliquat : <span class="font-bold">{{ number_format($remaining, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
                            </p>
                            <p class="text-[11px] text-amber-600 mt-0.5">Définissez une date limite de règlement</p>
                        </div>
                    </div>
                    <label class="f-label">Date limite de solde</label>
                    <div class="relative">
                        <x-heroicon-o-calendar class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                        <input wire:model="due_date" type="date" class="f-input pl"/>
                    </div>
                </div>
                @endif

                {{-- Toggle troc --}}
                <div class="border-t border-gray-100 pt-4">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <p class="text-sm font-medium text-gray-800">Vente avec reprise / troc</p>
                            <p class="text-xs text-gray-400 mt-0.5">L'appareil repris sera stocké dans les reprises</p>
                        </div>
                        <button type="button" wire:click="$toggle('is_trade_in')"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent
                                    transition-colors duration-200 {{ $is_trade_in ? 'bg-amber-500' : 'bg-gray-200' }}">
                            <span class="pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow transform transition duration-200
                                        {{ $is_trade_in ? 'translate-x-4' : 'translate-x-0' }}"></span>
                        </button>
                    </label>

                    @if($is_trade_in)
                    <div x-data x-init="
                        $el.style.opacity=0; $el.style.transform='translateY(4px)';
                        requestAnimationFrame(() => {
                            $el.style.transition='opacity .2s ease, transform .2s ease';
                            $el.style.opacity=1; $el.style.transform='translateY(0)';
                        })" class="mt-4 rounded-xl border border-amber-100 bg-amber-50/60 overflow-hidden">

                        {{-- Header --}}
                        <div class="flex items-center gap-2.5 px-4 py-3 border-b border-amber-100 bg-amber-50">
                            <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                <x-heroicon-o-arrow-path class="w-3.5 h-3.5 text-amber-600"/>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-amber-800">Appareil repris</p>
                                <p class="text-[10px] text-amber-600">Sera enregistré dans les reprises avec condition « Occasion »</p>
                            </div>
                        </div>

                        <div class="px-4 py-4 space-y-3">

                            {{-- Modèle de l'appareil repris --}}
                            <div>
                                <label class="f-label">Modèle de l'appareil <span>*</span></label>
                                <div class="relative">
                                    <x-heroicon-o-device-phone-mobile class="w-4 h-4 text-amber-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10"/>
                                    <select wire:model.live="trade_in_product_model_id"
                                            class="f-select" style="padding-left: 38px; border-color: #fde68a; background: #fff;">
                                        <option value="">Sélectionner le modèle...</option>
                                        @foreach($tradeInModels as $m)
                                        <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('trade_in_product_model_id')
                                <p class="f-error"><x-heroicon-o-exclamation-circle class="w-3.5 h-3.5"/>{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- IMEI / Série (optionnel) --}}
                            <div>
                                <label class="f-label">IMEI ou numéro de série <span class="text-gray-400 font-normal">(optionnel)</span></label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <x-heroicon-o-qr-code class="w-4 h-4 text-amber-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                                        <input wire:model="trade_in_imei"
                                            wire:keydown.enter="prefillTradeIn"
                                            placeholder="Scanner ou saisir l'IMEI..."
                                            class="f-input pl font-mono" style="border-color: #fde68a; background: #fff;"/>
                                    </div>
                                    <button type="button" wire:click="prefillTradeIn"
                                            class="h-9.5 px-3 rounded-xl bg-amber-500 text-white text-sm flex items-center gap-1.5
                                                hover:bg-amber-600 transition-colors shrink-0"
                                            title="Vérifier dans le stock">
                                        <x-heroicon-o-magnifying-glass class="w-4 h-4"/>
                                    </button>
                                </div>
                                @if($trade_in_stock_info)
                                <div class="mt-1.5 flex items-center gap-1.5 text-[11px] text-amber-700">
                                    <x-heroicon-o-information-circle class="w-3.5 h-3.5 shrink-0"/>
                                    {{ $trade_in_stock_info }}
                                </div>
                                @endif
                                @error('trade_in_imei')
                                <p class="f-error"><x-heroicon-o-exclamation-circle class="w-3.5 h-3.5"/>{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Valeur + État --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="f-label">Valeur de reprise <span>*</span></label>
                                    <div class="relative">
                                        <input wire:model.live="trade_in_value" type="number" min="0"
                                            placeholder="0"
                                            class="f-input pr" style="border-color: #fde68a; background: #fff;"/>
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-amber-500 select-none font-medium">
                                            {{ config('boutique.devise_symbole') }}
                                        </span>
                                    </div>
                                    @error('trade_in_value')
                                    <p class="f-error"><x-heroicon-o-exclamation-circle class="w-3.5 h-3.5"/>{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="f-label">Capacité / Stockage</label>
                                    <input wire:model="trade_in_storage"
                                        placeholder="Ex: 128 Go, 6 Go RAM..."
                                        class="f-input" style="border-color: #fde68a; background: #fff;"/>
                                </div>
                            </div>

                            {{-- Couleur + Batterie --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="f-label">Couleur</label>
                                    <input wire:model="trade_in_color"
                                        placeholder="Ex: Noir, Blanc, Bleu..."
                                        class="f-input" style="border-color: #fde68a; background: #fff;"/>
                                </div>
                                <div>
                                    <label class="f-label">Santé batterie</label>
                                    <div class="relative">
                                        <input wire:model="trade_in_battery"
                                            type="number" min="0" max="100"
                                            placeholder="Ex: 85"
                                            class="f-input pr" style="border-color: #fde68a; background: #fff;"/>
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-amber-500 select-none">%</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes / État général --}}
                            <div>
                                <label class="f-label">État général / Défauts visibles</label>
                                <textarea wire:model="trade_in_notes"
                                        rows="2"
                                        placeholder="Ex: Écran fissuré coin bas-gauche, caméra OK, batterie faible..."
                                        class="w-full px-3 py-2.5 text-sm rounded-xl outline-none resize-none leading-relaxed transition-all duration-150"
                                        style="border: 1.5px solid #fde68a; background: #fff;">
                                </textarea>
                            </div>

                            {{-- Résumé déduction --}}
                            @if((int)$trade_in_value > 0)
                            <div class="flex items-center justify-between p-3 bg-amber-100/60 rounded-lg border border-amber-200">
                                <span class="text-xs font-medium text-amber-700">Déduction sur la vente</span>
                                <span class="text-sm font-bold text-amber-700">
                                    −{{ number_format((int)$trade_in_value, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Notes --}}
                <div>
                    <label class="f-label">Notes</label>
                    <textarea wire:model="notes" rows="2"
                              placeholder="Remarques sur cette vente..."
                              class="w-full px-3 py-2.5 text-sm border-[1.5px] border-gray-200 rounded-xl outline-none bg-white
                                     hover:border-gray-300 focus:border-gray-900 focus:shadow-[0_0_0_3px_rgba(24,24,27,.07)]
                                     resize-none transition-all duration-150 leading-relaxed"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/60 gap-3">
                <button wire:click="prevStep"
                        class="inline-flex items-center gap-1.5 h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600
                            hover:bg-gray-50 hover:border-gray-300 transition-all duration-150">
                    <x-heroicon-o-arrow-left class="w-4 h-4"/>
                    Retour
                </button>
                <button wire:click="nextStep"
                        class="inline-flex items-center gap-2 h-9 px-5 rounded-xl bg-gray-900 text-white text-sm font-medium
                            hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md transition-all duration-150">
                    Récapitulatif
                    <x-heroicon-o-arrow-right class="w-4 h-4"/>
                </button>
            </div>
        </div>
    </div>
    @endif


    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- Étape 4 : Récapitulatif                                           --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    @if($step === 4)
    <div class="anim-panel-up max-w-xl">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Récapitulatif</p>
                    <p class="text-xs text-gray-400 mt-0.5">Vérifiez avant de valider</p>
                </div>
                <div class="w-8 h-8 rounded-xl bg-green-50 flex items-center justify-center">
                    <x-heroicon-o-check class="w-4 h-4 text-green-600"/>
                </div>
            </div>

            {{-- Blocs infos --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 divide-x-0 sm:divide-x divide-y divide-gray-50">
                @php
                    $ri = [
                        ['label' => 'Client',     'value' => $customer_type === 'reseller'
                            ? (\App\Models\Reseller::find($reseller_id)?->name ?? '—')
                            : ($customer_name ?: 'Anonyme')],
                        ['label' => 'Paiement',   'value' => \App\Enums\PaymentMethod::from($payment_method)->label()],
                        ['label' => 'Montant payé','value' => number_format((int)$paid_amount, 0, ',', ' ') . ' ' . config('boutique.devise_symbole')],
                        ['label' => 'Total net',   'value' => number_format($this->getTotal(), 0, ',', ' ') . ' ' . config('boutique.devise_symbole'), 'bold' => true],
                    ];
                @endphp
                @foreach($ri as $i => $r)
                <div class="px-5 py-4" style="animation: slideUp .25s ease {{ $i * 50 }}ms both">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">{{ $r['label'] }}</p>
                    <p class="text-sm {{ isset($r['bold']) ? 'text-base font-bold text-gray-900' : 'font-medium text-gray-800' }}">
                        {{ $r['value'] }}
                    </p>
                </div>
                @endforeach
            </div>

            {{-- Produits --}}
            <div class="px-6 py-5 border-t border-gray-50">
                <p class="sec-label">Articles</p>
                <div>
                    @foreach($items as $i => $item)
                    <div class="rrow" style="animation: slideUp .22s ease {{ $i * 40 }}ms both">
                        <div class="flex-1 min-w-0 pr-4">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <p class="text-[11px] font-mono text-gray-400">{{ $item['identifier'] }}</p>
                                @if($item['discount'] > 0)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700">
                                    −{{ number_format($item['discount'], 0, ',', ' ') }} remise
                                </span>
                                @endif
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-gray-900 shrink-0">
                            {{ number_format($item['line_total'], 0, ',', ' ') }}
                            <span class="text-[11px] font-normal text-gray-400">{{ config('boutique.devise_symbole') }}</span>
                        </span>
                    </div>
                    @endforeach

                    @if($is_trade_in && (int)$trade_in_value > 0)
                    <div class="rrow">
                        <span class="text-sm text-amber-600 font-medium">Troc déduit</span>
                        <span class="text-sm font-semibold text-amber-600">
                            −{{ number_format((int)$trade_in_value, 0, ',', ' ') }}
                            <span class="text-[11px] font-normal text-amber-400">{{ config('boutique.devise_symbole') }}</span>
                        </span>
                    </div>
                    @endif
                </div>

                {{-- Total final --}}
                <div class="flex items-center justify-between pt-4 mt-2 border-t border-gray-100">
                    <span class="text-sm font-semibold text-gray-600">Total</span>
                    <span class="text-2xl font-bold tracking-tight text-gray-900">
                        {{ number_format($this->getTotal(), 0, ',', ' ') }}
                        <span class="text-sm font-normal text-gray-400">{{ config('boutique.devise_symbole') }}</span>
                    </span>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/60 gap-3">
                <button wire:click="prevStep"
                        class="inline-flex items-center gap-1.5 h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600
                            hover:bg-gray-50 hover:border-gray-300 transition-all duration-150 shrink-0">
                    <x-heroicon-o-arrow-left class="w-4 h-4"/>
                    Modifier
                </button>
                <button wire:click="save"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-75 cursor-wait"
                        class="inline-flex items-center gap-2 h-9 px-4 sm:px-6 rounded-xl bg-green-600 text-white text-sm font-semibold
                            hover:bg-green-700 hover:-translate-y-0.5 hover:shadow-md hover:shadow-green-200/60
                            disabled:opacity-75 disabled:cursor-wait disabled:translate-y-0
                            transition-all duration-150 shrink-0">
                    <svg wire:loading wire:target="save"
                        class="w-4 h-4 shrink-0" style="animation: spin .7s linear infinite"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                    <x-heroicon-o-check wire:loading.remove wire:target="save" class="w-4 h-4 shrink-0"/>
                    <span class="hidden sm:inline">Valider la vente</span>
                    <span class="sm:hidden">Valider</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    @script
    <script>
        function saleCreate() {
            return { init() {} }
        }
    </script>
    @endscript
</div>
