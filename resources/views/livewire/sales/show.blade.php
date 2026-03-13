<div>
    <x-mary-header
        title="{{ $sale->reference }}"
        subtitle="{{ $sale->created_at->format('d/m/Y H:i') }}"
        icon="o-shopping-bag"
    >
        <x-slot:actions>
            <x-mary-button label="Retour" icon="o-arrow-left" class="btn-ghost btn-sm" link="{{ route('sales.index') }}" />
            <x-mary-button label="Imprimer reçu" icon="o-printer" class="btn-outline btn-sm" wire:click="printReceipt" />
            @if($sale->remaining_amount > 0)
            <x-mary-button label="Ajouter paiement" icon="o-banknotes" class="btn-primary btn-sm" wire:click="openPaymentModal" />
            @endif
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Colonne gauche --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Produits --}}
            <x-mary-card title="Articles vendus">
                @foreach($sale->items as $item)
                <div class="flex justify-between items-center py-3 border-b border-base-200 last:border-0">
                    <div>
                        <p class="font-medium text-sm">{{ $item->productModel->display_label }}</p>
                        @if($item->product)
                        <p class="text-xs font-mono text-gray-400">
                            {{ $item->product->imei ?? $item->product->serial_number ?? '—' }}
                        </p>
                        @endif
                        @if($item->discount > 0)
                        <p class="text-xs text-warning">
                            Prix: {{ number_format($item->unit_price, 0, ',', ' ') }} —
                            Remise: {{ number_format($item->discount, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="font-bold">
                            {{ number_format($item->line_total, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </p>
                        @if(auth()->user()->isAdmin())
                        <p class="text-xs text-gray-400">
                            Marge: {{ number_format($item->profit, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </p>
                        @endif
                    </div>
                </div>
                @endforeach

                @if($sale->is_trade_in)
                <div class="flex justify-between items-center py-2 text-warning border-b border-base-200">
                    <div>
                        <p class="text-sm font-medium">Troc déduit</p>
                        @if($sale->tradeInProduct)
                        <p class="text-xs font-mono text-gray-400">
                            {{ $sale->tradeInProduct->imei ?? $sale->tradeInProduct->serial_number ?? '—' }}
                        </p>
                        @endif
                        @if($sale->trade_in_notes)
                        <p class="text-xs text-gray-400">{{ $sale->trade_in_notes }}</p>
                        @endif
                    </div>
                    <p class="font-bold">
                        - {{ number_format($sale->trade_in_value, 0, ',', ' ') }}
                        {{ config('boutique.devise_symbole') }}
                    </p>
                </div>
                @endif

                <div class="flex justify-between font-bold text-lg mt-2 pt-2">
                    <span>Total net</span>
                    <span class="text-primary">
                        {{ number_format($sale->total_amount, 0, ',', ' ') }}
                        {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
            </x-mary-card>

            {{-- Historique paiements --}}
            <x-mary-card title="Paiements ({{ $sale->payments->count() }})">
                @forelse($sale->payments as $payment)
                <div class="flex justify-between items-center py-2 border-b border-base-200 last:border-0">
                    <div>
                        <p class="text-sm font-medium">{{ $payment->payment_method->label() }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $payment->payment_date->format('d/m/Y') }}
                            @if($payment->transaction_reference) · {{ $payment->transaction_reference }} @endif
                            @if($payment->createdBy) · {{ $payment->createdBy->name }} @endif
                        </p>
                    </div>
                    <p class="font-bold text-success">
                        {{ number_format($payment->amount, 0, ',', ' ') }}
                        {{ config('boutique.devise_symbole') }}
                    </p>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">Aucun paiement enregistré.</p>
                @endforelse
            </x-mary-card>
        </div>

        {{-- Colonne droite --}}
        <div class="space-y-6">
            <x-mary-card title="Résumé">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Client</span>
                        <span class="font-medium text-right">
                            @if($sale->customer_type === 'reseller')
                                {{ $sale->reseller?->name ?? '—' }}
                                <span class="badge badge-primary badge-xs">Rev.</span>
                            @else
                                {{ $sale->customer_name ?: 'Anonyme' }}
                            @endif
                        </span>
                    </div>
                    @if($sale->customer_phone)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Téléphone</span>
                        <span class="text-sm">{{ $sale->customer_phone }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Statut paiement</span>
                        <x-mary-badge
                            value="{{ $sale->payment_status->label() }}"
                            class="badge-sm badge-{{ $sale->payment_status->color() }}"
                        />
                    </div>
                    <div class="divider my-1"></div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total</span>
                        <span class="font-bold">
                            {{ number_format($sale->total_amount, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Payé</span>
                        <span class="font-bold text-success">
                            {{ number_format($sale->paid_amount, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    @if($sale->remaining_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Reste dû</span>
                        <span class="font-bold text-error">
                            {{ number_format($sale->remaining_amount, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    @if($sale->due_date)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Échéance</span>
                        <span class="text-sm {{ $sale->is_overdue ? 'text-error font-bold' : '' }}">
                            {{ $sale->due_date->format('d/m/Y') }}
                            @if($sale->is_overdue) ⚠️ @endif
                        </span>
                    </div>
                    @endif
                    @endif
                    @if($sale->notes)
                    <div>
                        <p class="text-xs text-gray-400">Notes</p>
                        <p class="text-sm">{{ $sale->notes }}</p>
                    </div>
                    @endif
                </div>
            </x-mary-card>

            <x-mary-card title="Traçabilité">
                <div class="space-y-2">
                    <div>
                        <p class="text-xs text-gray-400">Créé le</p>
                        <p class="text-sm">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($sale->createdBy)
                    <div>
                        <p class="text-xs text-gray-400">Par</p>
                        <p class="text-sm">{{ $sale->createdBy->name }}</p>
                    </div>
                    @endif
                </div>
            </x-mary-card>
        </div>
    </div>

    {{-- Modal ajout paiement --}}
    <x-mary-modal wire:model="showPaymentModal" title="Ajouter un paiement">
        <div class="space-y-4">
            <div class="p-3 bg-base-200 rounded-lg text-center">
                <p class="text-xs text-gray-400">Reste à payer</p>
                <p class="text-xl font-bold text-error">
                    {{ number_format($sale->remaining_amount, 0, ',', ' ') }}
                    {{ config('boutique.devise_symbole') }}
                </p>
            </div>
            <x-mary-select
                label="Mode de paiement *"
                wire:model.live="pay_method"
                :options="$paymentMethods"
                option-value="id"
                option-label="name"
            />
            <x-mary-input
                label="Montant *"
                wire:model="pay_amount"
                type="number"
                :suffix="config('boutique.devise_symbole')"
            />
            @if($pay_method === 'mobile_money')
            <x-mary-input label="Numéro Mobile Money" wire:model="pay_mobile" icon="o-device-phone-mobile" />
            <x-mary-input label="Référence" wire:model="pay_reference" icon="o-hashtag" />
            @endif
            @if($pay_method === 'bank_transfer' || $pay_method === 'cheque')
            <x-mary-input label="Banque" wire:model="pay_bank" icon="o-building-library" />
            <x-mary-input label="Référence" wire:model="pay_reference" icon="o-hashtag" />
            @endif
            <x-mary-input label="Notes" wire:model="pay_notes" />
        </div>
        <x-slot:actions>
            <x-mary-button label="Annuler" wire:click="$set('showPaymentModal', false)" class="btn-ghost" />
            <x-mary-button label="Enregistrer" wire:click="addPayment" class="btn-primary" />
        </x-slot:actions>
    </x-mary-modal>
</div>
