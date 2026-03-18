<div>
    <x-mary-header title="Nouvel achat" subtitle="Entrée de stock fournisseur" icon="o-shopping-cart">
        <x-slot:actions>
            <x-mary-button label="Annuler" icon="o-x-mark" class="btn-ghost btn-sm" link="{{ route('purchases.index') }}" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Steps --}}
    <div class="flex items-center mb-8">
        @foreach([1 => 'Informations', 2 => 'Produits', 3 => 'Récapitulatif'] as $num => $label)
        <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                    {{ $step > $num ? 'bg-success text-white' : ($step === $num ? 'bg-primary text-white' : 'bg-base-300 text-gray-500') }}">
                    @if($step > $num)
                        ✓
                    @else
                        {{ $num }}
                    @endif
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

    {{-- Étape 1 : Infos achat --}}
    @if($step === 1)
    <x-mary-card title="Informations de l'achat">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-mary-select
                label="Fournisseur *"
                wire:model="supplier_id"
                :options="$suppliers"
                option-value="id"
                option-label="name"
                placeholder="Sélectionner un fournisseur"
                icon="o-building-office"
            />
            <x-mary-input
                label="Date d'achat *"
                wire:model="purchase_date"
                type="date"
                icon="o-calendar"
            />
            <x-mary-select
                label="Statut paiement *"
                wire:model.live="payment_status"
                :options="$paymentStatuses"
                option-value="id"
                option-label="name"
                icon="o-banknotes"
            />
            @if($payment_status !== 'unpaid')
            <x-mary-select
                label="Mode de paiement"
                wire:model="payment_method"
                :options="$paymentMethods"
                option-value="id"
                option-label="name"
                icon="o-credit-card"
            />
            <x-mary-input
                label="Montant payé"
                wire:model="paid_amount"
                type="number"
                icon="o-banknotes"
                suffix="{{ config('boutique.devise_symbole') }}"
            />
            <x-mary-input
                label="Référence transaction"
                wire:model="transaction_reference"
                placeholder="Ex: TXN-123456"
                icon="o-hashtag"
            />
            @endif
            
            @if($payment_status === 'partial' || $payment_status === 'unpaid')
            <x-mary-input
                label="Date limite paiement"
                wire:model="due_date"
                type="date"
                icon="o-clock"
                hint="Date à laquelle le solde doit être réglé"
            />
            @endif
            <x-mary-textarea
                label="Notes"
                wire:model="notes"
                placeholder="Remarques sur cet achat..."
                class="md:col-span-2"
                rows="2"
            />
        </div>
        <div class="flex justify-end mt-4">
            <x-mary-button label="Suivant →" wire:click="nextStep" class="btn-primary" />
        </div>
    </x-mary-card>
    @endif

    {{-- Étape 2 : Produits --}}
    @if($step === 2)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Formulaire ajout ligne --}}
        <x-mary-card title="Ajouter un produit">
            <div class="space-y-4">
                <x-mary-select
                    label="Modèle *"
                    wire:model.live="line_product_model_id"
                    :options="$productModels"
                    option-value="id"
                    option-label="name"
                    placeholder="Choisir un modèle..."
                    icon="o-device-phone-mobile"
                    searchable
                />

                @if($line_product_model_id)
                <div class="grid grid-cols-3 gap-3">
                    <x-mary-input
                        label="Prix achat *"
                        wire:model="line_unit_purchase_price"
                        type="number"
                        icon="o-banknotes"
                        :suffix="config('boutique.devise_symbole')"
                    />
                    <x-mary-input
                        label="Prix client *"
                        wire:model="line_unit_client_price"
                        type="number"
                        :suffix="config('boutique.devise_symbole')"
                    />
                    <x-mary-input
                        label="Prix revendeur *"
                        wire:model="line_unit_reseller_price"
                        type="number"
                        :suffix="config('boutique.devise_symbole')"
                    />
                </div>

                <x-mary-select
                    label="Condition"
                    wire:model="line_condition"
                    :options="$conditions"
                    option-value="id"
                    option-label="name"
                />

                {{-- Sérialisé : IMEI / Serial --}}
                @if($line_is_serialized)
                <div>
                    <p class="text-sm font-medium mb-2">Mode de saisie des identifiants</p>
                    <div class="flex gap-3 mb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="line_imei_mode" value="manual" class="radio radio-sm radio-primary" />
                            <span class="text-sm">Manuel</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="line_imei_mode" value="bulk" class="radio radio-sm radio-primary" />
                            <span class="text-sm">Copier-coller</span>
                        </label>
                    </div>

                    @if($line_imei_mode === 'manual')
                    <div class="space-y-2">
                        @foreach($line_imei_list as $idx => $val)
                        <div class="flex gap-2">
                            <x-mary-input
                                wire:model="line_imei_list.{{ $idx }}"
                                placeholder="IMEI ou numéro de série"
                                class="flex-1"
                            />
                            @if(count($line_imei_list) > 1)
                            <x-mary-button
                                icon="o-trash"
                                class="btn-ghost btn-sm text-error"
                                wire:click="removeImeiField({{ $idx }})"
                            />
                            @endif
                        </div>
                        @endforeach
                        <x-mary-button
                            label="+ Ajouter un champ"
                            class="btn-ghost btn-sm"
                            wire:click="addImeiField"
                        />
                    </div>
                    @else
                    <x-mary-textarea
                        wire:model="line_imei_bulk"
                        placeholder="Coller les IMEI/numéros de série (un par ligne)"
                        rows="5"
                        hint="Un identifiant par ligne"
                    />
                    @endif
                </div>
                @else
                {{-- Non sérialisé : quantité --}}
                <x-mary-input
                    label="Quantité *"
                    wire:model="line_quantity"
                    type="number"
                    min="1"
                    icon="o-hashtag"
                />
                @endif

                <x-mary-input
                    label="Notes ligne"
                    wire:model="line_notes"
                    placeholder="Optionnel"
                    icon="o-chat-bubble-left"
                />
                @endif

                <x-mary-button
                    label="Ajouter au bon de commande"
                    icon="o-plus"
                    class="btn-primary w-full"
                    wire:click="addLine"
                />
            </div>
        </x-mary-card>

        {{-- Récap lignes ajoutées --}}
        <x-mary-card title="Bon de commande ({{ count($items) }} ligne(s))">
            @forelse($items as $index => $item)
            <div class="border border-base-200 rounded-lg p-3 mb-3">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="font-medium text-sm">{{ $item['model_name'] }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $item['quantity'] }} unité(s) ×
                            {{ number_format($item['unit_purchase_price'], 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </p>
                        @if($item['is_serialized'] && !empty($item['identifiers']))
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach($item['identifiers'] as $id)
                            <span class="badge badge-ghost badge-xs font-mono">{{ $id }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="text-right ml-3">
                        <p class="font-bold text-sm">
                            {{ number_format($item['line_total'], 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </p>
                        <x-mary-button
                            icon="o-trash"
                            class="btn-ghost btn-xs text-error mt-1"
                            wire:click="removeLine({{ $index }})"
                        />
                    </div>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-8">Aucune ligne ajoutée.</p>
            @endforelse

            @if(count($items) > 0)
            <div class="divider"></div>
            <div class="flex justify-between font-bold">
                <span>Total</span>
                <span>
                    {{ number_format(collect($items)->sum('line_total'), 0, ',', ' ') }}
                    {{ config('boutique.devise_symbole') }}
                </span>
            </div>
            @endif
        </x-mary-card>
    </div>

    <div class="flex justify-between mt-4">
        <x-mary-button label="← Retour" wire:click="prevStep" class="btn-ghost" />
        <x-mary-button
            label="Récapitulatif →"
            wire:click="nextStep"
            class="btn-primary"
            :disabled="empty($items)"
        />
    </div>
    @endif

    {{-- Étape 3 : Récapitulatif --}}
    @if($step === 3)
    <div class="space-y-6">
        <x-mary-card title="Récapitulatif de l'achat">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <p class="text-xs text-gray-400">Fournisseur</p>
                    <p class="font-medium">
                        {{ \App\Models\Supplier::find($supplier_id)?->name ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Date</p>
                    <p class="font-medium">{{ \Carbon\Carbon::parse($purchase_date)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Statut paiement</p>
                    <p class="font-medium">{{ \App\Enums\PaymentStatus::from($payment_status)->label() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Total</p>
                    <p class="font-bold text-lg text-primary">
                        {{ number_format(collect($items)->sum('line_total'), 0, ',', ' ') }}
                        {{ config('boutique.devise_symbole') }}
                    </p>
                </div>
            </div>

            <div class="divider">Lignes produits</div>

            @foreach($items as $item)
            <div class="flex justify-between items-center py-2 border-b border-base-200 last:border-0">
                <div>
                    <p class="font-medium text-sm">{{ $item['model_name'] }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $item['quantity'] }} unité(s) ·
                        Achat: {{ number_format($item['unit_purchase_price'], 0, ',', ' ') }} ·
                        Client: {{ number_format($item['unit_client_price'], 0, ',', ' ') }}
                        {{ config('boutique.devise_symbole') }}
                    </p>
                    @if(!empty($item['identifiers']))
                    <p class="text-xs text-gray-400 font-mono">
                        {{ implode(', ', $item['identifiers']) }}
                    </p>
                    @endif
                </div>
                <p class="font-bold text-sm">
                    {{ number_format($item['line_total'], 0, ',', ' ') }}
                    {{ config('boutique.devise_symbole') }}
                </p>
            </div>
            @endforeach
        </x-mary-card>

        <div class="flex justify-between">
            <x-mary-button label="← Modifier" wire:click="prevStep" class="btn-ghost" />
            <x-mary-button
                label="Valider l'achat"
                icon="o-check"
                class="btn-success"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
            />
        </div>
    </div>
    @endif
</div>
