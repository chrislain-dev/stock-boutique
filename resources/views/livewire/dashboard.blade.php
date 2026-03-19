<div>
    {{-- Animations CSS --}}
    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes countUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes shimmer {
            0%   { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        .anim-1 { animation: slideUp 0.4s cubic-bezier(.16,1,.3,1) both; }
        .anim-2 { animation: slideUp 0.4s cubic-bezier(.16,1,.3,1) 0.05s both; }
        .anim-3 { animation: slideUp 0.4s cubic-bezier(.16,1,.3,1) 0.10s both; }
        .anim-4 { animation: slideUp 0.4s cubic-bezier(.16,1,.3,1) 0.15s both; }
        .anim-5 { animation: slideUp 0.4s cubic-bezier(.16,1,.3,1) 0.20s both; }
        .anim-6 { animation: slideUp 0.4s cubic-bezier(.16,1,.3,1) 0.25s both; }
        .anim-7 { animation: slideUp 0.4s cubic-bezier(.16,1,.3,1) 0.30s both; }

        .kpi-card {
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 60%, rgba(255,255,255,0.8));
            pointer-events: none;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,.08) !important;
        }

        .kpi-value {
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
        }

        .row-link {
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .row-link:hover { background: #fafafa; }
        .row-link:hover .ref-badge { color: var(--boutique-primary, #18181b); }

        .section-header {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-header::before {
            content: '';
            display: block;
            width: 3px;
            height: 16px;
            background: var(--boutique-primary, #18181b);
            border-radius: 99px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .status-pill::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .chart-container {
            position: relative;
        }
        .chart-container::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: #f4f4f5;
        }
    </style>

    {{-- Page header --}}
    <div class="anim-1 flex items-end justify-between mb-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400 mb-1">
                {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
            </p>
            <h1 class="text-3xl font-semibold tracking-tight text-zinc-900">Tableau de bord</h1>
        </div>
        <div class="hidden md:flex items-center gap-2 text-xs text-zinc-400 bg-white border border-zinc-100 rounded-lg px-3 py-2 shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Données en temps réel
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        {{-- CA Jour --}}
        <div class="anim-1 kpi-card bg-white rounded-xl border border-zinc-100 p-5 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-zinc-400">CA Aujourd'hui</p>
                <div class="w-7 h-7 rounded-lg bg-zinc-50 flex items-center justify-center shrink-0">
                    <x-mary-icon name="o-sun" class="w-3.5 h-3.5 text-zinc-400" />
                </div>
            </div>
            <p class="kpi-value text-2xl font-semibold text-zinc-900 tracking-tight leading-none">
                {{ number_format($kpis['caJour'], 0, ',', ' ') }}
            </p>
            <p class="text-xs text-zinc-400 mt-1">{{ config('boutique.devise_symbole') }}</p>
        </div>

        {{-- CA Mois --}}
        <div class="anim-2 kpi-card bg-white rounded-xl border border-zinc-100 p-5 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-zinc-400">Ce mois</p>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                    <x-mary-icon name="o-arrow-trending-up" class="w-3.5 h-3.5 text-emerald-500" />
                </div>
            </div>
            <p class="kpi-value text-2xl font-semibold text-zinc-900 tracking-tight leading-none">
                {{ number_format($kpis['caMois'], 0, ',', ' ') }}
            </p>
            <p class="text-xs text-zinc-400 mt-1">{{ config('boutique.devise_symbole') }}</p>
        </div>

        {{-- Ventes jour --}}
        <div class="anim-3 kpi-card bg-white rounded-xl border border-zinc-100 p-5 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-zinc-400">Ventes</p>
                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <x-mary-icon name="o-shopping-bag" class="w-3.5 h-3.5 text-blue-500" />
                </div>
            </div>
            <p class="kpi-value text-2xl font-semibold text-zinc-900 tracking-tight leading-none">
                {{ $kpis['ventesJour'] }}
            </p>
            <p class="text-xs text-zinc-400 mt-1">aujourd'hui</p>
        </div>

        {{-- Stock --}}
        <div class="anim-4 kpi-card bg-white rounded-xl border border-zinc-100 p-5 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-zinc-400">Stock</p>
                <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                    <x-mary-icon name="o-archive-box" class="w-3.5 h-3.5 text-amber-500" />
                </div>
            </div>
            <p class="kpi-value text-2xl font-semibold text-zinc-900 tracking-tight leading-none">
                {{ $kpis['stockDispo'] }}
            </p>
            <p class="text-xs text-zinc-400 mt-1">disponibles</p>
        </div>

        {{-- Créances --}}
        <div class="anim-5 kpi-card bg-white rounded-xl border border-zinc-100 p-5 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-zinc-400">Créances</p>
                <div class="w-7 h-7 rounded-lg {{ $kpis['creances'] > 0 ? 'bg-red-50' : 'bg-zinc-50' }} flex items-center justify-center shrink-0">
                    <x-mary-icon name="o-clock" class="w-3.5 h-3.5 {{ $kpis['creances'] > 0 ? 'text-red-400' : 'text-zinc-300' }}" />
                </div>
            </div>
            <p class="kpi-value text-2xl font-semibold {{ $kpis['creances'] > 0 ? 'text-red-500' : 'text-zinc-900' }} tracking-tight leading-none">
                {{ number_format($kpis['creances'], 0, ',', ' ') }}
            </p>
            <p class="text-xs text-zinc-400 mt-1">{{ config('boutique.devise_symbole') }} en attente</p>
        </div>
    </div>

    {{-- Ligne 2 --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

        {{-- Graphique --}}
        <div class="anim-6 lg:col-span-2 bg-white rounded-xl border border-zinc-100 shadow-sm">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-zinc-50">
                <div class="section-header">
                    <h3 class="text-sm font-semibold text-zinc-900">Chiffre d'affaires</h3>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-zinc-400">7 derniers jours</span>
                    <span class="text-xs font-semibold text-zinc-900 bg-zinc-50 border border-zinc-100 px-2.5 py-1 rounded-lg">
                        {{ number_format(array_sum($chartData['values']), 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                    </span>
                </div>
            </div>
            <div class="p-6 chart-container">
                <canvas id="caChart" height="110"></canvas>
            </div>
        </div>

        {{-- Top produits --}}
        <div class="anim-7 bg-white rounded-xl border border-zinc-100 shadow-sm">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-zinc-50">
                <div class="section-header">
                    <h3 class="text-sm font-semibold text-zinc-900">Top produits</h3>
                </div>
                <span class="text-xs text-zinc-400">ce mois</span>
            </div>
            <div class="p-4">
                @forelse($topProducts as $i => $p)
                <div class="flex items-center gap-3 px-2 py-2.5 rounded-lg hover:bg-zinc-50 transition-colors {{ !$loop->last ? 'mb-1' : '' }}">
                    <span class="w-6 h-6 rounded-md bg-zinc-100 text-zinc-500 text-xs flex items-center justify-center font-bold shrink-0">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-zinc-800 truncate leading-tight">{{ $p['product'] }}</p>
                        <p class="text-xs text-zinc-400 mt-0.5">{{ $p['qty'] }} vendu(s)</p>
                    </div>
                    <span class="text-xs font-semibold text-zinc-700 whitespace-nowrap">
                        {{ number_format($p['ca'], 0, ',', ' ') }}
                        <span class="font-normal text-zinc-400">{{ config('boutique.devise_symbole') }}</span>
                    </span>
                </div>
                @empty
                <div class="py-10 text-center">
                    <x-mary-icon name="o-chart-bar" class="w-8 h-8 text-zinc-200 mx-auto mb-2" />
                    <p class="text-sm text-zinc-400">Aucune vente ce mois.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Ligne 3 --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Dernières ventes --}}
        <div class="anim-6 lg:col-span-2 bg-white rounded-xl border border-zinc-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-zinc-50">
                <div class="section-header">
                    <h3 class="text-sm font-semibold text-zinc-900">Dernières ventes</h3>
                </div>
                <a href="{{ route('sales.index') }}"
                   class="text-xs font-medium text-zinc-400 hover:text-zinc-900 transition-colors flex items-center gap-1">
                    Voir tout
                    <x-mary-icon name="o-arrow-right" class="w-3 h-3" />
                </a>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-zinc-50/60">
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">Réf</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lastSales as $sale)
                    <tr class="row-link border-t border-zinc-50" onclick="window.location='{{ route('sales.show', $sale) }}'">
                        <td class="px-6 py-3.5">
                            <span class="ref-badge font-mono text-xs font-medium text-zinc-500 transition-colors">{{ $sale->reference }}</span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="text-sm text-zinc-700">
                                @if($sale->customer_type === 'reseller')
                                    {{ $sale->reseller?->name ?? '—' }}
                                @else
                                    {{ $sale->customer_name ?: 'Anonyme' }}
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <span class="text-sm font-semibold text-zinc-900 font-mono">
                                {{ number_format($sale->total_amount, 0, ',', ' ') }}
                                <span class="text-zinc-400 font-normal text-xs">{{ config('boutique.devise_symbole') }}</span>
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            @php
                                $status = $sale->payment_status instanceof \App\Enums\PaymentStatus
                                    ? $sale->payment_status
                                    : \App\Enums\PaymentStatus::from($sale->payment_status);
                            @endphp
                            <span class="status-pill
                                {{ $status === \App\Enums\PaymentStatus::PAID    ? 'bg-emerald-50 text-emerald-600' :
                                  ($status === \App\Enums\PaymentStatus::PARTIAL ? 'bg-amber-50 text-amber-600'   : 'bg-red-50 text-red-500') }}">
                                {{ $status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <span class="text-xs text-zinc-400">{{ $sale->created_at->diffForHumans() }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-zinc-400">Aucune vente pour l'instant.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Alertes --}}
        <div class="anim-7 bg-white rounded-xl border border-zinc-100 shadow-sm">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-zinc-50">
                <div class="section-header">
                    <h3 class="text-sm font-semibold text-zinc-900">Alertes stock</h3>
                </div>
                @if($alerts['enReparation'] + $alerts['defectueux'] + count($alerts['stockBas']) > 0)
                <span class="text-xs font-semibold bg-red-50 text-red-500 px-2 py-0.5 rounded-full">
                    {{ $alerts['enReparation'] + $alerts['defectueux'] + count($alerts['stockBas']) }}
                </span>
                @endif
            </div>
            <div class="p-4 space-y-2">
                @if($alerts['enReparation'] > 0)
                <div class="flex items-center gap-3 p-3 bg-amber-50 border border-amber-100 rounded-xl">
                    <div class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                        <x-mary-icon name="o-wrench-screwdriver" class="w-3.5 h-3.5 text-amber-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-amber-800">{{ $alerts['enReparation'] }} en réparation</p>
                        <p class="text-xs text-amber-500">À suivre</p>
                    </div>
                </div>
                @endif

                @if($alerts['defectueux'] > 0)
                <div class="flex items-center gap-3 p-3 bg-red-50 border border-red-100 rounded-xl">
                    <div class="w-7 h-7 bg-red-100 rounded-lg flex items-center justify-center shrink-0">
                        <x-mary-icon name="o-exclamation-triangle" class="w-3.5 h-3.5 text-red-500" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-red-700">{{ $alerts['defectueux'] }} défectueux</p>
                        <p class="text-xs text-red-400">Action requise</p>
                    </div>
                </div>
                @endif

                @if(count($alerts['stockBas']) > 0)
                <div class="pt-2 pb-1 px-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400 mb-2">Stock bas</p>
                    @foreach($alerts['stockBas'] as $item)
                    <div class="flex items-center justify-between py-2 border-b border-zinc-50 last:border-0">
                        <span class="text-sm text-zinc-700 truncate mr-3">{{ $item->product }}</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-lg shrink-0
                            {{ $item->qty == 0 ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600' }}">
                            {{ $item->qty }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($alerts['enReparation'] == 0 && $alerts['defectueux'] == 0 && count($alerts['stockBas']) == 0)
                <div class="flex items-center gap-3 p-3 bg-emerald-50 border border-emerald-100 rounded-xl">
                    <div class="w-7 h-7 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                        <x-mary-icon name="o-check-circle" class="w-3.5 h-3.5 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-emerald-800">Tout va bien</p>
                        <p class="text-xs text-emerald-500">Stock en ordre</p>
                    </div>
                </div>
                @endif
            </div>
            <div class="px-6 pb-4">
                <a href="{{ route('products.index') }}"
                   class="flex items-center justify-center gap-1.5 w-full py-2 text-xs font-medium text-zinc-500 hover:text-zinc-900 bg-zinc-50 hover:bg-zinc-100 rounded-lg transition-colors border border-zinc-100">
                    <x-mary-icon name="o-archive-box" class="w-3.5 h-3.5" />
                    Gérer le stock
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@script
<script>
    let _caChart = null;

    function initDashboardChart() {
        if (typeof Chart === 'undefined') return;

        const el = document.getElementById('caChart');
        if (!el) return;

        // Détruire l'instance existante
        if (_caChart) { _caChart.destroy(); _caChart = null; }

        const data    = @js($chartData);
        const primary = getComputedStyle(document.documentElement)
            .getPropertyValue('--boutique-primary').trim() || '#18181b';

        _caChart = new Chart(el, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    borderColor: primary,
                    backgroundColor: (context) => {
                        const chart = context.chart;
                        const { ctx: c, chartArea } = chart;
                        if (!chartArea) return 'transparent';
                        const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, primary + '18');
                        gradient.addColorStop(1, primary + '00');
                        return gradient;
                    },
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: primary,
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: primary,
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#18181b',
                        titleColor: '#71717a',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 10,
                        borderColor: '#27272a',
                        borderWidth: 1,
                        callbacks: {
                            title: items => items[0].label,
                            label: ctx => '  ' + ctx.parsed.y.toLocaleString('fr') + ' {{ config('boutique.devise_symbole') }}'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: '#a1a1aa', font: { size: 11, family: 'Geist Mono, monospace' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f4f4f5' },
                        border: { display: false },
                        ticks: {
                            color: '#a1a1aa',
                            font: { size: 11 },
                            callback: val => val === 0 ? '0' : (val / 1000).toFixed(0) + 'k'
                        }
                    }
                },
                animation: { duration: 800, easing: 'easeOutQuart' }
            }
        });
    }

    // Premier chargement et après chaque navigation wire:navigate
    setTimeout(initDashboardChart, 80);
    Livewire.hook('morph.updated', () => setTimeout(initDashboardChart, 80));
</script>
@endscript
@endpush
