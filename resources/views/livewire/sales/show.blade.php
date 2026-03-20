<div x-data x-init="
    $nextTick(() => {
        document.querySelectorAll('.page-header, .main-col, .side-col').forEach((el, i) => {
            el.style.opacity = 0;
            el.style.transform = 'translateY(10px)';
            setTimeout(() => {
                el.style.transition = 'opacity .35s cubic-bezier(.22,1,.36,1), transform .35s cubic-bezier(.22,1,.36,1)';
                el.style.opacity = 1;
                el.style.transform = 'translateY(0)';
            }, i * 70);
        });
    })
" @open-receipt.window="window.open($event.detail.url, '_blank')">

    <style>
        @keyframes lineIn { from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:translateY(0);} }
        @keyframes badgePop { 0%{transform:scale(.75);opacity:0;}70%{transform:scale(1.07);}100%{transform:scale(1);opacity:1;} }
        @keyframes spin { to{transform:rotate(360deg);} }
        @keyframes errorIn { from{opacity:0;transform:translateY(-3px);}to{opacity:1;transform:translateY(0);} }
        .item-row  { animation: lineIn .25s cubic-bezier(.22,1,.36,1) both; }
        .badge-pop { animation: badgePop .22s cubic-bezier(.34,1.56,.64,1) both; }
        .error-msg { animation: errorIn .18s ease both; }
        .hover-row { transition: background-color .12s ease, box-shadow .12s ease; }
        .hover-row:hover { background-color:#fafafa; box-shadow:inset 3px 0 0 0 #e5e7eb; }
        .info-row  { transition: background-color .12s ease; }
        .info-row:hover { background-color:#fafafa; }
        button:active:not(:disabled) { transform:scale(0.97); }
        input:focus,select:focus,textarea:focus { box-shadow:0 0 0 3px rgba(24,24,27,.07); }
        .field-error input,.field-error select { border-color:#fca5a5!important;background-color:#fff5f5!important; }
    </style>

    {{-- ─── Header ──────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3 page-header">
        <div>
            <div class="flex items-center gap-2.5 mb-0.5">
                <span class="text-xl font-semibold tracking-tight text-gray-900 font-mono">{{ $sale->reference }}</span>
                @php
                    $stColor = match($sale->sale_status) {
                        'completed'      => 'bg-green-50 text-green-800 ring-green-100',
                        'cancelled'      => 'bg-red-50 text-red-700 ring-red-100',
                        'partial_return' => 'bg-amber-50 text-amber-800 ring-amber-100',
                        default          => 'bg-gray-100 text-gray-600 ring-gray-200',
                    };
                    $stDot = match($sale->sale_status) {
                        'completed'      => 'bg-green-500',
                        'cancelled'      => 'bg-red-500',
                        'partial_return' => 'bg-amber-500',
                        default          => 'bg-gray-400',
                    };
                    $stLabel = match($sale->sale_status) {
                        'completed'      => 'Complétée',
                        'cancelled'      => 'Annulée',
                        'partial_return' => 'Retour partiel',
                        'full_return'    => 'Retour total',
                        default          => $sale->sale_status,
                    };
                @endphp
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium ring-1 badge-pop {{ $stColor }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $stDot }}"></span>{{ $stLabel }}
                </span>
            </div>
            <p class="text-sm text-gray-400">{{ $sale->created_at->format('d/m/Y') }}<span class="text-gray-300 mx-1">·</span>{{ $sale->created_at->format('H:i') }}</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all duration-150">
                <x-heroicon-o-arrow-left class="w-4 h-4"/>Retour
            </a>
            <button wire:click="printReceipt" class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all duration-150">
                <x-heroicon-o-printer class="w-4 h-4"/>Imprimer reçu
            </button>
            @if($sale->remaining_amount > 0 && $sale->sale_status !== 'cancelled')
            <button wire:click="openPaymentModal" class="inline-flex items-center gap-2 h-9 px-4 rounded-xl bg-gray-900 text-white text-sm hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md transition-all duration-150">
                <x-heroicon-o-banknotes class="w-4 h-4"/>Ajouter paiement
            </button>
            @endif
            {{-- Bouton suppression — admin uniquement --}}
            @if(auth()->user()->hasPermission('cancel_sale') && $sale->sale_status !== 'cancelled')
            <button wire:click="openDeleteModal"
                    class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-red-200 bg-red-50 text-red-600 text-sm hover:bg-red-100 hover:border-red-300 transition-all duration-150">
                <x-heroicon-o-trash class="w-4 h-4"/>Supprimer
            </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── Colonne principale ──────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4 main-col">

            {{-- Articles --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden transition-shadow duration-200 hover:shadow-sm">
                <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <p class="text-sm font-medium">Articles vendus</p>
                    <span class="text-[11px] text-gray-400">{{ $sale->items->count() }} article(s)</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($sale->items as $i => $item)
                    <div class="hover-row px-5 py-4 item-row" style="animation-delay:{{ $i*40 }}ms">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 leading-snug">{{ $item->productModel->display_label }}</p>
                                @if($item->product)
                                <p class="text-[11px] font-mono text-gray-400 mt-0.5 tracking-wide">{{ $item->product->imei ?? $item->product->serial_number ?? '—' }}</p>
                                @endif
                                @if($item->discount > 0)
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[11px] text-gray-400">Prix : {{ number_format($item->unit_price, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700">−{{ number_format($item->discount, 0, ',', ' ') }} remise</span>
                                </div>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-semibold text-gray-900">{{ number_format($item->line_total, 0, ',', ' ') }}<span class="text-[11px] font-normal text-gray-400"> {{ config('boutique.devise_symbole') }}</span></p>
                                @if(auth()->user()->isAdmin())
                                <p class="text-[11px] text-gray-400 mt-0.5">Marge : <span class="text-green-600 font-medium">{{ number_format($item->profit, 0, ',', ' ') }}</span></p>
                                @endif
                            </div>
                        </div>

                        {{-- Bouton retour défectueux (masqué si vente annulée) --}}
                        @if($sale->sale_status !== 'cancelled')
                            @if($item->product && $item->product->state === \App\Enums\ProductState::SOLD)
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <button wire:click="openDeclareReturn({{ $item->product->id }})"
                                        class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-lg text-[11px] font-medium bg-red-50 text-red-600 hover:bg-red-100 border border-red-100 transition-all duration-150">
                                    <x-heroicon-o-arrow-uturn-left class="w-3 h-3"/>
                                    Déclarer un retour défectueux
                                </button>
                            </div>
                            @elseif($item->product && $item->product->state === \App\Enums\ProductState::DEFECTIVE)
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-lg text-[11px] font-medium bg-red-50 text-red-400 border border-red-100">
                                    <x-heroicon-o-exclamation-triangle class="w-3 h-3"/>
                                    Retour déclaré — en attente fournisseur
                                </span>
                            </div>
                            @endif
                        @endif
                    </div>
                    @endforeach

                    @if($sale->is_trade_in)
                    <div class="hover-row px-5 py-4 bg-amber-50/40">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <p class="text-sm font-medium text-amber-700">Troc déduit</p>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700">Trade-in</span>
                                </div>
                                @if($sale->tradeInProduct)
                                <p class="text-[11px] font-mono text-gray-400 mt-0.5">{{ $sale->tradeInProduct->imei ?? $sale->tradeInProduct->serial_number ?? '—' }}</p>
                                @endif
                                @if($sale->trade_in_notes)
                                <p class="text-[11px] text-gray-400 italic mt-0.5">{{ $sale->trade_in_notes }}</p>
                                @endif
                            </div>
                            <p class="text-sm font-semibold text-amber-600 shrink-0">
                                −{{ number_format($sale->trade_in_value, 0, ',', ' ') }}<span class="text-[11px] font-normal text-gray-400"> {{ config('boutique.devise_symbole') }}</span>
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/70 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600">Total net</span>
                    <span class="text-base font-bold text-gray-900">{{ number_format($sale->total_amount, 0, ',', ' ') }}<span class="text-sm font-normal text-gray-400"> {{ config('boutique.devise_symbole') }}</span></span>
                </div>
            </div>

            {{-- Paiements --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden transition-shadow duration-200 hover:shadow-sm">
                <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <p class="text-sm font-medium">Paiements</p>
                    <span class="text-[11px] text-gray-400">{{ $sale->payments->count() }} paiement(s)</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($sale->payments as $i => $payment)
                    <div class="hover-row px-5 py-3.5 item-row" style="animation-delay:{{ $i*35 }}ms">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                                    <x-heroicon-o-banknotes class="w-4 h-4 text-green-600"/>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $payment->payment_method->label() }}</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">
                                        {{ $payment->payment_date->format('d/m/Y') }}
                                        @if($payment->transaction_reference)<span class="text-gray-300 mx-1">·</span><span class="font-mono">{{ $payment->transaction_reference }}</span>@endif
                                        @if($payment->createdBy)<span class="text-gray-300 mx-1">·</span>{{ $payment->createdBy->name }}@endif
                                    </p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-green-700 shrink-0">
                                +{{ number_format($payment->amount, 0, ',', ' ') }}<span class="text-[11px] font-normal text-gray-400"> {{ config('boutique.devise_symbole') }}</span>
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center justify-center py-10">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center mb-2">
                            <x-heroicon-o-banknotes class="w-4 h-4 text-gray-300"/>
                        </div>
                        <p class="text-sm text-gray-400">Aucun paiement enregistré</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ─── Colonne droite ──────────────────────────────────── --}}
        <div class="space-y-4 side-col">

            {{-- Résumé --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden transition-shadow duration-200 hover:shadow-sm">
                <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <p class="text-sm font-medium">Résumé</p>
                    @php
                        $payColor = match($sale->payment_status->value ?? $sale->payment_status) {
                            'paid'    => 'bg-green-50 text-green-800 ring-green-100',
                            'partial' => 'bg-amber-50 text-amber-800 ring-amber-100',
                            'unpaid'  => 'bg-red-50 text-red-700 ring-red-100',
                            default   => 'bg-gray-100 text-gray-600 ring-gray-200',
                        };
                        $payDot = match($sale->payment_status->value ?? $sale->payment_status) {
                            'paid'    => 'bg-green-500',
                            'partial' => 'bg-amber-500',
                            'unpaid'  => 'bg-red-500',
                            default   => 'bg-gray-400',
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium ring-1 badge-pop {{ $payColor }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $payDot }}"></span>{{ $sale->payment_status->label() }}
                    </span>
                </div>
                <div class="divide-y divide-gray-100">
                    <div class="info-row px-5 py-3 flex items-center justify-between">
                        <span class="text-xs text-gray-400">Client</span>
                        @if($sale->customer_type === 'reseller')
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded-md bg-violet-50 flex items-center justify-center text-[9px] font-bold text-violet-600 shrink-0">{{ strtoupper(substr($sale->reseller?->name ?? 'R', 0, 2)) }}</div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-800">{{ $sale->reseller?->name ?? '—' }}</p>
                                <span class="text-[10px] text-violet-600 font-medium">Revendeur</span>
                            </div>
                        </div>
                        @else
                        <span class="text-sm font-medium text-gray-800">{{ $sale->customer_name ?: 'Anonyme' }}</span>
                        @endif
                    </div>
                    @if($sale->customer_phone)
                    <div class="info-row px-5 py-3 flex items-center justify-between">
                        <span class="text-xs text-gray-400">Téléphone</span>
                        <span class="text-sm font-mono text-gray-700">{{ $sale->customer_phone }}</span>
                    </div>
                    @endif
                    <div class="px-5 py-3 bg-gray-50/60">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-gray-400">Total</span>
                            <span class="text-sm font-semibold text-gray-800">{{ number_format($sale->total_amount, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-gray-400">Payé</span>
                            <span class="text-sm font-semibold text-green-700">{{ number_format($sale->paid_amount, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
                        </div>
                        @if($sale->remaining_amount > 0)
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <span class="text-xs text-red-400 font-medium">Reste dû</span>
                            <span class="text-sm font-bold text-red-600">{{ number_format($sale->remaining_amount, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
                        </div>
                        @endif
                    </div>
                    @if($sale->remaining_amount > 0 && $sale->due_date)
                    @php $overdue = $sale->is_overdue ?? $sale->due_date->isPast(); @endphp
                    <div class="info-row px-5 py-3 flex items-center justify-between {{ $overdue ? 'bg-red-50/30' : '' }}">
                        <span class="text-xs {{ $overdue ? 'text-red-400' : 'text-gray-400' }}">Échéance</span>
                        <span class="text-sm font-medium {{ $overdue ? 'text-red-600' : 'text-gray-700' }}">
                            {{ $sale->due_date->format('d/m/Y') }}@if($overdue)<span class="text-[10px] font-normal ml-1 text-red-400">· dépassée</span>@endif
                        </span>
                    </div>
                    @endif
                    @if($sale->notes)
                    <div class="px-5 py-3">
                        <p class="text-xs text-gray-400 mb-1">Notes</p>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $sale->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Traçabilité --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden transition-shadow duration-200 hover:shadow-sm">
                <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50">
                    <p class="text-sm font-medium">Traçabilité</p>
                </div>
                <div class="divide-y divide-gray-100">
                    <div class="info-row px-5 py-3 flex items-center justify-between">
                        <span class="text-xs text-gray-400">Vendu le</span>
                        <span class="text-sm text-gray-700 tabular-nums">
                            {{ $sale->created_at->locale('fr')->translatedFormat('d F Y') }}
                            <span class="text-gray-400">· à {{ $sale->created_at->locale('fr')->translatedFormat('H\hi') }}</span>
                        </span>
                    </div>
                    @if($sale->createdBy)
                    <div class="info-row px-5 py-3 flex items-center justify-between">
                        <span class="text-xs text-gray-400">Par</span>
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded-md bg-gray-900 flex items-center justify-center text-[9px] font-bold text-white shrink-0">{{ strtoupper(substr($sale->createdBy->name, 0, 2)) }}</div>
                            <span class="text-sm text-gray-700">{{ $sale->createdBy->name }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- ─── Modal : Ajouter paiement ─────────────────────────────── --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div x-data="{ open: @entangle('showPaymentModal').live }" x-cloak
         @keydown.escape.window="$wire.set('showPaymentModal', false)">

        {{-- Backdrop --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/25 backdrop-blur-[3px]"
             wire:click="$set('showPaymentModal', false)">
        </div>

        {{-- Panel --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">

            <div class="relative bg-white rounded-2xl shadow-2xl shadow-gray-200/80 border border-gray-100 w-full max-w-md pointer-events-auto">

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Ajouter un paiement</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Enregistrer un encaissement</p>
                    </div>
                    <button wire:click="$set('showPaymentModal', false)" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-700 hover:rotate-90 transition-all duration-200">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                    </button>
                </div>

                <div class="mx-6 mt-5 flex items-center justify-between p-4 bg-red-50 border border-red-100 rounded-xl">
                    <div>
                        <p class="text-xs text-red-400 font-medium uppercase tracking-wider">Reste à payer</p>
                        <p class="text-xl font-bold text-red-600 mt-0.5">{{ number_format($sale->remaining_amount, 0, ',', ' ') }}<span class="text-sm font-normal text-red-400"> {{ config('boutique.devise_symbole') }}</span></p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-500"/>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Mode de paiement <span class="text-red-400">*</span></label>
                        <select wire:model.live="pay_method" class="w-full h-9 px-3 text-sm border border-gray-200 rounded-xl outline-none bg-white hover:border-gray-300 focus:border-gray-400 transition-all duration-150 cursor-pointer">
                            @foreach($paymentMethods as $pm)
                            <option value="{{ $pm['id'] }}">{{ $pm['name'] }}</option>
                            @endforeach
                        </select>
                        @error('pay_method')<p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1"><x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>{{ $message }}</p>@enderror
                    </div>

                    <div class="{{ $errors->has('pay_amount') ? 'field-error' : '' }}">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Montant <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input wire:model="pay_amount" type="number" class="w-full h-9 pl-3 pr-14 text-sm border border-gray-200 rounded-xl outline-none bg-white hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 select-none">{{ config('boutique.devise_symbole') }}</span>
                        </div>
                        @error('pay_amount')<p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1"><x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>{{ $message }}</p>@enderror
                    </div>

                    @if($pay_method === 'mobile_money')
                    <div x-data x-init="$el.style.opacity=0;$el.style.transform='translateY(4px)';requestAnimationFrame(()=>{$el.style.transition='opacity .2s ease,transform .2s ease';$el.style.opacity=1;$el.style.transform='translateY(0)'})" class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Numéro Mobile Money</label>
                            <div class="relative"><x-heroicon-o-device-phone-mobile class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/><input wire:model="pay_mobile" class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/></div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Référence</label>
                            <div class="relative"><x-heroicon-o-hashtag class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/><input wire:model="pay_reference" class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white hover:border-gray-300 focus:border-gray-400 transition-all duration-150 font-mono"/></div>
                        </div>
                    </div>
                    @endif

                    @if($pay_method === 'bank_transfer' || $pay_method === 'cheque')
                    <div x-data x-init="$el.style.opacity=0;$el.style.transform='translateY(4px)';requestAnimationFrame(()=>{$el.style.transition='opacity .2s ease,transform .2s ease';$el.style.opacity=1;$el.style.transform='translateY(0)'})" class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Banque</label>
                            <div class="relative"><x-heroicon-o-building-library class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/><input wire:model="pay_bank" class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/></div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Référence</label>
                            <div class="relative"><x-heroicon-o-hashtag class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/><input wire:model="pay_reference" class="w-full h-9 pl-9 pr-3 text-sm border border-gray-200 rounded-xl outline-none bg-white hover:border-gray-300 focus:border-gray-400 transition-all duration-150 font-mono"/></div>
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes</label>
                        <input wire:model="pay_notes" class="w-full h-9 px-3 text-sm border border-gray-200 rounded-xl outline-none bg-white hover:border-gray-300 focus:border-gray-400 transition-all duration-150"/>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                    <button wire:click="$set('showPaymentModal', false)" class="h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600 hover:bg-gray-50 transition-all duration-150">Annuler</button>
                    <button wire:click="addPayment" wire:loading.attr="disabled" wire:loading.class="opacity-75 cursor-wait"
                            class="h-9 px-4 rounded-xl bg-gray-900 text-white text-sm hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md disabled:opacity-75 disabled:cursor-wait disabled:translate-y-0 transition-all duration-150 flex items-center gap-1.5">
                        <svg wire:loading wire:target="addPayment" class="w-3.5 h-3.5" style="animation:spin .7s linear infinite" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                        <x-heroicon-o-check wire:loading.remove wire:target="addPayment" class="w-4 h-4"/>Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- ─── Modal : Retour défectueux ─────────────────────────────── --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div x-data="{ open: @entangle('showReturnModal').live }" x-cloak
         @keydown.escape.window="$wire.set('showReturnModal', false)">

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/25 backdrop-blur-[3px]"
             wire:click="$set('showReturnModal', false)">
        </div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">

            <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md pointer-events-auto">

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Déclarer un retour défectueux</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Le produit sera placé dans la file "Retours fournisseur"</p>
                    </div>
                    <button wire:click="$set('showReturnModal', false)" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-700 hover:rotate-90 transition-all duration-200">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                    </button>
                </div>

                <div class="mx-6 mt-5 flex items-center gap-3 p-3.5 bg-red-50 border border-red-100 rounded-xl">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-500 shrink-0"/>
                    <div>
                        <p class="text-sm font-medium text-red-800">Produit défectueux</p>
                        <p class="text-[11px] text-red-600 mt-0.5">Il sera retiré du stock et marqué à renvoyer au fournisseur.</p>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Défaillance constatée <span class="text-red-400">*</span></label>
                        <textarea wire:model="return_reason" rows="3" placeholder="Ex: Écran qui clignote, micro HS, batterie qui gonfle..."
                                  class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl outline-none hover:border-gray-300 focus:border-gray-400 resize-none transition-all duration-150 leading-relaxed"></textarea>
                        @error('return_reason')<p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1"><x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Produit de remplacement
                            <span class="text-gray-400 font-normal">(optionnel)</span>
                        </label>
                        @if(count($availableReplacements) > 0)
                        <select wire:model="replacement_id" class="w-full h-9 px-3 text-sm border border-gray-200 rounded-xl outline-none bg-white hover:border-gray-300 focus:border-gray-400 transition-all duration-150 cursor-pointer">
                            <option value="">— Aucun remplacement immédiat</option>
                            @foreach($availableReplacements as $r)
                            <option value="{{ $r['id'] }}">{{ $r['name'] }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">{{ count($availableReplacements) }} unité(s) disponible(s) du même modèle</p>
                        @else
                        <div class="flex items-center gap-2.5 p-3 bg-amber-50 border border-amber-100 rounded-xl">
                            <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-amber-500 shrink-0"/>
                            <p class="text-xs text-amber-700">Aucun stock disponible pour ce modèle. Le client devra être rappelé quand un exemplaire arrive.</p>
                        </div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes internes (optionnel)</label>
                        <textarea wire:model="return_notes" rows="2" placeholder="Informations pour le suivi interne..."
                                  class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl outline-none hover:border-gray-300 focus:border-gray-400 resize-none transition-all duration-150 leading-relaxed"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                    <button wire:click="$set('showReturnModal', false)" class="h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600 hover:bg-gray-50 transition-all duration-150">Annuler</button>
                    <button wire:click="declareReturn" wire:loading.attr="disabled" wire:loading.class="opacity-75 cursor-wait"
                            class="h-9 px-5 rounded-xl bg-red-500 text-white text-sm hover:bg-red-600 hover:shadow-md hover:shadow-red-200/60 disabled:opacity-75 disabled:cursor-wait transition-all duration-150 flex items-center gap-1.5">
                        <svg wire:loading wire:target="declareReturn" class="w-3.5 h-3.5" style="animation:spin .7s linear infinite" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                        <x-heroicon-o-arrow-uturn-left wire:loading.remove wire:target="declareReturn" class="w-4 h-4"/>
                        Déclarer le retour
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- ─── Modal : Supprimer la vente ────────────────────────────── --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div x-data="{ open: @entangle('showDeleteModal').live }" x-cloak
         @keydown.escape.window="$wire.set('showDeleteModal', false)">

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/30 backdrop-blur-[3px]">
        </div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">

            <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md pointer-events-auto">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                            <x-heroicon-o-trash class="w-4.5 h-4.5 text-red-500"/>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">Supprimer la vente</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Action irréversible — confirmation requise</p>
                        </div>
                    </div>
                    <button wire:click="$set('showDeleteModal', false)" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-700 hover:rotate-90 transition-all duration-200">
                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                    </button>
                </div>

                {{-- Bandeau d'avertissement --}}
                <div class="mx-6 mt-5 flex items-start gap-3 p-4 bg-red-50 border border-red-100 rounded-xl">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-500 mt-0.5 shrink-0"/>
                    <div>
                        <p class="text-sm font-semibold text-red-800">Vous allez supprimer <span class="font-mono">{{ $sale->reference }}</span></p>
                        <p class="text-xs text-red-600 mt-1 leading-relaxed">
                            Les produits vendus seront automatiquement remis en stock.
                            Cette action sera enregistrée dans les logs d'activité.
                        </p>
                    </div>
                </div>

                {{-- Récapitulatif --}}
                <div class="grid grid-cols-3 gap-3 mx-6 mt-4">
                    <div class="bg-gray-50 rounded-xl p-3 text-center">
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Total</p>
                        <p class="text-sm font-bold text-gray-800">{{ number_format($sale->total_amount, 0, ',', ' ') }} <span class="font-normal text-gray-400 text-[11px]">{{ config('boutique.devise_symbole') }}</span></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 text-center">
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Articles</p>
                        <p class="text-sm font-bold text-gray-800">{{ $sale->items->count() }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 text-center">
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Paiements</p>
                        <p class="text-sm font-bold text-gray-800">{{ $sale->payments->count() }}</p>
                    </div>
                </div>

                {{-- Formulaire --}}
                <div class="px-6 py-5 space-y-4">

                    {{-- Motif --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Motif de suppression <span class="text-red-400">*</span>
                        </label>
                        <textarea wire:model="delete_reason" rows="3"
                                  placeholder="Expliquez pourquoi cette vente est supprimée (min. 10 caractères)…"
                                  class="w-full px-3 py-2.5 text-sm border {{ $errors->has('delete_reason') ? 'border-red-300 bg-red-50/30' : 'border-gray-200' }} rounded-xl outline-none hover:border-gray-300 focus:border-gray-400 resize-none transition-all duration-150 leading-relaxed"></textarea>
                        @error('delete_reason')
                        <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>{{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Mot de passe --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Confirmez avec votre mot de passe <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <x-heroicon-o-lock-closed class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"/>
                            <input wire:model="delete_password"
                                   type="password"
                                   placeholder="••••••••"
                                   class="w-full h-9 pl-9 pr-3 text-sm border {{ $delete_password_error || $errors->has('delete_password') ? 'border-red-300 bg-red-50/30' : 'border-gray-200' }} rounded-xl outline-none hover:border-gray-300 focus:border-gray-400 transition-all duration-150"
                                   wire:keydown.enter="deleteSale"/>
                        </div>
                        @if($delete_password_error)
                        <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                            <x-heroicon-o-x-circle class="w-3.5 h-3.5 shrink-0"/>
                            Mot de passe incorrect. Veuillez réessayer.
                        </p>
                        @elseif($errors->has('delete_password'))
                        <p class="error-msg flex items-center gap-1 text-[11px] text-red-500 mt-1">
                            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0"/>{{ $errors->first('delete_password') }}
                        </p>
                        @else
                        <p class="text-[11px] text-gray-400 mt-1">Ce mot de passe sera conservé dans les logs de sécurité.</p>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600 hover:bg-gray-50 transition-all duration-150">
                        Annuler
                    </button>
                    <button wire:click="deleteSale"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-wait"
                            class="h-9 px-5 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 hover:-translate-y-0.5 hover:shadow-md hover:shadow-red-200/60 disabled:opacity-75 disabled:cursor-wait disabled:translate-y-0 transition-all duration-150 flex items-center gap-2">
                        <svg wire:loading wire:target="deleteSale" class="w-3.5 h-3.5" style="animation:spin .7s linear infinite" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        <x-heroicon-o-trash wire:loading.remove wire:target="deleteSale" class="w-4 h-4"/>
                        Supprimer définitivement
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
