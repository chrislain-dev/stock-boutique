<div x-data x-init="initChart(@js($chartData))">

    <x-mary-header title="Dashboard" subtitle="{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}" icon="o-home" />

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <x-mary-card class="bg-primary/10 border-0">
            <p class="text-xs text-gray-400 uppercase tracking-wide">CA Aujourd'hui</p>
            <p class="text-2xl font-bold text-primary mt-1">
                {{ number_format($kpis['caJour'], 0, ',', ' ') }}
                <span class="text-sm font-normal">{{ config('boutique.devise_symbole') }}</span>
            </p>
        </x-mary-card>

        <x-mary-card class="bg-success/10 border-0">
            <p class="text-xs text-gray-400 uppercase tracking-wide">CA Ce mois</p>
            <p class="text-2xl font-bold text-success mt-1">
                {{ number_format($kpis['caMois'], 0, ',', ' ') }}
                <span class="text-sm font-normal">{{ config('boutique.devise_symbole') }}</span>
            </p>
        </x-mary-card>

        <x-mary-card class="bg-info/10 border-0">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Ventes aujourd'hui</p>
            <p class="text-2xl font-bold text-info mt-1">{{ $kpis['ventesJour'] }}</p>
        </x-mary-card>

        <x-mary-card class="bg-warning/10 border-0">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Stock disponible</p>
            <p class="text-2xl font-bold text-warning mt-1">{{ $kpis['stockDispo'] }}</p>
        </x-mary-card>

        <x-mary-card class="{{ $kpis['creances'] > 0 ? 'bg-error/10' : 'bg-base-200' }} border-0">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Créances totales</p>
            <p class="text-2xl font-bold {{ $kpis['creances'] > 0 ? 'text-error' : 'text-gray-400' }} mt-1">
                {{ number_format($kpis['creances'], 0, ',', ' ') }}
                <span class="text-sm font-normal">{{ config('boutique.devise_symbole') }}</span>
            </p>
        </x-mary-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Graphique CA 7 jours --}}
        <div class="lg:col-span-2">
            <x-mary-card title="CA des 7 derniers jours">
                <canvas id="caChart" height="120"></canvas>
            </x-mary-card>
        </div>

        {{-- Top produits --}}
        <x-mary-card title="Top 5 produits (ce mois)">
            @forelse($topProducts as $i => $p)
            <div class="flex items-center gap-3 py-2 {{ !$loop->last ? 'border-b border-base-200' : '' }}">
                <span class="w-6 h-6 rounded-full bg-primary/20 text-primary text-xs flex items-center justify-center font-bold">
                    {{ $i + 1 }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ $p['product'] }}</p>
                    <p class="text-xs text-gray-400">{{ $p['qty'] }} vendu(s)</p>
                </div>
                <span class="text-sm font-bold text-success whitespace-nowrap">
                    {{ number_format($p['ca'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                </span>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Aucune vente ce mois.</p>
            @endforelse
        </x-mary-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Dernières ventes --}}
        <div class="lg:col-span-2">
            <x-mary-card title="Dernières ventes">
                <div class="overflow-x-auto">
                    <table class="table table-sm w-full">
                        <thead>
                            <tr class="text-xs text-gray-400">
                                <th>Réf</th>
                                <th>Client</th>
                                <th>Vendeur</th>
                                <th class="text-right">Montant</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lastSales as $sale)
                            <tr class="hover cursor-pointer" onclick="window.location='{{ route('sales.show', $sale) }}'">
                                <td class="font-mono text-xs">{{ $sale->reference }}</td>
                                <td class="text-sm">
                                    @if($sale->customer_type === 'reseller')
                                        {{ $sale->reseller?->name ?? '—' }}
                                    @else
                                        {{ $sale->customer_name ?: 'Anonyme' }}
                                    @endif
                                </td>
                                <td class="text-xs text-gray-400">{{ $sale->createdBy?->name ?? '—' }}</td>
                                <td class="text-right font-medium text-sm">
                                    {{ number_format($sale->total_amount, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                                </td>
                                <td>
                                    @php
                                        $status = $sale->payment_status instanceof \App\Enums\PaymentStatus
                                            ? $sale->payment_status
                                            : \App\Enums\PaymentStatus::from($sale->payment_status);
                                    @endphp
                                    <x-mary-badge
                                        value="{{ $status->label() }}"
                                        class="badge-xs badge-{{ $status->color() }}"
                                    />
                                </td>
                                <td class="text-xs text-gray-400">{{ $sale->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-gray-400 py-4">Aucune vente.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-right">
                    <a href="{{ route('sales.index') }}" class="text-sm text-primary hover:underline">
                        Voir toutes les ventes →
                    </a>
                </div>
            </x-mary-card>
        </div>

        {{-- Alertes stock --}}
        <x-mary-card title="Alertes stock">
            @if($alerts['enReparation'] > 0)
            <div class="alert alert-warning py-2 mb-3">
                <x-mary-icon name="o-wrench-screwdriver" class="w-4 h-4" />
                <span class="text-sm">{{ $alerts['enReparation'] }} produit(s) en réparation</span>
            </div>
            @endif

            @if($alerts['defectueux'] > 0)
            <div class="alert alert-error py-2 mb-3">
                <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4" />
                <span class="text-sm">{{ $alerts['defectueux'] }} produit(s) défectueux</span>
            </div>
            @endif

            @if(count($alerts['stockBas']) > 0)
            <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Stock bas (< 2)</p>
            @foreach($alerts['stockBas'] as $item)
            <div class="flex justify-between items-center py-1 border-b border-base-200 last:border-0">
                <span class="text-sm truncate">{{ $item->product }}</span>
                <span class="badge badge-sm {{ $item->qty == 0 ? 'badge-error' : 'badge-warning' }}">
                    {{ $item->qty }}
                </span>
            </div>
            @endforeach
            @else
            <div class="alert alert-success py-2">
                <x-mary-icon name="o-check-circle" class="w-4 h-4" />
                <span class="text-sm">Stock OK</span>
            </div>
            @endif

            <div class="mt-3 text-right">
                <a href="{{ route('products.index') }}" class="text-sm text-primary hover:underline">
                    Gérer le stock →
                </a>
            </div>
        </x-mary-card>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function initChart(data) {
    const ctx = document.getElementById('caChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'CA ({{ config('boutique.devise_symbole') }})',
                data: data.values,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#6366f1',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.parsed.y.toLocaleString('fr') + ' {{ config('boutique.devise_symbole') }}'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: val => val.toLocaleString('fr')
                    }
                }
            }
        }
    });
}
</script>
@endpush
