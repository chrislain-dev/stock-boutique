<div>
    <x-mary-header
        title="Modifier le produit"
        subtitle="{{ $product->identifier }}"
        icon="o-pencil"
    >
        <x-slot:actions>
            <x-mary-button
                label="Voir le détail"
                icon="o-eye"
                class="btn-ghost btn-sm"
                link="{{ route('products.show', $product->id) }}"
            />
            <x-mary-button
                label="Retour à la liste"
                icon="o-arrow-left"
                class="btn-ghost btn-sm"
                link="{{ route('products.index') }}"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Colonne gauche --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Identification --}}
                <x-mary-card title="Identification">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-mary-select
                            label="Modèle"
                            wire:model="product_model_id"
                            :options="$productModels"
                            placeholder="Choisir un modèle"
                            class="md:col-span-2"
                            searchable
                            required
                        />
                        <x-mary-input
                            label="IMEI"
                            wire:model="imei"
                            placeholder="Ex: 358843000000000"
                            icon="o-identification"
                        />
                        <x-mary-input
                            label="Numéro de série"
                            wire:model="serial_number"
                            placeholder="Ex: C02X1234"
                            icon="o-qr-code"
                        />
                    </div>
                </x-mary-card>

                {{-- État & localisation --}}
                <x-mary-card title="État & Localisation">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-mary-select
                            label="Condition"
                            wire:model="condition"
                            :options="collect($conditions)->map(fn($c) => ['id' => $c->value, 'name' => $c->label()])"
                            required
                        />
                        <x-mary-select
                            label="État"
                            wire:model="state"
                            :options="collect($states)->map(fn($s) => ['id' => $s->value, 'name' => $s->label()])"
                            required
                        />
                        <x-mary-select
                            label="Localisation"
                            wire:model="location"
                            :options="collect($locations)->map(fn($l) => ['id' => $l->value, 'name' => $l->label()])"
                            required
                        />
                    </div>
                </x-mary-card>

                {{-- Infos achat --}}
                <x-mary-card title="Informations d'achat">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-mary-input
                            label="Date d'achat"
                            wire:model="purchase_date"
                            type="date"
                            required
                        />
                        <x-mary-select
                            label="Fournisseur"
                            wire:model="supplier_id"
                            :options="$suppliers"
                            placeholder="Choisir (optionnel)"
                        />
                    </div>
                    <x-mary-textarea
                        label="Défauts constatés"
                        wire:model="defects"
                        placeholder="Laisser vide si aucun défaut"
                        rows="2"
                    />
                    <x-mary-textarea
                        label="Notes"
                        wire:model="notes"
                        placeholder="Notes internes..."
                        rows="2"
                    />
                </x-mary-card>

            </div>

            {{-- Colonne droite — prix --}}
            <div class="space-y-4">

                <x-mary-card title="Prix">
                    <x-mary-input
                        label="Prix d'achat"
                        wire:model.live="purchase_price"
                        type="number"
                        step="0.01"
                        :hint="config('boutique.devise')"
                        required
                    />
                    <x-mary-input
                        label="Prix client"
                        wire:model.live="client_price"
                        type="number"
                        step="0.01"
                        :hint="config('boutique.devise')"
                        required
                    />
                    <x-mary-input
                        label="Prix revendeur"
                        wire:model.live="reseller_price"
                        type="number"
                        step="0.01"
                        :hint="config('boutique.devise')"
                        required
                    />

                    {{-- Marge visible admin --}}
                    @if(auth()->user()->isAdmin() && $client_price > 0 && $purchase_price > 0)
                    <div class="mt-3 p-3 bg-base-200 rounded-lg">
                        <p class="text-xs text-gray-500">Marge client</p>
                        <p class="font-bold text-success">
                            {{ number_format($client_price - $purchase_price, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                            ({{ $purchase_price > 0 ? round((($client_price - $purchase_price) / $purchase_price) * 100, 1) : 0 }}%)
                        </p>
                    </div>
                    @endif

                    {{-- Raison du changement de prix --}}
                    @if($purchase_price != $original_purchase_price
                        || $client_price != $original_client_price
                        || $reseller_price != $original_reseller_price)
                    <div class="mt-3">
                        <x-mary-alert
                            title="Prix modifié"
                            description="Veuillez indiquer la raison du changement."
                            icon="o-exclamation-triangle"
                            class="alert-warning mb-3"
                        />
                        <x-mary-input
                            label="Raison du changement"
                            wire:model="price_change_reason"
                            placeholder="Ex: Ajustement marché, Promotion..."
                            required
                        />
                    </div>
                    @endif
                </x-mary-card>

                {{-- Boutons --}}
                <x-mary-card>
                    <x-mary-button
                        label="Annuler"
                        icon="o-x-mark"
                        class="btn-ghost w-full mb-2"
                        link="{{ route('products.show', $product->id) }}"
                    />
                    <x-mary-button
                        label="Enregistrer"
                        icon="o-check"
                        type="submit"
                        class="btn-primary w-full"
                        spinner="save"
                    />
                </x-mary-card>

            </div>

        </div>
    </x-mary-form>
</div>
