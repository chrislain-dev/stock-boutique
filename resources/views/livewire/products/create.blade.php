<div>
    <x-mary-header title="Ajouter des produits" icon="o-plus-circle">
        <x-slot:actions>
            <x-mary-button
                label="Retour"
                icon="o-arrow-left"
                class="btn-ghost btn-sm"
                link="{{ route('products.index') }}"
            />
        </x-slot:actions>
    </x-mary-header>

    {{-- ── STEP 1 — Choix du mode ─────────────────────────── --}}
    @if(!$mode)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto mt-8">

        <div wire:click="$set('mode', 'single')" class="cursor-pointer">
            <x-mary-card class="hover:shadow-lg hover:border-primary border-2 border-transparent transition-all text-center">
                <div class="flex flex-col items-center gap-4 py-6">
                    <x-mary-icon name="o-device-phone-mobile" class="w-16 h-16 text-primary" />
                    <div>
                        <p class="font-bold text-lg">Un produit</p>
                        <p class="text-sm text-gray-400 mt-1">Ajouter un seul produit avec son IMEI</p>
                    </div>
                </div>
            </x-mary-card>
        </div>

        <div wire:click="$set('mode', 'bulk')" class="cursor-pointer">
            <x-mary-card class="hover:shadow-lg hover:border-primary border-2 border-transparent transition-all text-center">
                <div class="flex flex-col items-center gap-4 py-6">
                    <x-mary-icon name="o-rectangle-stack" class="w-16 h-16 text-success" />
                    <div>
                        <p class="font-bold text-lg">Plusieurs IMEI</p>
                        <p class="text-sm text-gray-400 mt-1">Saisir ou coller une liste d'IMEI</p>
                    </div>
                </div>
            </x-mary-card>
        </div>

        <div wire:click="$set('mode', 'import')" class="cursor-pointer">
            <x-mary-card class="hover:shadow-lg hover:border-primary border-2 border-transparent transition-all text-center">
                <div class="flex flex-col items-center gap-4 py-6">
                    <x-mary-icon name="o-arrow-up-tray" class="w-16 h-16 text-info" />
                    <div>
                        <p class="font-bold text-lg">Import CSV</p>
                        <p class="text-sm text-gray-400 mt-1">Importer depuis un fichier CSV</p>
                    </div>
                </div>
            </x-mary-card>
        </div>

    </div>

    {{-- ── STEP 2 — Formulaire selon le mode ──────────────── --}}
    @else

    {{-- Résultats import/bulk --}}
    @if($showResults)
    <x-mary-card title="Rapport d'import" class="mb-6">
        @if(!empty($results['success']))
        <div class="mb-4">
            <p class="text-success font-semibold mb-2">✅ {{ count($results['success']) }} produit(s) ajouté(s) :</p>
            <div class="flex flex-wrap gap-2">
                @foreach($results['success'] as $imei)
                    <x-mary-badge value="{{ $imei }}" class="badge-success badge-sm font-mono" />
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($results['errors']))
        <div>
            <p class="text-error font-semibold mb-2">❌ {{ count($results['errors']) }} erreur(s) :</p>
            @foreach($results['errors'] as $error)
                <p class="text-sm text-error">• {{ $error }}</p>
            @endforeach
        </div>
        @endif

        <x-slot:actions>
            <x-mary-button
                label="Voir les produits"
                icon="o-eye"
                class="btn-primary btn-sm"
                link="{{ route('products.index') }}"
            />
            <x-mary-button
                label="Ajouter d'autres"
                icon="o-plus"
                class="btn-ghost btn-sm"
                wire:click="$set('showResults', false)"
            />
        </x-slot:actions>
    </x-mary-card>
    @endif

    <x-mary-form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Colonne gauche — infos communes --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Modèle --}}
                <x-mary-card title="Informations produit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-mary-select
                            label="Modèle"
                            wire:model.live="product_model_id"
                            :options="$productModels"
                            placeholder="Choisir un modèle"
                            class="md:col-span-2"
                            searchable
                            required
                        />
                        @if($product_model_id)
                        @php $selectedModel = $productModels->firstWhere('id', $product_model_id); @endphp
                        @if($selectedModel)
                        <div class="md:col-span-2 p-3 bg-base-200 rounded-lg text-sm">
                            <span class="text-gray-500">Modèle sélectionné : </span>
                            <span class="font-semibold">{{ $selectedModel['name'] }}</span>
                        </div>
                        @endif
                        @endif
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

                {{-- Selon le mode --}}
                @if($mode === 'single')
                <x-mary-card title="Identification">
                    <div class="grid grid-cols-2 gap-4">
                        @if($modelCategory === 'pc' || $modelCategory === 'tablet')
                        <x-mary-input
                            label="Numéro de série"
                            wire:model="serial_number"
                            placeholder="Ex: C02X1234JGH"
                            icon="o-qr-code"
                            hint="Numéro de série du fabricant"
                            class="col-span-2"
                        />
                        @else
                        <x-mary-input
                            label="IMEI"
                            wire:model="imei"
                            placeholder="Ex: 358843000000000"
                            icon="o-identification"
                            hint="15 chiffres — visible sur la boîte ou *#06#"
                            class="col-span-2"
                        />
                        @endif
                    </div>
                </x-mary-card>
                @endif

                @if($mode === 'bulk')
                <x-mary-card title="Liste d'IMEI">

                    {{-- Champs dynamiques --}}
                    <p class="text-sm font-medium mb-3">Saisie manuelle</p>
                    @foreach($imeiFields as $index => $imei)
                    <div class="flex gap-2 mb-2">
                        <x-mary-input
                            wire:model="imeiFields.{{ $index }}"
                            placeholder="IMEI {{ $index + 1 }}"
                            class="flex-1 font-mono"
                            icon="o-identification"
                        />
                        @if(count($imeiFields) > 1)
                        <x-mary-button
                            icon="o-x-mark"
                            class="btn-ghost btn-sm text-error"
                            wire:click="removeImeiField({{ $index }})"
                        />
                        @endif
                    </div>
                    @endforeach
                    <x-mary-button
                        label="Ajouter un IMEI"
                        icon="o-plus"
                        class="btn-ghost btn-sm mt-2"
                        wire:click="addImeiField"
                    />

                    <div class="divider">OU</div>

                    {{-- Coller liste --}}
                    <x-mary-textarea
                        label="Coller une liste d'IMEI (un par ligne)"
                        wire:model="imeiList"
                        placeholder="358843000000001&#10;358843000000002&#10;358843000000003"
                        rows="5"
                        class="font-mono"
                    />
                </x-mary-card>
                @endif

                @if($mode === 'import')
                <x-mary-card title="Import CSV">
                    <x-mary-alert
                        title="Format attendu"
                        description="Une colonne IMEI par ligne. La première ligne peut être un en-tête (ignoré automatiquement)."
                        icon="o-information-circle"
                        class="alert-info mb-4"
                    />
                    <x-mary-file
                        wire:model="csvFile"
                        label="Fichier CSV"
                        accept=".csv,.txt"
                        hint="Max 2MB"
                    />
                </x-mary-card>
                @endif

            </div>

            {{-- Colonne droite — prix --}}
            <div class="space-y-4">
                <x-mary-card title="Prix">
                    <x-mary-input
                        label="Prix d'achat"
                        wire:model="purchase_price"
                        type="number"
                        step="0.01"
                        :hint="config('boutique.devise')"
                        required
                    />
                    <x-mary-input
                        label="Prix client"
                        wire:model="client_price"
                        type="number"
                        step="0.01"
                        :hint="config('boutique.devise')"
                        required
                    />
                    <x-mary-input
                        label="Prix revendeur"
                        wire:model="reseller_price"
                        type="number"
                        step="0.01"
                        :hint="config('boutique.devise')"
                        required
                    />
                    @if(auth()->user()->isAdmin() && $purchase_price > 0 && $client_price > 0)
                    <div class="mt-3 p-3 bg-base-200 rounded-lg">
                        <p class="text-xs text-gray-500">Marge client</p>
                        <p class="font-bold text-success">
                            {{ number_format($client_price - $purchase_price, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                            ({{ round((($client_price - $purchase_price) / $purchase_price) * 100, 1) }}%)
                        </p>
                    </div>
                    @endif
                </x-mary-card>

                {{-- Boutons --}}
                <x-mary-card>
                    <x-mary-button
                        label="Annuler"
                        icon="o-x-mark"
                        class="btn-ghost w-full mb-2"
                        wire:click="$set('mode', '')"
                    />
                    <x-mary-button
                        label="{{ $mode === 'single' ? 'Ajouter le produit' : 'Importer' }}"
                        icon="o-check"
                        type="submit"
                        class="btn-primary w-full"
                        spinner="save"
                    />
                </x-mary-card>
            </div>

        </div>
    </x-mary-form>
    @endif

    {{-- Modal après sauvegarde --}}
    <x-mary-modal wire:model="showAfterSaveModal" title="Produit ajouté ✅">
        <p class="text-gray-600 mb-4">Que souhaitez-vous faire ?</p>
        <div class="flex flex-col gap-3">
            @if($lastCreatedProductId)
            <x-mary-button
                label="Voir le détail du produit"
                icon="o-eye"
                class="btn-primary w-full"
                wire:click="redirectToProduct"
            />
            @endif
            <x-mary-button
                label="Ajouter un autre produit"
                icon="o-plus"
                class="btn-outline w-full"
                wire:click="resetAfterSave"
            />
            <x-mary-button
                label="Retour à la liste"
                icon="o-list-bullet"
                class="btn-ghost w-full"
                wire:click="redirectToList"
            />
        </div>
    </x-mary-modal>

</div>
