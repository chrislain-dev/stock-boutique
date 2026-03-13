<div>
    <x-mary-header title="Rapports" subtitle="Analyse des performances" icon="o-chart-bar">
        <x-slot:actions>
            <x-mary-button label="Export PDF" icon="o-document-text" class="btn-outline btn-sm" wire:click="exportPdf" />
            <x-mary-button label="Export Excel" icon="o-table-cells" class="btn-outline btn-sm" wire:click="exportExcel" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Filtres période --}}
    <x-mary-card class="mb-6">
        <div class="flex flex-wrap gap-3 items-end">
            <x-mary-select
                label="Période"
                wire:model.live="period"
                :options="$periods"
                option-value="id"
                option-label="name"
                class="w-48"
            />
            @if($period === 'custom')
            <x-mary-input label="Du" wire:model.live="dateFrom" type="date" />
            <x-mary-input label="Au" wire:model.live="dateTo"   type="date" />
            @else
            <div class="text-sm text-gray-400 pt-6">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
                →
                {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            </div>
            @endif
        </div>
    </x-mary-card>

    {{-- Tabs --}}
    <div class="tabs tabs-bordered mb-6">
        @foreach($tabs as $tab)
        <button
            class="tab {{ $activeTab === $tab['id'] ? 'tab-active' : '' }}"
            wire:click="$set('activeTab', '{{ $tab['id'] }}')"
        >
            {{ $tab['name'] }}
        </button>
        @endforeach
    </div>

    {{-- CA par période --}}
    @if($activeTab === 'ca')
    <div class="space-y-4">
        <div class="grid grid-cols-3 gap-4">
            <x-mary-stat title="CA total" :value="number_format($caStats['total_ca'], 0, ',', ' ') . ' ' . config('boutique.devise_symbole')" icon="o-banknotes" color="text-primary" />
            <x-mary-stat title="Encaissé" :value="number_format($caStats['total_paid'], 0, ',', ' ') . ' ' . config('boutique.devise_symbole')" icon="o-check-circle" color="text-success" />
            <x-mary-stat title="Nb ventes" :value="$caStats['total_count']" icon="o-shopping-bag" />
        </div>

        <x-mary-card title="Détail par jour">
            <x-mary-table :headers="[
                ['key'=>'date',  'label'=>'Date'],
                ['key'=>'count', 'label'=>'Ventes'],
                ['key'=>'total', 'label'=>'CA'],
                ['key'=>'paid',  'label'=>'Encaissé'],
                ['key'=>'unpaid','label'=>'Reste dû'],
            ]" :rows="$caStats['rows']->toArray()">

                @scope('cell_date', $row)
                    {{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}
                @endscope

                @scope('cell_total', $row)
                    <span class="font-medium">{{ number_format($row['total'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
                @endscope

                @scope('cell_paid', $row)
                    <span class="text-success">{{ number_format($row['paid'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
                @endscope

                @scope('cell_unpaid', $row)
                    @php $unpaid = $row['total'] - $row['paid']; @endphp
                    <span class="{{ $unpaid > 0 ? 'text-error' : 'text-success' }}">
                        {{ number_format($unpaid, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                    </span>
                @endscope

            </x-mary-table>
        </x-mary-card>
    </div>
    @endif

    {{-- Marge & Bénéfice --}}
    @if($activeTab === 'margin')
    <div class="space-y-4">
        <div class="grid grid-cols-3 gap-4">
            <x-mary-stat title="CA total" :value="number_format($marginStats['total_ca'], 0, ',', ' ') . ' ' . config('boutique.devise_symbole')" icon="o-banknotes" color="text-primary" />
            <x-mary-stat title="Coût total" :value="number_format($marginStats['total_cost'], 0, ',', ' ') . ' ' . config('boutique.devise_symbole')" icon="o-arrow-trending-down" color="text-error" />
            <x-mary-stat title="Bénéfice" :value="number_format($marginStats['total_profit'], 0, ',', ' ') . ' ' . config('boutique.devise_symbole')" icon="o-arrow-trending-up" color="text-success" />
        </div>

        <x-mary-card title="Par marque">
            <x-mary-table :headers="[
                ['key'=>'brand',  'label'=>'Marque'],
                ['key'=>'qty',    'label'=>'Qté'],
                ['key'=>'ca',     'label'=>'CA'],
                ['key'=>'cost',   'label'=>'Coût'],
                ['key'=>'profit', 'label'=>'Bénéfice'],
                ['key'=>'margin', 'label'=>'Marge %'],
            ]" :rows="$marginStats['rows']->toArray()">

                @scope('cell_ca', $row)
                    {{ number_format($row['ca'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                @endscope

                @scope('cell_cost', $row)
                    <span class="text-error">{{ number_format($row['cost'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
                @endscope

                @scope('cell_profit', $row)
                    <span class="font-bold text-success">{{ number_format($row['profit'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
                @endscope

                @scope('cell_margin', $row)
                    @php $pct = $row['ca'] > 0 ? round(($row['profit'] / $row['ca']) * 100, 1) : 0; @endphp
                    <span class="badge badge-sm {{ $pct >= 20 ? 'badge-success' : ($pct >= 10 ? 'badge-warning' : 'badge-error') }}">
                        {{ $pct }}%
                    </span>
                @endscope

            </x-mary-table>
        </x-mary-card>
    </div>
    @endif

    {{-- Top Produits --}}
    @if($activeTab === 'products')
    <x-mary-card title="Top 20 produits vendus">
        <x-mary-table :headers="[
            ['key'=>'product', 'label'=>'Produit'],
            ['key'=>'qty',     'label'=>'Qté vendue'],
            ['key'=>'ca',      'label'=>'CA'],
            ['key'=>'profit',  'label'=>'Bénéfice'],
        ]" :rows="$topProducts">

            @scope('cell_ca', $row)
                {{ number_format($row['ca'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
            @endscope

            @scope('cell_profit', $row)
                <span class="text-success font-medium">{{ number_format($row['profit'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
            @endscope

        </x-mary-table>
    </x-mary-card>
    @endif

    {{-- Créances --}}
    @if($activeTab === 'creances')
    <div class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <x-mary-stat title="Créances clients" :value="number_format($creances['total_clients'], 0, ',', ' ') . ' ' . config('boutique.devise_symbole')" icon="o-user" color="text-warning" />
            <x-mary-stat title="Créances revendeurs" :value="number_format($creances['total_resellers'], 0, ',', ' ') . ' ' . config('boutique.devise_symbole')" icon="o-building-storefront" color="text-error" />
        </div>

        <x-mary-card title="Clients particuliers">
            @forelse($creances['clients'] as $c)
            <div class="flex justify-between items-center py-2 border-b border-base-200 last:border-0">
                <div>
                    <p class="font-medium text-sm">{{ $c->customer_name ?: 'Anonyme' }}</p>
                    @if($c->customer_phone)
                    <p class="text-xs text-gray-400">{{ $c->customer_phone }}</p>
                    @endif
                    <p class="text-xs text-gray-400">{{ $c->count }} vente(s)</p>
                </div>
                <span class="font-bold text-error">
                    {{ number_format($c->solde, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                </span>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Aucune créance client.</p>
            @endforelse
        </x-mary-card>

        <x-mary-card title="Revendeurs">
            @forelse($creances['resellers'] as $r)
            <div class="flex justify-between items-center py-2 border-b border-base-200 last:border-0">
                <div>
                    <p class="font-medium text-sm">{{ $r->name }}</p>
                    <p class="text-xs text-gray-400">{{ $r->count }} vente(s)</p>
                </div>
                <span class="font-bold text-error">
                    {{ number_format($r->solde, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                </span>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Aucune créance revendeur.</p>
            @endforelse
        </x-mary-card>
    </div>
    @endif

    {{-- Mouvements stock --}}
    @if($activeTab === 'stock')
    <x-mary-card title="Mouvements de stock par type">
        <x-mary-table :headers="[
            ['key'=>'type',  'label'=>'Type'],
            ['key'=>'count', 'label'=>'Nombre'],
        ]" :rows="$stockStats['rows']->toArray()">

            @scope('cell_type', $row)
                @php $type = \App\Enums\StockMovementType::from($row['type']); @endphp
                <x-mary-badge
                    value="{{ $type->label() }}"
                    class="badge-sm {{ $type->isPositive() ? 'badge-success' : 'badge-error' }}"
                />
            @endscope

        </x-mary-table>
        <div class="mt-3 text-right font-bold">
            Total: {{ $stockStats['total'] }} mouvement(s)
        </div>
    </x-mary-card>
    @endif
</div>
