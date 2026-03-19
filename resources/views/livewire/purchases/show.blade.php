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
">

    <style>
        @keyframes lineIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes badgePop {
            0%   { transform: scale(0.75); opacity: 0; }
            70%  { transform: scale(1.07); }
            100% { transform: scale(1); opacity: 1; }
        }

        .item-row  { animation: lineIn .25s cubic-bezier(.22,1,.36,1) both; }
        .badge-pop { animation: badgePop .22s cubic-bezier(.34,1.56,.64,1) both; }

        .item-row-wrap {
            transition: background-color .12s ease, box-shadow .12s ease;
        }
        .item-row-wrap:hover {
            background-color: #fafafa;
            box-shadow: inset 3px 0 0 0 #e5e7eb;
        }

        .info-row { transition: background-color .12s ease; }
        .info-row:hover { background-color: #fafafa; }
    </style>

    {{-- ─── Header ───────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3 page-header">
        <div>
            <div class="flex items-center gap-2.5 mb-0.5">
                <span class="text-xl font-semibold tracking-tight text-gray-900 font-mono">
                    {{ $purchase->reference }}
                </span>
                {{-- Badge statut commande --}}
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
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium ring-1 badge-pop {{ $stColor }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $stDot }}"></span>
                    {{ $stLabel }}
                </span>
            </div>
            <p class="text-sm text-gray-400">
                {{ $purchase->supplier->name }}
                <span class="mx-1.5 text-gray-200">·</span>
                {{ $purchase->purchase_date->format('d/m/Y') }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('purchases.index') }}"
               class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600
                      hover:bg-gray-50 hover:border-gray-300 transition-all duration-150">
                <x-heroicon-o-arrow-left class="w-4 h-4"/>
                Retour
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('purchases.edit', $purchase->id) }}"
               class="inline-flex items-center gap-2 h-9 px-4 rounded-xl bg-gray-900 text-white text-sm
                      hover:bg-gray-800 hover:-translate-y-0.5 hover:shadow-md
                      transition-all duration-150">
                <x-heroicon-o-pencil class="w-4 h-4"/>
                Modifier
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── Colonne principale — produits ───────────────────────── --}}
        <div class="lg:col-span-2 space-y-4 main-col">

            {{-- Card produits --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden
                        transition-shadow duration-200 hover:shadow-sm">

                <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <p class="text-sm font-medium">Produits achetés</p>
                    <span class="text-[11px] text-gray-400">{{ $purchase->items->count() }} ligne(s)</span>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach($purchase->items as $i => $item)
                    <div class="item-row-wrap px-5 py-4 item-row"
                         style="animation-delay: {{ $i * 40 }}ms">
                        <div class="flex items-start justify-between gap-4">

                            {{-- Infos produit --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 leading-snug">
                                    {{ $item->productModel->display_label }}
                                </p>
                                <p class="text-[11px] text-gray-400 mt-0.5">
                                    {{ $item->quantity }} unité(s) ×
                                    <span class="font-medium">
                                        {{ number_format($item->unit_purchase_price, 0, ',', ' ') }}
                                        {{ config('boutique.devise_symbole') }}
                                    </span>
                                </p>

                                {{-- IMEI / Serial --}}
                                @if($item->product)
                                <p class="text-[11px] font-mono text-gray-400 mt-1 tracking-wide">
                                    {{ $item->product->imei ?? $item->product->serial_number ?? '—' }}
                                </p>
                                @endif

                                {{-- Notes --}}
                                @if($item->notes)
                                <p class="text-[11px] text-gray-400 italic mt-1">{{ $item->notes }}</p>
                                @endif
                            </div>

                            {{-- Total ligne + condition --}}
                            <div class="text-right shrink-0">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ number_format($item->line_total, 0, ',', ' ') }}
                                    <span class="text-[11px] font-normal text-gray-400">{{ config('boutique.devise_symbole') }}</span>
                                </p>
                                <span class="inline-flex items-center mt-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium
                                             bg-gray-100 text-gray-500 border border-gray-200">
                                    {{ $item->condition->label() }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Total général --}}
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/70 flex items-center justify-between">
                    <span class="text-sm text-gray-500">Total commande</span>
                    <span class="text-base font-bold text-gray-900">
                        {{ number_format($purchase->total_amount, 0, ',', ' ') }}
                        <span class="text-sm font-normal text-gray-400">{{ config('boutique.devise_symbole') }}</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- ─── Colonne droite — paiement + traçabilité ─────────────── --}}
        <div class="space-y-4 side-col">

            {{-- Card Paiement --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden
                        transition-shadow duration-200 hover:shadow-sm">

                <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <p class="text-sm font-medium">Paiement</p>
                    {{-- Badge statut paiement --}}
                    @php
                        $payColor = match($purchase->payment_status->value ?? $purchase->payment_status) {
                            'paid'    => 'bg-green-50 text-green-800 ring-green-100',
                            'partial' => 'bg-amber-50 text-amber-800 ring-amber-100',
                            'unpaid'  => 'bg-red-50 text-red-700 ring-red-100',
                            default   => 'bg-gray-100 text-gray-600 ring-gray-200',
                        };
                        $payDot = match($purchase->payment_status->value ?? $purchase->payment_status) {
                            'paid'    => 'bg-green-500',
                            'partial' => 'bg-amber-500',
                            'unpaid'  => 'bg-red-500',
                            default   => 'bg-gray-400',
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-medium ring-1 badge-pop {{ $payColor }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $payDot }}"></span>
                        {{ $purchase->payment_status->label() }}
                    </span>
                </div>

                <div class="divide-y divide-gray-100">
                    @if(auth()->user()->isAdmin())

                    {{-- Total --}}
                    <div class="info-row flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Total</span>
                        <span class="text-sm font-medium text-gray-800">
                            {{ number_format($purchase->total_amount, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>

                    {{-- Payé --}}
                    <div class="info-row flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Payé</span>
                        <span class="text-sm font-semibold text-green-700">
                            {{ number_format($purchase->paid_amount, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>

                    {{-- Reste à payer --}}
                    @if($purchase->remaining_amount > 0)
                    <div class="info-row flex items-center justify-between px-5 py-3 bg-red-50/40">
                        <span class="text-xs text-red-500">Reste à payer</span>
                        <span class="text-sm font-bold text-red-600">
                            {{ number_format($purchase->remaining_amount, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    @endif
                    @endif

                    {{-- Mode de paiement --}}
                    @if($purchase->payment_method)
                    <div class="info-row flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Mode</span>
                        <span class="text-sm text-gray-700">{{ $purchase->payment_method->label() }}</span>
                    </div>
                    @endif

                    {{-- Référence transaction --}}
                    @if($purchase->transaction_reference)
                    <div class="info-row flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Réf. transaction</span>
                        <span class="text-sm font-mono text-gray-700">{{ $purchase->transaction_reference }}</span>
                    </div>
                    @endif

                    {{-- Date d'échéance --}}
                    @if($purchase->due_date)
                    @php
                        $overdue = $purchase->due_date->isPast()
                            && ($purchase->payment_status->value ?? $purchase->payment_status) !== 'paid';
                    @endphp
                    <div class="info-row flex items-center justify-between px-5 py-3 {{ $overdue ? 'bg-red-50/40' : '' }}">
                        <span class="text-xs {{ $overdue ? 'text-red-400' : 'text-gray-400' }}">Échéance</span>
                        <span class="text-sm font-medium {{ $overdue ? 'text-red-600' : 'text-gray-700' }}">
                            {{ $purchase->due_date->format('d/m/Y') }}
                            @if($overdue)
                            <span class="text-[10px] font-normal ml-1 text-red-400">· dépassée</span>
                            @endif
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Card Traçabilité --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden
                        transition-shadow duration-200 hover:shadow-sm">

                <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50">
                    <p class="text-sm font-medium">Traçabilité</p>
                </div>

                <div class="divide-y divide-gray-100">

                    {{-- Fournisseur --}}
                    <div class="info-row flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Fournisseur</span>
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded-md bg-violet-50 flex items-center justify-center text-[9px] font-bold text-violet-600 shrink-0">
                                {{ strtoupper(substr($purchase->supplier->name, 0, 2)) }}
                            </div>
                            <span class="text-sm font-medium text-gray-800">{{ $purchase->supplier->name }}</span>
                        </div>
                    </div>

                    {{-- Notes --}}
                    @if($purchase->notes)
                    <div class="px-5 py-3">
                        <span class="text-xs text-gray-400 block mb-1">Notes</span>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $purchase->notes }}</p>
                    </div>
                    @endif

                    {{-- Créé le --}}
                    <div class="info-row flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Créé le</span>
                        <span class="text-sm text-gray-700 tabular-nums">
                            {{ $purchase->created_at->format('d/m/Y') }}
                            <span class="text-gray-400">· {{ $purchase->created_at->format('H:i') }}</span>
                        </span>
                    </div>

                    {{-- Créé par --}}
                    @if($purchase->createdBy)
                    <div class="info-row flex items-center justify-between px-5 py-3">
                        <span class="text-xs text-gray-400">Créé par</span>
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded-md bg-gray-900 flex items-center justify-center text-[9px] font-bold text-white shrink-0">
                                {{ strtoupper(substr($purchase->createdBy->name, 0, 2)) }}
                            </div>
                            <span class="text-sm text-gray-700">{{ $purchase->createdBy->name }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
