<div>
    <x-mary-header title="Ventes" subtitle="Historique des ventes" icon="o-shopping-bag">
        <x-slot:actions>
            <x-mary-button
                label="Nouvelle vente"
                icon="o-plus"
                class="btn-primary btn-sm"
                link="{{ route('sales.create') }}"
            />
        </x-slot:actions>
    </x-mary-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-mary-stat
            title="CA aujourd'hui"
            :value="number_format($stats['ca_today'], 0, ',', ' ') . ' ' . config('boutique.devise_symbole')"
            icon="o-sun"
            color="text-warning"
        />
        <x-mary-stat
            title="CA ce mois"
            :value="number_format($stats['ca_month'], 0, ',', ' ') . ' ' . config('boutique.devise_symbole')"
            icon="o-calendar"
            color="text-primary"
        />
        <x-mary-stat
            title="Ventes aujourd'hui"
            :value="$stats['count_today']"
            icon="o-shopping-bag"
        />
        <x-mary-stat
            title="Créances clients"
            :value="number_format($stats['unpaid_total'], 0, ',', ' ') . ' ' . config('boutique.devise_symbole')"
            icon="o-exclamation-triangle"
            color="text-error"
        />
    </div>

    {{-- Filtres --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <x-mary-input
            wire:model.live.debounce="search"
            placeholder="Réf, client, téléphone..."
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
            wire:model.live="typeFilter"
            :options="[
                ['id'=>'','name'=>'Tous les types'],
                ['id'=>'client','name'=>'Client particulier'],
                ['id'=>'reseller','name'=>'Revendeur'],
            ]"
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
            ['key'=>'customer',       'label'=>'Client'],
            ['key'=>'items_count',    'label'=>'Articles'],
            ['key'=>'total_amount',   'label'=>'Total'],
            ['key'=>'paid_amount',    'label'=>'Payé'],
            ['key'=>'payment_status', 'label'=>'Paiement'],
            ['key'=>'sale_status',    'label'=>'Statut'],
            ['key'=>'created_at',     'label'=>'Date'],
            ['key'=>'actions',        'label'=>''],
        ]" :rows="$sales" with-pagination>

            @scope('cell_customer', $sale)
                @if($sale->customer_type === 'reseller')
                    <span class="font-medium">{{ $sale->reseller?->name ?? '—' }}</span>
                    <span class="badge badge-primary badge-xs ml-1">Rev.</span>
                @else
                    {{ $sale->customer_name ?: 'Anonyme' }}
                    @if($sale->customer_phone)
                        <p class="text-xs text-gray-400">{{ $sale->customer_phone }}</p>
                    @endif
                @endif
            @endscope

            @scope('cell_items_count', $sale)
                {{ $sale->items->count() }} art.
            @endscope

            @scope('cell_total_amount', $sale)
                <span class="font-medium">
                    {{ number_format($sale->total_amount, 0, ',', ' ') }}
                    {{ config('boutique.devise_symbole') }}
                </span>
            @endscope

            @scope('cell_paid_amount', $sale)
                <span class="{{ $sale->paid_amount >= $sale->total_amount ? 'text-success' : 'text-warning' }} font-medium">
                    {{ number_format($sale->paid_amount, 0, ',', ' ') }}
                    {{ config('boutique.devise_symbole') }}
                </span>
            @endscope

            @scope('cell_payment_status', $sale)
                <x-mary-badge
                    value="{{ $sale->payment_status->label() }}"
                    class="badge-sm badge-{{ $sale->payment_status->color() }}"
                />
            @endscope

            @scope('cell_sale_status', $sale)
                @php
                    $color = match($sale->sale_status) {
                        'completed'      => 'success',
                        'cancelled'      => 'error',
                        'partial_return' => 'warning',
                        'full_return'    => 'ghost',
                        default          => 'ghost',
                    };
                    $label = match($sale->sale_status) {
                        'completed'      => 'Complétée',
                        'cancelled'      => 'Annulée',
                        'partial_return' => 'Retour partiel',
                        'full_return'    => 'Retour total',
                        default          => $sale->sale_status,
                    };
                @endphp
                <x-mary-badge value="{{ $label }}" class="badge-sm badge-{{ $color }}" />
            @endscope

            @scope('cell_created_at', $sale)
                {{ $sale->created_at->format('d/m/Y H:i') }}
            @endscope

            @scope('cell_actions', $sale)
                <div class="flex gap-2 justify-end">
                    <x-mary-button
                        icon="o-eye"
                        class="btn-ghost btn-xs"
                        link="{{ route('sales.show', $sale->id) }}"
                        tooltip="Voir"
                    />
                </div>
            @endscope

        </x-mary-table>
    </x-mary-card>
</div>
