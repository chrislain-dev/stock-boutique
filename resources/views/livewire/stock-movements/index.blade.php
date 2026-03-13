<div>
    <x-mary-header title="Mouvements de stock" subtitle="Historique complet" icon="o-arrows-right-left">
        <x-slot:actions>
            @if(auth()->user()->hasPermission('adjust_stock'))
            <x-mary-button
                label="Ajustement manuel"
                icon="o-wrench-screwdriver"
                class="btn-warning btn-sm"
                wire:click="openAdjustModal"
            />
            @endif
        </x-slot:actions>
    </x-mary-header>

    {{-- Filtres --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <x-mary-input
            wire:model.live.debounce="search"
            placeholder="IMEI, modèle..."
            icon="o-magnifying-glass"
            clearable
        />
        <x-mary-select
            wire:model.live="typeFilter"
            :options="$types"
            option-value="id"
            option-label="name"
        />
        <x-mary-input wire:model.live="dateFrom" type="date" label="Du" />
        <x-mary-input wire:model.live="dateTo"   type="date" label="Au" />
    </div>

    {{-- Table --}}
    <x-mary-card>
        <x-mary-table :headers="[
            ['key'=>'type',       'label'=>'Type'],
            ['key'=>'product',    'label'=>'Produit'],
            ['key'=>'mouvement',  'label'=>'Mouvement'],
            ['key'=>'notes',      'label'=>'Notes'],
            ['key'=>'created_by', 'label'=>'Par'],
            ['key'=>'created_at', 'label'=>'Date'],
        ]" :rows="$movements" with-pagination>

            @scope('cell_type', $movement)
                <x-mary-badge
                    value="{{ $movement->type->label() }}"
                    class="badge-sm {{ $movement->type->isPositive() ? 'badge-success' : 'badge-error' }}"
                />
            @endscope

            @scope('cell_product', $movement)
                @if($movement->product)
                    <p class="text-sm font-medium">{{ $movement->product->productModel->display_label }}</p>
                    <p class="text-xs font-mono text-gray-400">
                        {{ $movement->product->imei ?? $movement->product->serial_number ?? '—' }}
                    </p>
                @elseif($movement->productModel)
                    <p class="text-sm">{{ $movement->productModel->display_label }}</p>
                @else
                    <span class="text-gray-400">—</span>
                @endif
            @endscope

            @scope('cell_mouvement', $movement)
                <div class="flex items-center gap-2 text-xs">
                    @if($movement->location_from)
                        <span class="badge badge-ghost badge-xs">{{ $movement->location_from }}</span>
                        <span>→</span>
                    @endif
                    @if($movement->location_to)
                        <span class="badge badge-ghost badge-xs">{{ $movement->location_to }}</span>
                    @endif
                    @if(!$movement->location_from && !$movement->location_to)
                        <span class="text-gray-400">—</span>
                    @endif
                </div>
            @endscope

            @scope('cell_notes', $movement)
                <span class="text-xs text-gray-500">{{ $movement->notes ?? '—' }}</span>
            @endscope

            @scope('cell_created_by', $movement)
                {{ $movement->createdBy?->name ?? '—' }}
            @endscope

            @scope('cell_created_at', $movement)
                {{ $movement->created_at->format('d/m/Y H:i') }}
            @endscope

        </x-mary-table>
    </x-mary-card>

    {{-- Modal ajustement --}}
    @if(auth()->user()->hasPermission('adjust_stock'))
    <x-mary-modal wire:model="showAdjustModal" title="Ajustement manuel de stock">
        <div class="space-y-4">
            {{-- Recherche produit --}}
            <div>
                <p class="text-sm font-medium mb-2">Produit concerné</p>
                <div class="flex gap-2">
                    <x-mary-input
                        wire:model="adjust_imei"
                        placeholder="IMEI ou numéro de série"
                        icon="o-qr-code"
                        class="flex-1"
                        wire:keydown.enter="searchAdjustProduct"
                    />
                    <x-mary-button
                        icon="o-magnifying-glass"
                        class="btn-primary"
                        wire:click="searchAdjustProduct"
                    />
                </div>
                @if($adjust_error)
                    <p class="text-error text-xs mt-1">{{ $adjust_error }}</p>
                @endif
                @if($adjust_product_id)
                    @php $p = \App\Models\Product::with('productModel')->find($adjust_product_id); @endphp
                    @if($p)
                    <div class="mt-2 p-2 bg-success/10 rounded text-sm">
                        ✓ {{ $p->productModel->display_label }}
                        — {{ $p->imei ?? $p->serial_number ?? '—' }}
                    </div>
                    @endif
                @endif
            </div>

            <x-mary-select
                label="Type de mouvement *"
                wire:model="adjust_type"
                :options="$adjustTypes"
                option-value="id"
                option-label="name"
                icon="o-arrows-right-left"
            />

            <div class="grid grid-cols-2 gap-3">
                <x-mary-select
                    label="De"
                    wire:model="adjust_location_from"
                    :options="$locations"
                    option-value="id"
                    option-label="name"
                    placeholder="Optionnel"
                />
                <x-mary-select
                    label="Vers"
                    wire:model="adjust_location_to"
                    :options="$locations"
                    option-value="id"
                    option-label="name"
                    placeholder="Optionnel"
                />
            </div>

            <x-mary-textarea
                label="Motif / Notes *"
                wire:model="adjust_notes"
                placeholder="Ex: Produit perdu, transfert vers dépôt, retour fournisseur..."
                rows="3"
                hint="Obligatoire pour traçabilité"
            />
        </div>
        <x-slot:actions>
            <x-mary-button label="Annuler" wire:click="$set('showAdjustModal', false)" class="btn-ghost" />
            <x-mary-button label="Enregistrer" wire:click="saveAdjustment" class="btn-warning" />
        </x-slot:actions>
    </x-mary-modal>
    @endif
</div>
