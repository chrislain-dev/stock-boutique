<div>
    {{-- Header --}}
    <x-mary-header
        title="Dashboard"
        subtitle="Bonjour {{ auth()->user()->name }} 👋"
    >
        @if(auth()->user()->isAdmin())
        <x-slot:actions>
            <x-mary-select
                wire:model.live="period"
                :options="[
                    ['id' => 'day',      'name' => 'Aujourd\'hui'],
                    ['id' => 'week',     'name' => 'Cette semaine'],
                    ['id' => 'month',    'name' => 'Ce mois'],
                    ['id' => 'quarter',  'name' => 'Ce trimestre'],
                    ['id' => 'semester', 'name' => 'Ce semestre'],
                    ['id' => 'year',     'name' => 'Cette année'],
                ]"
                class="select-sm w-40"
            />
        </x-slot:actions>
        @endif
    </x-mary-header>

    {{-- ── ADMIN ─────────────────────────────────────────── --}}
    @if(auth()->user()->isAdmin())

        {{-- Stats cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            <x-mary-stat
                title="Chiffre d'affaires"
                :value="number_format($stats['revenue'] ?? 0, 0, ',', ' ') . ' ' . config('boutique.devise')"
                icon="o-banknotes"
                color="text-success"
            />

            <x-mary-stat
                title="Bénéfice"
                :value="number_format($stats['profit'] ?? 0, 0, ',', ' ') . ' ' . config('boutique.devise')"
                icon="o-arrow-trending-up"
                color="text-primary"
            />

            <x-mary-stat
                title="Ventes"
                :value="$stats['sales_count'] ?? 0"
                icon="o-shopping-bag"
                color="text-info"
            />

            <x-mary-stat
                title="Unités vendues"
                :value="$stats['units_sold'] ?? 0"
                icon="o-cube"
                color="text-secondary"
            />

        </div>

        {{-- Graphique + Stock bas --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

            {{-- Graphique ventes --}}
            <div class="lg:col-span-2">
                <x-mary-card title="Évolution des ventes">
                    <x-slot:menu>
                        <x-mary-select
                            wire:model.live="chartPeriod"
                            :options="[
                                ['id' => 'week',  'name' => 'Semaine'],
                                ['id' => 'month', 'name' => 'Mois'],
                                ['id' => 'year',  'name' => 'Année'],
                            ]"
                            class="select-xs w-28"
                        />
                    </x-slot:menu>

                    @if(!empty($chartData))
                    <div class="h-48 flex items-end gap-2 pt-4">
                        @php $max = max(array_column($chartData, 'amount')) ?: 1; @endphp
                        @foreach($chartData as $point)
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <span class="text-xs text-gray-400">
                                {{ number_format($point['amount'] / 1000, 0) }}k
                            </span>
                            <div
                                class="w-full rounded-t-md bg-primary transition-all duration-500"
                                style="height: {{ max(4, ($point['amount'] / $max) * 160) }}px; opacity: 0.85"
                            ></div>
                            <span class="text-xs text-gray-500 truncate w-full text-center">
                                {{ $point['label'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <x-mary-alert title="Aucune donnée" icon="o-information-circle" class="alert-info" />
                    @endif
                </x-mary-card>
            </div>

            {{-- Stock bas --}}
            <x-mary-card title="Stock bas" icon="o-exclamation-triangle">
                @forelse($lowStockProducts as $product)
                <div class="flex items-center justify-between py-2 border-b border-base-200 last:border-0">
                    <div>
                        <p class="text-sm font-medium">{{ $product->full_name }}</p>
                        <p class="text-xs text-gray-400">Min: {{ $product->stock_minimum }}</p>
                    </div>
                    <x-mary-badge
                        :value="$product->quantity_stock"
                        :class="$product->quantity_stock == 0 ? 'badge-error' : 'badge-warning'"
                    />
                </div>
                @empty
                <x-mary-alert title="Aucun stock bas" icon="o-check-circle" class="alert-success" />
                @endforelse
            </x-mary-card>

        </div>

    {{-- ── VENDEUR ──────────────────────────────────────── --}}
    @else

        {{-- Stats vendeur --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <x-mary-stat
                title="Mes ventes du jour"
                :value="$stats['sales_count'] ?? 0"
                icon="o-shopping-bag"
                color="text-primary"
            />
            <x-mary-stat
                title="Unités vendues"
                :value="$stats['units_sold'] ?? 0"
                icon="o-cube"
                color="text-info"
            />
        </div>

        {{-- Stock bas + Créances --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

            <x-mary-card title="Alertes stock bas" icon="o-exclamation-triangle">
                @forelse($lowStockProducts as $product)
                <div class="flex items-center justify-between py-2 border-b border-base-200 last:border-0">
                    <p class="text-sm font-medium">{{ $product->full_name }}</p>
                    <x-mary-badge
                        :value="$product->quantity_stock"
                        :class="$product->quantity_stock == 0 ? 'badge-error' : 'badge-warning'"
                    />
                </div>
                @empty
                <x-mary-alert title="Aucun stock bas" class="alert-success" />
                @endforelse
            </x-mary-card>

            <x-mary-card title="Revendeurs avec créances" icon="o-users">
                @forelse($resellersWithDebt as $reseller)
                <div class="flex items-center justify-between py-2 border-b border-base-200 last:border-0">
                    <div>
                        <p class="text-sm font-medium">{{ $reseller->name }}</p>
                        <p class="text-xs text-gray-400">{{ $reseller->phone }}</p>
                    </div>
                    <x-mary-badge
                        value="{{ number_format($reseller->solde_du, 0, ',', ' ') }} {{ config('boutique.devise') }}"
                        class="badge-error"
                    />
                </div>
                @empty
                <x-mary-alert title="Aucune créance" class="alert-success" />
                @endforelse
            </x-mary-card>

        </div>

    @endif

    {{-- Dernières ventes (commun) --}}
    <x-mary-card title="Dernières ventes" icon="o-clock">
        <x-mary-table
            :headers="[
                ['key' => 'reference',      'label' => 'Référence'],
                ['key' => 'customer',       'label' => 'Client'],
                ['key' => 'total_amount',   'label' => 'Montant'],
                ['key' => 'payment_status', 'label' => 'Paiement'],
                ['key' => 'created_at',     'label' => 'Date'],
            ]"
            :rows="$recentSales->map(fn($s) => [
                'reference'      => $s->reference,
                'customer'       => $s->reseller?->name ?? $s->customer_name ?? 'Client anonyme',
                'total_amount'   => number_format($s->total_amount, 0, ',', ' ') . ' ' . config('boutique.devise'),
                'payment_status' => $s->payment_status->label(),
                'created_at'     => $s->created_at->format('d/m/Y H:i'),
            ])"
        />
    </x-mary-card>

</div>
