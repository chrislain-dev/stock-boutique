<div>
    <x-mary-header
        title="{{ $product->productModel->full_name }}"
        subtitle="{{ $product->identifier }}"
        icon="o-device-phone-mobile"
    >
        <x-slot:actions>
            <x-mary-button
                label="Retour"
                icon="o-arrow-left"
                class="btn-ghost btn-sm"
                link="{{ route('products.index') }}"
            />
            @if(auth()->user()->isAdmin())
            <x-mary-button
                label="Modifier"
                icon="o-pencil"
                class="btn-primary btn-sm"
                link="{{ route('products.edit', $product->id) }}"
            />
            @endif
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Colonne gauche — infos principales --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Infos produit --}}
            <x-mary-card title="Informations">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                    <div>
                        <p class="text-xs text-gray-400">Catégorie</p>
                        <p class="font-medium">{{ $product->productModel->category->label() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Marque</p>
                        <p class="font-medium">{{ $product->productModel->brand->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Condition</p>
                        <x-mary-badge
                            value="{{ $product->condition->label() }}"
                            class="badge-sm badge-{{ $product->condition->color() }}"
                        />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">État</p>
                        <x-mary-badge
                            value="{{ $product->state->label() }}"
                            class="badge-sm badge-{{ $product->state->color() }}"
                        />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Localisation</p>
                        <p class="font-medium">{{ $product->location->label() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Date d'achat</p>
                        <p class="font-medium">{{ $product->purchase_date?->format('d/m/Y') ?? '—' }}</p>
                    </div>

                    @if($product->imei)
                    <div>
                        <p class="text-xs text-gray-400">IMEI</p>
                        <p class="font-mono text-sm">{{ $product->imei }}</p>
                    </div>
                    @endif

                    @if($product->serial_number)
                    <div>
                        <p class="text-xs text-gray-400">Numéro de série</p>
                        <p class="font-mono text-sm">{{ $product->serial_number }}</p>
                    </div>
                    @endif

                    @if($product->supplier)
                    <div>
                        <p class="text-xs text-gray-400">Fournisseur</p>
                        <p class="font-medium">{{ $product->supplier->name }}</p>
                    </div>
                    @endif

                    @if($product->defects)
                    <div class="col-span-full">
                        <p class="text-xs text-gray-400">Défauts</p>
                        <p class="text-sm text-warning">{{ $product->defects }}</p>
                    </div>
                    @endif

                    @if($product->notes)
                    <div class="col-span-full">
                        <p class="text-xs text-gray-400">Notes</p>
                        <p class="text-sm">{{ $product->notes }}</p>
                    </div>
                    @endif

                </div>
            </x-mary-card>

            {{-- Historique mouvements --}}
            <x-mary-card title="Mouvements de stock">
                @forelse($product->stockMovements as $movement)
                <div class="flex items-center justify-between py-2 border-b border-base-200 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            {{ $movement->type->isPositive() ? 'bg-success/10' : 'bg-error/10' }}">
                            <x-mary-icon
                                name="{{ $movement->type->isPositive() ? 'o-arrow-down' : 'o-arrow-up' }}"
                                class="w-4 h-4 {{ $movement->type->isPositive() ? 'text-success' : 'text-error' }}"
                            />
                        </div>
                        <div>
                            <p class="text-sm font-medium">{{ $movement->type->label() }}</p>
                            @if($movement->notes)
                                <p class="text-xs text-gray-400">{{ $movement->notes }}</p>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-gray-400">{{ $movement->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">Aucun mouvement enregistré.</p>
                @endforelse
            </x-mary-card>

            {{-- Historique prix --}}
            @if(auth()->user()->isAdmin() && $product->priceHistory->count() > 0)
            <x-mary-card title="Historique des prix">
                @foreach($product->priceHistory as $history)
                <div class="py-2 border-b border-base-200 last:border-0">
                    <div class="flex justify-between items-center">
                        <p class="text-xs text-gray-400">{{ $history->created_at->format('d/m/Y H:i') }}</p>
                        <p class="text-xs text-gray-400">{{ $history->reason }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mt-1">
                        <div>
                            <p class="text-xs text-gray-400">Achat</p>
                            <p class="text-sm">
                                <span class="line-through text-gray-400">{{ number_format($history->old_purchase_price, 0, ',', ' ') }}</span>
                                →
                                <span class="font-medium">{{ number_format($history->new_purchase_price, 0, ',', ' ') }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Client</p>
                            <p class="text-sm">
                                <span class="line-through text-gray-400">{{ number_format($history->old_client_price, 0, ',', ' ') }}</span>
                                →
                                <span class="font-medium">{{ number_format($history->new_client_price, 0, ',', ' ') }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Revendeur</p>
                            <p class="text-sm">
                                <span class="line-through text-gray-400">{{ number_format($history->old_reseller_price, 0, ',', ' ') }}</span>
                                →
                                <span class="font-medium">{{ number_format($history->new_reseller_price, 0, ',', ' ') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </x-mary-card>
            @endif

        </div>

        {{-- Colonne droite — prix + meta --}}
        <div class="space-y-6">

            {{-- Prix --}}
            <x-mary-card title="Prix">
                @if(auth()->user()->isAdmin())
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Prix d'achat</span>
                        <span class="font-medium">
                            {{ number_format($product->purchase_price, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Prix client</span>
                        <span class="font-medium text-primary">
                            {{ number_format($product->client_price, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Prix revendeur</span>
                        <span class="font-medium text-info">
                            {{ number_format($product->reseller_price, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    <div class="divider my-1"></div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Marge client</span>
                        <span class="font-bold text-success">
                            {{ number_format($product->margin, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                            ({{ $product->margin_percent }}%)
                        </span>
                    </div>
                </div>
                @else
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Prix client</span>
                        <span class="font-medium text-primary">
                            {{ number_format($product->client_price, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Prix revendeur</span>
                        <span class="font-medium text-info">
                            {{ number_format($product->reseller_price, 0, ',', ' ') }}
                            {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                </div>
                @endif
            </x-mary-card>

            {{-- Meta --}}
            <x-mary-card title="Traçabilité">
                <div class="space-y-2">
                    <div>
                        <p class="text-xs text-gray-400">Créé le</p>
                        <p class="text-sm">{{ $product->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($product->createdBy)
                    <div>
                        <p class="text-xs text-gray-400">Créé par</p>
                        <p class="text-sm">{{ $product->createdBy->name }}</p>
                    </div>
                    @endif
                    @if($product->updatedBy && $product->updated_at != $product->created_at)
                    <div>
                        <p class="text-xs text-gray-400">Modifié le</p>
                        <p class="text-sm">{{ $product->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Modifié par</p>
                        <p class="text-sm">{{ $product->updatedBy->name }}</p>
                    </div>
                    @endif
                </div>
            </x-mary-card>

        </div>
    </div>
</div>
