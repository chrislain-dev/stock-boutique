<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Rapports</h1>
            <p class="text-sm text-gray-400 mt-0.5">Analyse des performances commerciales</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="exportPdf"
                    class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-gray-200 bg-white text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <x-heroicon-o-document-text class="w-4 h-4"/>
                Export PDF
            </button>
            <button wire:click="exportExcel"
                    class="inline-flex items-center gap-2 h-9 px-4 rounded-xl border border-green-600 bg-green-600 text-white text-sm hover:bg-green-700 transition-colors">
                <x-heroicon-o-table-cells class="w-4 h-4"/>
                Export Excel
            </button>
        </div>
    </div>

    {{-- Filtre période --}}
    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-2xl px-4 py-3 mb-5 flex-wrap">
        <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mr-1">Période</span>
        <div class="flex gap-1.5 flex-wrap">
            @foreach($periods as $p)
            <button wire:click="$set('period', '{{ $p['id'] }}')"
                    class="h-8 px-3.5 rounded-lg text-sm transition-all border
                           {{ $period === $p['id']
                              ? 'bg-gray-900 text-white border-gray-900'
                              : 'bg-transparent text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                {{ $p['name'] }}
            </button>
            @endforeach
        </div>

        @if($period === 'custom')
        <div class="flex items-center gap-2 ml-auto">
            <input wire:model.live="dateFrom" type="date"
                   class="h-8 px-3 text-sm border border-gray-200 rounded-lg outline-none focus:border-gray-400"/>
            <span class="text-gray-400 text-sm">→</span>
            <input wire:model.live="dateTo" type="date"
                   class="h-8 px-3 text-sm border border-gray-200 rounded-lg outline-none focus:border-gray-400"/>
        </div>
        @else
        <div class="flex items-center gap-2 ml-auto">
            <span class="bg-gray-100 border border-gray-200 rounded-lg px-2.5 py-1 text-xs text-gray-600 font-medium">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
            </span>
            <span class="text-gray-400 text-xs">→</span>
            <span class="bg-gray-100 border border-gray-200 rounded-lg px-2.5 py-1 text-xs text-gray-600 font-medium">
                {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            </span>
        </div>
        @endif
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-4 gap-3 mb-5">

        {{-- CA --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                    <x-heroicon-o-banknotes class="w-4 h-4 text-blue-600"/>
                </div>
                <span class="text-[11px] font-medium bg-blue-50 text-blue-700 px-2 py-0.5 rounded-md">CA</span>
            </div>
            <p class="text-2xl font-semibold tracking-tight">{{ number_format($caStats['total_ca'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Chiffre d'affaires · {{ config('boutique.devise_symbole') }}</p>
            <p class="text-[11px] text-gray-300 mt-2">{{ $caStats['total_count'] }} ventes réalisées</p>
        </div>

        {{-- Bénéfice — admin only --}}
        @if($isAdmin)
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center">
                    <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-green-700"/>
                </div>
                @php $margin = $marginStats['total_ca'] > 0 ? round(($marginStats['total_profit'] / $marginStats['total_ca']) * 100, 1) : 0; @endphp
                <span class="text-[11px] font-medium bg-green-50 text-green-800 px-2 py-0.5 rounded-md">{{ $margin }}%</span>
            </div>
            <p class="text-2xl font-semibold tracking-tight">{{ number_format($marginStats['total_profit'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Bénéfice net · {{ config('boutique.devise_symbole') }}</p>
            <p class="text-[11px] text-gray-300 mt-2">Coût : {{ number_format($marginStats['total_cost'], 0, ',', ' ') }}</p>
        </div>
        @else
        <div class="bg-white border border-gray-200 rounded-2xl p-5 opacity-40 pointer-events-none select-none">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center">
                    <x-heroicon-o-lock-closed class="w-4 h-4 text-gray-400"/>
                </div>
                <span class="text-[11px] font-medium bg-gray-100 text-gray-400 px-2 py-0.5 rounded-md">Admin</span>
            </div>
            <p class="text-2xl font-semibold tracking-tight text-gray-300">—</p>
            <p class="text-xs text-gray-300 mt-0.5">Accès restreint</p>
        </div>
        @endif

        {{-- Ventes --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                    <x-heroicon-o-shopping-bag class="w-4 h-4 text-amber-700"/>
                </div>
                @php $avg = $caStats['total_count'] > 0 ? round($caStats['total_ca'] / $caStats['total_count']) : 0; @endphp
                <span class="text-[11px] font-medium bg-amber-50 text-amber-800 px-2 py-0.5 rounded-md">moy.</span>
            </div>
            <p class="text-2xl font-semibold tracking-tight">{{ $caStats['total_count'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Ventes réalisées</p>
            <p class="text-[11px] text-gray-300 mt-2">{{ number_format($avg, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }} / vente</p>
        </div>

        {{-- Créances --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500"/>
                </div>
                <span class="text-[11px] font-medium bg-red-50 text-red-700 px-2 py-0.5 rounded-md">Attention</span>
            </div>
            <p class="text-2xl font-semibold tracking-tight">{{ number_format($creances['total_clients'] + $creances['total_resellers'], 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Créances totales · {{ config('boutique.devise_symbole') }}</p>
            <p class="text-[11px] text-gray-300 mt-2">{{ $creances['clients']->count() + $creances['resellers']->count() }} débiteurs</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-white border border-gray-200 rounded-xl p-1 mb-5 overflow-x-auto">
        @foreach($tabs as $tab)
        <button wire:click="$set('activeTab', '{{ $tab['id'] }}')"
                class="flex items-center gap-1.5 h-9 px-4 rounded-lg text-sm transition-all whitespace-nowrap
                       {{ $activeTab === $tab['id'] ? 'bg-gray-900 text-white font-medium' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' }}">
            {{ $tab['name'] }}
        </button>
        @endforeach
    </div>

    {{-- ─── Panel CA ─────────────────────────────────────────── --}}
    @if($activeTab === 'ca')
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <p class="text-sm font-medium mb-1">Évolution du CA</p>
                <p class="text-xs text-gray-400 mb-4">Chiffre d'affaires quotidien</p>
                <div style="position:relative;height:200px;">
                    <canvas id="caChart"></canvas>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <p class="text-sm font-medium mb-1">CA vs Encaissé</p>
                <p class="text-xs text-gray-400 mb-4">Comparaison par jour</p>
                <div style="position:relative;height:200px;">
                    <canvas id="paidChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                <p class="text-sm font-medium">Détail par jour</p>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Date</th>
                        <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Ventes</th>
                        <th class="text-end text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">CA Total</th>
                        <th class="text-end text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Encaissé</th>
                        <th class="text-end text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Reste dû</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($caStats['rows'] as $row)
                    @php $unpaid = $row->total - $row->paid; @endphp
                    <tr class="border-b border-gray-100 last:border-none hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 text-sm">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-sm font-medium">{{ $row->count }}</td>
                        <td class="px-5 py-3 text-sm font-medium text-end">{{ number_format($row->total, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</td>
                        <td class="px-5 py-3 text-sm text-green-700 font-medium text-end">{{ number_format($row->paid, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</td>
                        <td class="px-5 py-3 text-sm {{ $unpaid > 0 ? 'text-red-600 font-medium' : 'text-green-700' }} text-end">
                            {{ number_format($unpaid, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">Aucune vente sur cette période</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ─── Panel Marge (admin only) ────────────────────────── --}}
    @if($activeTab === 'margin' && $isAdmin)
    <div class="space-y-4">
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <p class="text-sm font-medium mb-1">Bénéfice par marque</p>
            <p class="text-xs text-gray-400 mb-4">CA — Coût = Bénéfice</p>
            <div style="position:relative;height:200px;">
                <canvas id="marginChart"></canvas>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                <p class="text-sm font-medium">Détail par marque</p>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Marque</th>
                        <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Qté</th>
                        <th class="text-end text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">CA</th>
                        <th class="text-end text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Coût</th>
                        <th class="text-end text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Bénéfice</th>
                        <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Marge</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($marginStats['rows'] as $row)
                    @php $pct = $row->ca > 0 ? round(($row->profit / $row->ca) * 100, 1) : 0; @endphp
                    <tr class="border-b border-gray-100 last:border-none hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 text-sm font-medium">{{ $row->brand }}</td>
                        <td class="px-5 py-3 text-sm">{{ $row->qty }}</td>
                        <td class="px-5 py-3 text-sm font-medium text-end">{{ number_format($row->ca, 0, ',', ' ') }}  {{ config('boutique.devise_symbole') }}</td>
                        <td class="px-5 py-3 text-sm text-red-600 text-end">{{ number_format($row->cost, 0, ',', ' ') }}  {{ config('boutique.devise_symbole') }}</td>
                        <td class="px-5 py-3 text-sm text-green-700 font-medium text-end">{{ number_format($row->profit, 0, ',', ' ') }}  {{ config('boutique.devise_symbole') }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium
                                {{ $pct >= 20 ? 'bg-green-50 text-green-800' : ($pct >= 10 ? 'bg-amber-50 text-amber-800' : 'bg-red-50 text-red-700') }}">
                                {{ $pct }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">Aucune donnée sur cette période</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ─── Panel Top Produits ───────────────────────────────── --}}
    @if($activeTab === 'products')
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <p class="text-sm font-medium mb-4">Top 5 par quantité</p>
                <div style="position:relative;height:200px;">
                    <canvas id="topQtyChart"></canvas>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <p class="text-sm font-medium mb-4">Top 5 par CA</p>
                <div style="position:relative;height:200px;">
                    <canvas id="topCaChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                <p class="text-sm font-medium">Top 20 produits</p>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">#</th>
                        <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Produit</th>
                        <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Qté</th>
                        <th class="text-end text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">CA</th>
                        @if($isAdmin)
                        <th class="text-end text-[11px] font-medium text-gray-400 uppercase tracking-wider px-5 py-2.5">Bénéfice</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $i => $row)
                    <tr class="border-b border-gray-100 last:border-none hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 text-xs text-gray-300">{{ $i + 1 }}</td>
                        <td class="px-5 py-3 text-sm font-medium">{{ $row->product }}</td>
                        <td class="px-5 py-3 text-sm font-medium">{{ $row->qty }}</td>
                        <td class="px-5 py-3 text-sm text-end">{{ number_format($row->ca, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</td>
                        @if($isAdmin)
                        <td class="px-5 py-3 text-sm text-end text-green-700 font-medium">{{ number_format($row->profit, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="{{ $isAdmin ? 5 : 4 }}" class="px-5 py-10 text-center text-sm text-gray-400">Aucun produit vendu sur cette période</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ─── Panel Créances ───────────────────────────────────── --}}
    @if($activeTab === 'creances')
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium">Clients particuliers</p>
                    <p class="text-xs text-gray-400 mt-0.5">Total : {{ number_format($creances['total_clients'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</p>
                </div>
                <span class="text-[11px] bg-amber-50 text-amber-800 px-2 py-0.5 rounded-md font-medium">
                    {{ $creances['clients']->count() }} client(s)
                </span>
            </div>
            @forelse($creances['clients'] as $c)
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-none">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-[11px] font-semibold text-gray-500 shrink-0">
                        {{ strtoupper(substr($c->customer_name ?? 'A', 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium">{{ $c->customer_name ?: 'Anonyme' }}</p>
                        <p class="text-xs text-gray-400">{{ $c->customer_phone ?? '' }} · {{ $c->count }} vente(s)</p>
                    </div>
                </div>
                <span class="text-sm font-semibold text-red-600">{{ number_format($c->solde, 0, ',', ' ') }}</span>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-10">
                <x-heroicon-o-check-circle class="w-8 h-8 text-green-300 mb-2"/>
                <p class="text-sm text-gray-400">Aucune créance client</p>
            </div>
            @endforelse
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium">Revendeurs</p>
                    <p class="text-xs text-gray-400 mt-0.5">Total : {{ number_format($creances['total_resellers'], 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</p>
                </div>
                <span class="text-[11px] bg-red-50 text-red-700 px-2 py-0.5 rounded-md font-medium">
                    {{ $creances['resellers']->count() }} revendeur(s)
                </span>
            </div>
            @forelse($creances['resellers'] as $r)
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-none">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center text-[11px] font-semibold text-purple-600 shrink-0">
                        {{ strtoupper(substr($r->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium">{{ $r->name }}</p>
                        <p class="text-xs text-gray-400">{{ $r->count }} vente(s) en attente</p>
                    </div>
                </div>
                <span class="text-sm font-semibold text-red-600">{{ number_format($r->solde, 0, ',', ' ') }}</span>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-10">
                <x-heroicon-o-check-circle class="w-8 h-8 text-green-300 mb-2"/>
                <p class="text-sm text-gray-400">Aucune créance revendeur</p>
            </div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- ─── Panel Stock ───────────────────────────────────────── --}}
    @if($activeTab === 'stock')
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <p class="text-sm font-medium mb-4">Répartition des mouvements</p>
            <div style="position:relative;height:240px;">
                <canvas id="stockChart"></canvas>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <p class="text-sm font-medium mb-1">Détail par type</p>
            <p class="text-xs text-gray-400 mb-5">Total : {{ $stockStats['total'] }} mouvement(s)</p>
            @forelse($stockStats['rows'] as $row)
            @php
                $type  = $row['type'] instanceof \App\Enums\StockMovementType
                            ? $row['type']
                            : \App\Enums\StockMovementType::from($row['type']);
                $pct   = $stockStats['total'] > 0 ? round(($row['count'] / $stockStats['total']) * 100) : 0;
                $barColors = [
                    'stock_in'        => '#3B6D11',
                    'sale_out'        => '#2563EB',
                    'client_return'   => '#0ea5e9',
                    'supplier_return' => '#854F0B',
                    'transfer'        => '#7c3aed',
                    'adjustment'      => '#f59e0b',
                    'loss'            => '#DC2626',
                    'trade_in'        => '#f97316',
                ];
                $barColor = $barColors[$type->value] ?? '#d1d5db';
            @endphp
            <div class="flex items-center gap-3 mb-3.5">
                <span class="text-sm text-gray-700 w-36 shrink-0">{{ $type->label() }}</span>
                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full transition-all duration-500"
                        style="width: {{ $pct }}%; background-color: {{ $barColor }}"></div>
                </div>
                <span class="text-sm font-semibold text-gray-900 min-w-8 text-right">{{ $row['count'] }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-6">Aucun mouvement sur cette période</p>
            @endforelse
        </div>
    </div>
    @endif

    {{-- ─── Scripts Charts ────────────────────────────────────── --}}
    @script
    <script>
        const _charts = {};

        function destroyCharts() {
            Object.keys(_charts).forEach(key => {
                if (_charts[key]) {
                    _charts[key].destroy();
                    delete _charts[key];
                }
            });
        }

        function makeChart(id, config) {
            const el = document.getElementById(id);
            if (!el) return;
            if (_charts[id]) { _charts[id].destroy(); }
            _charts[id] = new Chart(el, config);
        }

        function initCharts() {
            if (typeof Chart === 'undefined') return;
            destroyCharts();

            const activeTab = @this.activeTab;
            const isAdmin   = @json($isAdmin);

            // ── CA ──────────────────────────────────────────────────
            if (activeTab === 'ca') {
                const caLabels = @json($caStats['rows']->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m')));
                const caData   = @json($caStats['rows']->pluck('total'));
                const paidData = @json($caStats['rows']->pluck('paid'));

                makeChart('caChart', {
                    type: 'bar',
                    data: {
                        labels: caLabels,
                        datasets: [{ label: 'CA', data: caData, backgroundColor: '#2563EB', borderRadius: 6, barThickness: 20 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { grid: { color: '#f0ede9' }, border: { dash: [3,3] } },
                            x: { grid: { display: false } }
                        }
                    }
                });

                makeChart('paidChart', {
                    type: 'line',
                    data: {
                        labels: caLabels,
                        datasets: [
                            { label: 'CA',       data: caData,   borderColor: '#2563EB', backgroundColor: 'rgba(37,99,235,0.07)', tension: 0.4, fill: true, pointRadius: 4, pointBackgroundColor: '#2563EB' },
                            { label: 'Encaissé', data: paidData, borderColor: '#3B6D11', backgroundColor: 'rgba(59,109,17,0.07)',  tension: 0.4, fill: true, pointRadius: 4, pointBackgroundColor: '#3B6D11' }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'top', labels: { boxWidth: 10, padding: 16 } } },
                        scales: {
                            y: { grid: { color: '#f0ede9' }, border: { dash: [3,3] } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // ── Marge ───────────────────────────────────────────────
            if (activeTab === 'margin' && isAdmin) {
                makeChart('marginChart', {
                    type: 'bar',
                    data: {
                        labels: @json($marginStats['rows']->pluck('brand')),
                        datasets: [
                            { label: 'CA',       data: @json($marginStats['rows']->pluck('ca')),     backgroundColor: '#BFDBFE', borderRadius: 4, barThickness: 22 },
                            { label: 'Coût',     data: @json($marginStats['rows']->pluck('cost')),   backgroundColor: '#FCA5A5', borderRadius: 4, barThickness: 22 },
                            { label: 'Bénéfice', data: @json($marginStats['rows']->pluck('profit')), backgroundColor: '#86EFAC', borderRadius: 4, barThickness: 22 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'top', labels: { boxWidth: 10, padding: 12 } } },
                        scales: {
                            y: { grid: { color: '#f0ede9' }, border: { dash: [3,3] } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // ── Top Produits ────────────────────────────────────────
            if (activeTab === 'products') {
                const top5 = @json(collect($topProducts)->take(5)->values());

                // Labels tronqués pour l'affichage
                const shortLabels = top5.map(r => r.product.length > 20 ? r.product.slice(0, 18) + '…' : r.product);
                // Noms complets pour le hover
                const fullLabels  = top5.map(r => r.product);

                const tooltipPlugin = {
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: (items) => fullLabels[items[0].dataIndex]
                            }
                        }
                    }
                };

                makeChart('topQtyChart', {
                    type: 'bar',
                    data: {
                        labels: shortLabels,
                        datasets: [{ data: top5.map(r => r.qty), backgroundColor: '#EEEDFE', borderRadius: 6, barThickness: 22 }]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        ...tooltipPlugin,
                        scales: {
                            x: { grid: { color: '#f0ede9' }, border: { dash: [3,3] } },
                            y: { grid: { display: false } }
                        }
                    }
                });

                makeChart('topCaChart', {
                    type: 'bar',
                    data: {
                        labels: shortLabels,
                        datasets: [{ data: top5.map(r => r.ca), backgroundColor: '#2563EB', borderRadius: 6, barThickness: 22 }]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        ...tooltipPlugin,
                        scales: {
                            x: { grid: { color: '#f0ede9' }, border: { dash: [3,3] } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            }

            // ── Stock ───────────────────────────────────────────────
            if (activeTab === 'stock') {
                const colorMap = {
                    'stock_in':        '#3B6D11',  // vert foncé   — entrée
                    'sale_out':        '#2563EB',  // bleu          — vente
                    'client_return':   '#0ea5e9',  // bleu clair    — retour client
                    'supplier_return': '#854F0B',  // marron        — retour fournisseur
                    'transfer':        '#7c3aed',  // violet        — transfert
                    'adjustment':      '#f59e0b',  // amber         — ajustement
                    'loss':            '#DC2626',  // rouge foncé   — perte
                    'trade_in':        '#f97316',  // orange        — reprise
                };

                const stockRows  = @json($stockStats['rows']);
                const stockTypes = stockRows.map(r => typeof r.type === 'string' ? r.type : r.type.value);
                const stockLabels = @json($stockStats['rows']->map(fn($r) => ($r['type'] instanceof \App\Enums\StockMovementType ? $r['type'] : \App\Enums\StockMovementType::from($r['type']))->label()));

                makeChart('stockChart', {
                    type: 'doughnut',
                    data: {
                        labels: stockLabels,
                        datasets: [{
                            data: @json($stockStats['rows']->pluck('count')),
                            backgroundColor: stockTypes.map(t => colorMap[t] ?? '#d1d5db'),
                            borderWidth: 0,
                            hoverOffset: 4,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { boxWidth: 10, padding: 14, font: { size: 12 } }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }
        }

        $wire.on('open-tab', ({ url }) => {
            window.open(url, '_blank');
        });

        // Premier chargement
        setTimeout(initCharts, 80);

        // Après chaque update Livewire (changement de période, de tab...)
        Livewire.hook('morph.updated', () => setTimeout(initCharts, 80));
    </script>
    @endscript
</div>
