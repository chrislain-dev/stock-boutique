<div>
    <x-mary-header title="Nouvelle vente" icon="o-shopping-bag">
        <x-slot:actions>
            <x-mary-button label="Annuler" icon="o-x-mark" class="btn-ghost btn-sm" link="{{ route('sales.index') }}" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Steps --}}
    <div class="flex items-center mb-8">
        @foreach([1 => 'Client', 2 => 'Produits', 3 => 'Paiement', 4 => 'Récapitulatif'] as $num => $label)
        <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                    {{ $step > $num ? 'bg-success text-white' : ($step === $num ? 'bg-primary text-white' : 'bg-base-300 text-gray-500') }}">
                    {{ $step > $num ? '✓' : $num }}
                </div>
                <span class="text-sm font-medium {{ $step === $num ? 'text-primary' : 'text-gray-400' }}">
                    {{ $label }}
                </span>
            </div>
            @if(!$loop->last)
            <div class="flex-1 h-px mx-4 {{ $step > $num ? 'bg-success' : 'bg-base-300' }}"></div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Étape 1 : Client --}}
    @if($step === 1)
    <x-mary-card title="Type de client">
        <div class="space-y-4">
            <div class="flex gap-4">
                <label class="flex items-center gap-3 cursor-pointer p-4 border rounded-lg flex-1
                    {{ $customer_type === 'client' ? 'border-primary bg-primary/5' : 'border-base-300' }}">
                    <input type="radio" wire:model.live="customer_type" value="client" class="radio radio-primary" />
                    <div>
                        <p class="font-medium">Client particulier</p>
                        <p class="text-xs text-gray-400">Walk-in ou client enregistré</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer p-4 border rounded-lg flex-1
                    {{ $customer_type === 'reseller' ? 'border-primary bg-primary/5' : 'border-base-300' }}">
                    <input type="radio" wire:model.live="customer_type" value="reseller" class="radio radio-primary" />
                    <div>
                        <p class="font-medium">Revendeur</p>
                        <p class="text-xs text-gray-400">Prix revendeur appliqué</p>
                    </div>
                </label>
            </div>

            @if($customer_type === 'reseller')
            <x-mary-select
                label="Revendeur *"
                wire:model="reseller_id"
                :options="$resellers"
                option-value="id"
                option-label="name"
                placeholder="Sélectionner un revendeur"
                icon="o-building-storefront"
            />
            @else
            <div class="grid grid-cols-2 gap-4">
                <x-mary-input
                    label="Nom du client"
                    wire:model="customer_name"
                    placeholder="Optionnel"
                    icon="o-user"
                />
                <x-mary-input
                    label="Téléphone"
                    wire:model="customer_phone"
                    placeholder="Optionnel"
                    icon="o-phone"
                />
            </div>
            @endif
        </div>
        <div class="flex justify-end mt-4">
            <x-mary-button label="Suivant →" wire:click="nextStep" class="btn-primary" />
        </div>
    </x-mary-card>
    @endif

    {{-- Étape 2 : Produits --}}
    @if($step === 2)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recherche produits --}}
        <x-mary-card title="Ajouter un produit">
            <div class="space-y-4">
                {{-- Par IMEI --}}
                <div>
                    <p class="text-sm font-medium mb-2">Par IMEI / numéro de série</p>
                    <div class="flex gap-2">
                        <x-mary-input
                            wire:model="search_imei"
                            placeholder="Scanner ou saisir IMEI..."
                            icon="o-qr-code"
                            class="flex-1"
                            wire:keydown.enter="searchByImei"
                        />
                        <x-mary-button
                            icon="o-magnifying-glass"
                            class="btn-primary"
                            wire:click="searchByImei"
                        />
                    </div>
                    @error('search_imei')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                    @if($search_error)
                        <p class="text-error text-xs mt-1">{{ $search_error }}</p>
                    @endif
                </div>

                <div class="divider text-xs">OU</div>

                {{-- Par liste --}}
                <div>
                    <p class="text-sm font-medium mb-2">Choisir dans la liste</p>
                    <x-mary-select
                        wire:model.live="search_product_id"
                        :options="$availableProducts"
                        option-value="id"
                        option-label="name"
                        placeholder="Sélectionner un produit..."
                        searchable
                    />
                </div>
            </div>
        </x-mary-card>

        {{-- Panier --}}
        <x-mary-card title="Panier ({{ count($items) }} article(s))">
            @forelse($items as $index => $item)
            <div class="border border-base-200 rounded-lg p-3 mb-2">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="font-medium text-sm">{{ $item['name'] }}</p>
                        <p class="text-xs font-mono text-gray-400">{{ $item['identifier'] }}</p>
                    </div>
                    <x-mary-button
                        icon="o-trash"
                        class="btn-ghost btn-xs text-error"
                        wire:click="removeItem({{ $index }})"
                    />
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Prix unitaire</p>
                        <input
                            type="number"
                            class="input input-bordered input-xs w-full"
                            value="{{ $item['unit_price'] }}"
                            wire:change="updateItemPrice({{ $index }}, 'unit_price', $event.target.value)"
                        />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Remise</p>
                        <input
                            type="number"
                            class="input input-bordered input-xs w-full"
                            value="{{ $item['discount'] }}"
                            wire:change="updateItemPrice({{ $index }}, 'discount', $event.target.value)"
                        />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Total ligne</p>
                        <p class="text-sm font-bold pt-1">
                            {{ number_format($item['line_total'], 0, ',', ' ') }}
                        </p>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-8">Aucun produit ajouté.</p>
            @endforelse

            @if(count($items) > 0)
            <div class="divider my-2"></div>
            <div class="flex justify-between font-bold">
                <span>Total</span>
                <span class="text-primary">
                    {{ number_format(collect($items)->sum('line_total'), 0, ',', ' ') }}
                    {{ config('boutique.devise_symbole') }}
                </span>
            </div>
            @endif
        </x-mary-card>
    </div>

    <div class="flex justify-between mt-4">
        <x-mary-button label="← Retour" wire:click="prevStep" class="btn-ghost" />
        <x-mary-button label="Paiement →" wire:click="nextStep" class="btn-primary" :disabled="empty($items)" />
    </div>
    @endif

    {{-- Étape 3 : Paiement --}}
    @if($step === 3)
    <x-mary-card title="Paiement">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="md:col-span-2 p-4 bg-base-200 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="font-medium">Total à payer</span>
                    <span class="text-2xl font-bold text-primary">
                        {{ number_format($this->getTotal(), 0, ',', ' ') }}
                        {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
            </div>

            <x-mary-select
                label="Mode de paiement *"
                wire:model.live="payment_method"
                :options="$paymentMethods"
                option-value="id"
                option-label="name"
                icon="o-credit-card"
            />
            <x-mary-input
                label="Montant payé *"
                wire:model.live="paid_amount"
                type="number"
                icon="o-banknotes"
                :suffix="config('boutique.devise_symbole')"
            />

            @if($payment_method === 'mobile_money')
            <x-mary-input label="Numéro Mobile Money" wire:model="mobile_number" icon="o-device-phone-mobile" />
            <x-mary-input label="Référence transaction" wire:model="transaction_reference" icon="o-hashtag" />
            @endif

            @if($payment_method === 'bank_transfer' || $payment_method === 'cheque')
            <x-mary-input label="Banque" wire:model="bank_name" icon="o-building-library" />
            <x-mary-input label="Référence" wire:model="transaction_reference" icon="o-hashtag" />
            @endif

            @php $remaining = $this->getTotal() - (float)$paid_amount; @endphp
            @if($remaining > 0)
            <x-mary-input
                label="Date limite solde"
                wire:model="due_date"
                type="date"
                icon="o-clock"
                hint="Reliquat : {{ number_format($remaining, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}"
            />
            @endif

            {{-- Troc --}}
            <div class="md:col-span-2">
                <x-mary-toggle label="Vente avec troc (produit repris)" wire:model.live="is_trade_in" />
            </div>

            @if($is_trade_in)
            <div class="md:col-span-2 border border-warning/30 bg-warning/5 rounded-lg p-4 space-y-3">
                <p class="text-sm font-medium text-warning">Produit repris en échange</p>
                <div class="flex gap-2">
                    <x-mary-input
                        wire:model="trade_in_imei"
                        placeholder="IMEI du produit repris"
                        class="flex-1"
                        icon="o-qr-code"
                    />
                    <x-mary-button icon="o-magnifying-glass" class="btn-warning" wire:click="searchTradeIn" />
                </div>
                @error('trade_in_imei')
                    <p class="text-error text-xs">{{ $message }}</p>
                @enderror
                <div class="grid grid-cols-2 gap-3">
                    <x-mary-input
                        label="Valeur estimée"
                        wire:model.live="trade_in_value"
                        type="number"
                        :suffix="config('boutique.devise_symbole')"
                        icon="o-banknotes"
                    />
                    <x-mary-input
                        label="Notes (état du produit)"
                        wire:model="trade_in_notes"
                        placeholder="Ex: écran fissuré, batterie ok"
                    />
                </div>
            </div>
            @endif

            <x-mary-textarea
                label="Notes"
                wire:model="notes"
                class="md:col-span-2"
                rows="2"
                placeholder="Remarques sur cette vente..."
            />
        </div>

        <div class="flex justify-between mt-4">
            <x-mary-button label="← Retour" wire:click="prevStep" class="btn-ghost" />
            <x-mary-button label="Récapitulatif →" wire:click="nextStep" class="btn-primary" />
        </div>
    </x-mary-card>
    @endif

    {{-- Étape 4 : Récapitulatif --}}
    @if($step === 4)
    <div class="space-y-6">
        <x-mary-card title="Récapitulatif de la vente">

            {{-- Client --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <p class="text-xs text-gray-400">Client</p>
                    <p class="font-medium">
                        @if($customer_type === 'reseller')
                            {{ \App\Models\Reseller::find($reseller_id)?->name ?? '—' }}
                            <span class="badge badge-primary badge-xs ml-1">Revendeur</span>
                        @else
                            {{ $customer_name ?: 'Client anonyme' }}
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Paiement</p>
                    <p class="font-medium">{{ \App\Enums\PaymentMethod::from($payment_method)->label() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Montant payé</p>
                    <p class="font-medium text-success">
                        {{ number_format((float)$paid_amount, 0, ',', ' ') }}
                        {{ config('boutique.devise_symbole') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Total net</p>
                    <p class="font-bold text-lg text-primary">
                        {{ number_format($this->getTotal(), 0, ',', ' ') }}
                        {{ config('boutique.devise_symbole') }}
                    </p>
                </div>
            </div>

            <div class="divider">Produits</div>

            @foreach($items as $item)
            <div class="flex justify-between items-center py-2 border-b border-base-200 last:border-0">
                <div>
                    <p class="font-medium text-sm">{{ $item['name'] }}</p>
                    <p class="text-xs font-mono text-gray-400">{{ $item['identifier'] }}</p>
                    @if($item['discount'] > 0)
                    <p class="text-xs text-warning">Remise: {{ number_format($item['discount'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</p>
                    @endif
                </div>
                <p class="font-bold text-sm">
                    {{ number_format($item['line_total'], 0, ',', ' ') }}
                    {{ config('boutique.devise_symbole') }}
                </p>
            </div>
            @endforeach

            @if($is_trade_in && (float)$trade_in_value > 0)
            <div class="flex justify-between items-center py-2 text-warning">
                <span class="text-sm">Troc déduit</span>
                <span class="font-bold">- {{ number_format((float)$trade_in_value, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
            </div>
            @endif
        </x-mary-card>

        <div class="flex justify-between">
            <x-mary-button label="← Modifier" wire:click="prevStep" class="btn-ghost" />
            <x-mary-button
                label="Valider la vente"
                icon="o-check"
                class="btn-success btn-lg"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
            />
        </div>
    </div>
    @endif
</div>
