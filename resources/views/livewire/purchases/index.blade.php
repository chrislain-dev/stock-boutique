<div>
    <x-mary-header title="Achats fournisseurs" subtitle="Entrées de stock" icon="o-shopping-cart">
        <x-slot:actions>
            <x-mary-button
                label="Nouvel achat"
                icon="o-plus"
                class="btn-primary btn-sm"
                link="{{ route('purchases.create') }}"
            />
        </x-slot:actions>
    </x-mary-header>

    {{-- Stats rapides --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <x-mary-stat
            title="Achats ce mois"
            :value="number_format($stats['total_month'], 0, ',', ' ') . ' ' . config('boutique.devise_symbole')"
            icon="o-calendar"
            color="text-primary"
        />
        <x-mary-stat
            title="Commandes ce mois"
            :value="$stats['count_month']"
            icon="o-clipboard-document-list"
        />
        <x-mary-stat
            title="Dettes fournisseurs"
            :value="number_format($stats['unpaid'], 0, ',', ' ') . ' ' . config('boutique.devise_symbole')"
            icon="o-exclamation-triangle"
            color="text-error"
        />
    </div>

    {{-- Filtres --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <x-mary-input
            wire:model.live.debounce="search"
            placeholder="Référence, fournisseur..."
            icon="o-magnifying-glass"
            clearable
        />
        <x-mary-select
            wire:model.live="statusFilter"
            :options="$statuses"
            option-value="id"
            option-label="name"
        />
        <x-mary-select
            wire:model.live="supplierFilter"
            :options="$suppliers"
            option-value="id"
            option-label="name"
        />
        <div class="flex gap-2">
            <x-mary-input wire:model.live="dateFrom" type="date" class="flex-1" />
            <x-mary-input wire:model.live="dateTo"   type="date" class="flex-1" />
        </div>
    </div>

    {{-- Table --}}
    <x-mary-card>
        <x-mary-table :headers="[
            ['key'=>'reference',      'label'=>'Référence'],
            ['key'=>'supplier',       'label'=>'Fournisseur'],
            ['key'=>'purchase_date',  'label'=>'Date'],
            ['key'=>'items_count',    'label'=>'Articles'],
            ['key'=>'total_amount',   'label'=>'Total'],
            ['key'=>'payment_status', 'label'=>'Paiement'],
            ['key'=>'status',         'label'=>'Statut'],
            ['key'=>'actions',        'label'=>''],
        ]" :rows="$purchases" with-pagination>

            @scope('cell_supplier', $purchase)
                {{ $purchase->supplier->name }}
            @endscope

            @scope('cell_purchase_date', $purchase)
                {{ $purchase->purchase_date->format('d/m/Y') }}
            @endscope

            @scope('cell_items_count', $purchase)
                {{ $purchase->items->count() }} ligne(s)
            @endscope

            @scope('cell_total_amount', $purchase)
                <span class="font-medium">
                    {{ number_format($purchase->total_amount, 0, ',', ' ') }}
                    {{ config('boutique.devise_symbole') }}
                </span>
            @endscope

            @scope('cell_payment_status', $purchase)
                <x-mary-badge
                    value="{{ $purchase->payment_status->label() }}"
                    class="badge-sm badge-{{ $purchase->payment_status->color() }}"
                />
            @endscope

            @scope('cell_status', $purchase)
                @php
                    $color = match($purchase->status) {
                        'received'  => 'success',
                        'pending'   => 'warning',
                        'cancelled' => 'error',
                        default     => 'ghost',
                    };
                    $label = match($purchase->status) {
                        'received'  => 'Reçu',
                        'pending'   => 'En attente',
                        'cancelled' => 'Annulé',
                        default     => $purchase->status,
                    };
                @endphp
                <x-mary-badge value="{{ $label }}" class="badge-sm badge-{{ $color }}" />
            @endscope

            @scope('cell_actions', $purchase)
                <div class="flex gap-2 justify-end">
                    <x-mary-button
                        icon="o-eye"
                        class="btn-ghost btn-xs"
                        link="{{ route('purchases.show', $purchase->id) }}"
                        tooltip="Voir"
                    />
                    @if(auth()->user()->isAdmin())
                    <x-mary-button
                        icon="o-pencil"
                        class="btn-ghost btn-xs"
                        link="{{ route('purchases.edit', $purchase->id) }}"
                        tooltip="Modifier"
                    />
                    @endif
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>
</div>
