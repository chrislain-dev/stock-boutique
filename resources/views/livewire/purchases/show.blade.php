<div>
    <x-mary-header
        title="{{ $purchase->reference }}"
        subtitle="{{ $purchase->supplier->name }} — {{ $purchase->purchase_date->format('d/m/Y') }}"
        icon="o-shopping-cart"
    >
        <x-slot:actions>
            <x-mary-button label="Retour" icon="o-arrow-left" class="btn-ghost btn-sm" link="{{ route('purchases.index') }}" />
            @if(auth()->user()->isAdmin())
            <x-mary-button label="Modifier" icon="o-pencil" class="btn-primary btn-sm" link="{{ route('purchases.edit', $purchase->id) }}" />
            @endif
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Colonne gauche — lignes produits --}}
        <div class="lg:col-span-2 space-y-6">
            <x-mary-card title="Produits achetés ({{ $purchase->items->count() }} ligne(s))">
                @foreach($purchase->items as $item)
                <div class="py-3 border-b border-base-200 last:border-0">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-sm">{{ $item->productModel->display_label }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $item->quantity }} unité(s) ×
                                {{ number_format($item->unit_purchase_price, 0, ',', ' ') }}
                                {{ config('boutique.devise_symbole') }}
                            </p>
                            @if($item->product)
                            <p class="text-xs font-mono text-gray-400 mt-1">
                                {{ $item->product->imei ?? $item->product->serial_number ?? '—' }}
                            </p>
                            @endif
                            @if($item->notes)
                            <p class="text-xs text-gray-400 italic">{{ $item->notes }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-sm">
                                {{ number_format($item->line_total, 0, ',', ' ') }}
                                {{ config('boutique.devise_symbole') }}
                            </p>
                            <x-mary-badge
                                value="{{ $item->condition->label() }}"
                                class="badge-xs badge-ghost mt-1"
                            />
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="divider my-2"></div>
                <div class="flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span class="text-primary">
                        {{ number_format($purchase->total_amount, 0, ',', ' ') }}
                        {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
            </x-mary-card>
        </div>

        {{-- Colonne droite — infos paiement --}}
        <div class="space-y-6">
            <x-mary-card title="Paiement">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Statut</span>
                        <x-mary-badge
                            value="{{ $purchase->payment_status->label() }}"
                            class="badge-sm badge-{{ $purchase->payment_status->color() }}"
                        />
                    </div>
                    @if(auth()->user()->isAdmin())
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total</span>
                        <span class="font-medium">
                            {{ number_format($purchase->total_amount, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Payé</span>
                        <span class="font-medium text-success">
                            {{ number_format($purchase->paid_amount, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    @if($purchase->remaining_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Reste à payer</span>
                        <span class="font-bold text-error">
                            {{ number_format($purchase->remaining_amount, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    @endif
                    @endif
                    @if($purchase->payment_method)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Mode</span>
                        <span class="text-sm">{{ $purchase->payment_method->label() }}</span>
                    </div>
                    @endif
                    @if($purchase->transaction_reference)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Référence</span>
                        <span class="text-sm font-mono">{{ $purchase->transaction_reference }}</span>
                    </div>
                    @endif
                    @if($purchase->due_date)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Échéance</span>
                        <span class="text-sm {{ $purchase->due_date->isPast() && $purchase->payment_status !== \App\Enums\PaymentStatus::PAID ? 'text-error font-bold' : '' }}">
                            {{ $purchase->due_date->format('d/m/Y') }}
                        </span>
                    </div>
                    @endif
                </div>
            </x-mary-card>

            <x-mary-card title="Traçabilité">
                <div class="space-y-2">
                    <div>
                        <p class="text-xs text-gray-400">Fournisseur</p>
                        <p class="text-sm font-medium">{{ $purchase->supplier->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Statut commande</p>
                        @php
                            $color = match($purchase->status) { 'received' => 'success', 'pending' => 'warning', 'cancelled' => 'error', default => 'ghost' };
                            $label = match($purchase->status) { 'received' => 'Reçu', 'pending' => 'En attente', 'cancelled' => 'Annulé', default => $purchase->status };
                        @endphp
                        <x-mary-badge value="{{ $label }}" class="badge-sm badge-{{ $color }}" />
                    </div>
                    @if($purchase->notes)
                    <div>
                        <p class="text-xs text-gray-400">Notes</p>
                        <p class="text-sm">{{ $purchase->notes }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-400">Créé le</p>
                        <p class="text-sm">{{ $purchase->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($purchase->createdBy)
                    <div>
                        <p class="text-xs text-gray-400">Créé par</p>
                        <p class="text-sm">{{ $purchase->createdBy->name }}</p>
                    </div>
                    @endif
                </div>
            </x-mary-card>
        </div>
    </div>
</div>
