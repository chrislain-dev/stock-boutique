<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Journaux d'activité</h1>
            <p class="text-sm text-gray-400 mt-0.5">Traçabilité complète de toutes les actions</p>
        </div>
        <div class="inline-flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-1.5 text-xs text-gray-500">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
            Suivi en temps réel
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-plus class="w-4 h-4 text-green-700"/>
            </div>
            <div><p class="text-base font-semibold">{{ $totalCounts['create'] ?? 0 }}</p><p class="text-[11px] text-gray-400">Créations</p></div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-pencil class="w-4 h-4 text-blue-600"/>
            </div>
            <div><p class="text-base font-semibold">{{ $totalCounts['update'] ?? 0 }}</p><p class="text-[11px] text-gray-400">Modifications</p></div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-trash class="w-4 h-4 text-red-500"/>
            </div>
            <div><p class="text-base font-semibold">{{ $totalCounts['delete'] ?? 0 }}</p><p class="text-[11px] text-gray-400">Suppressions</p></div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4 text-purple-600"/>
            </div>
            <div><p class="text-base font-semibold">{{ $totalCounts['login'] ?? 0 }}</p><p class="text-[11px] text-gray-400">Connexions</p></div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 flex items-center gap-2.5 col-span-2 sm:col-span-1">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-amber-700"/>
            </div>
            <div><p class="text-base font-semibold">{{ $totalCounts['export'] ?? 0 }}</p><p class="text-[11px] text-gray-400">Exports</p></div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 bg-white border border-gray-200 rounded-2xl px-4 py-3.5 mb-4">
        {{-- Recherche --}}
        <div class="sm:col-span-2 lg:col-span-1">
            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1.5">Recherche</p>
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400"/>
                <input wire:model.live.debounce="search" type="text"
                    placeholder="Description, utilisateur..."
                    class="w-full h-8 pl-8 pr-3 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 focus:bg-white transition-colors"/>
            </div>
        </div>

        {{-- Action --}}
        <div>
            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1.5">Action</p>
            <select wire:model.live="actionFilter"
                    class="w-full h-8 px-2.5 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 text-gray-700">
                @foreach($actions as $a)
                <option value="{{ $a['id'] }}">{{ $a['name'] }}</option>
                @endforeach
            </select>
        </div>

        {{-- Utilisateur --}}
        <div>
            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1.5">Utilisateur</p>
            <select wire:model.live="userFilter"
                    class="w-full h-8 px-2.5 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 text-gray-700">
                @foreach($users as $u)
                <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                @endforeach
            </select>
        </div>

        {{-- Dates --}}
        <div class="sm:col-span-2 lg:col-span-2">
            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1.5">Période</p>
            <div class="flex items-center gap-1.5">
                <input wire:model.live="dateFrom" type="date"
                    class="flex-1 min-w-0 h-8 px-2 text-xs border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400"/>
                <span class="text-gray-300 text-xs shrink-0">→</span>
                <input wire:model.live="dateTo" type="date"
                    class="flex-1 min-w-0 h-8 px-2 text-xs border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400"/>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">

        {{-- En-tête desktop uniquement --}}
        <div class="hidden lg:grid grid-cols-[110px_1fr_150px_120px_150px] px-4 py-2.5 border-b border-gray-100 bg-gray-50">
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Action</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Description</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Utilisateur</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">IP</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Date</span>
        </div>

        @forelse($logs as $log)
        @php
            $badgeConfig = match($log->action) {
                'create'  => ['label' => 'Création',     'class' => 'bg-green-50 text-green-800',  'dot' => 'bg-green-700'],
                'update'  => ['label' => 'Modification', 'class' => 'bg-blue-50 text-blue-800',    'dot' => 'bg-blue-600'],
                'delete'  => ['label' => 'Suppression',  'class' => 'bg-red-50 text-red-700',      'dot' => 'bg-red-600'],
                'restore' => ['label' => 'Restauration', 'class' => 'bg-teal-50 text-teal-800',    'dot' => 'bg-teal-600'],
                'login'   => ['label' => 'Connexion',    'class' => 'bg-purple-50 text-purple-800','dot' => 'bg-purple-600'],
                'logout'  => ['label' => 'Déconnexion',  'class' => 'bg-gray-100 text-gray-600',   'dot' => 'bg-gray-400'],
                'export'  => ['label' => 'Export',       'class' => 'bg-amber-50 text-amber-800',  'dot' => 'bg-amber-600'],
                default   => ['label' => $log->action,   'class' => 'bg-gray-100 text-gray-600',   'dot' => 'bg-gray-400'],
            };
        @endphp

        {{-- ── Ligne desktop ── --}}
        <div class="hidden lg:grid grid-cols-[110px_1fr_150px_120px_150px] px-4 py-3 border-b border-gray-100 last:border-none items-start hover:bg-gray-50 transition-colors">

            {{-- Badge action --}}
            <div class="pt-0.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium {{ $badgeConfig['class'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $badgeConfig['dot'] }}"></span>
                    {{ $badgeConfig['label'] }}
                </span>
            </div>

            {{-- Description + détails --}}
            <div>
                <p class="text-sm text-gray-800 leading-snug">{{ $log->description }}</p>
                @include('livewire.logs._details', ['log' => $log])
            </div>

            {{-- Utilisateur --}}
            <div class="flex items-center gap-2">
                @if($log->user)
                <div class="w-6 h-6 rounded-md bg-purple-50 flex items-center justify-center text-[10px] font-semibold text-purple-600 shrink-0">
                    {{ strtoupper(substr($log->user->name, 0, 2)) }}
                </div>
                <span class="text-sm font-medium text-gray-800 truncate">{{ $log->user->name }}</span>
                @else
                <span class="text-sm text-gray-400">—</span>
                @endif
            </div>

            {{-- IP --}}
            <div>
                <span class="text-[11px] font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md">
                    {{ $log->ip_address ?? '—' }}
                </span>
            </div>

            {{-- Date --}}
            <div>
                <p class="text-sm text-gray-600">{{ $log->created_at->format('d/m/Y') }}</p>
                <p class="text-[11px] font-mono text-gray-400 mt-0.5">{{ $log->created_at->format('H:i:s') }}</p>
            </div>
        </div>

        {{-- ── Carte mobile ── --}}
        <div class="lg:hidden px-4 py-3.5 border-b border-gray-100 last:border-none hover:bg-gray-50 transition-colors space-y-2.5">

            {{-- Ligne 1 : badge + date --}}
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium {{ $badgeConfig['class'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $badgeConfig['dot'] }}"></span>
                    {{ $badgeConfig['label'] }}
                </span>
                <div class="text-right">
                    <p class="text-xs text-gray-500">{{ $log->created_at->format('d/m/Y') }}</p>
                    <p class="text-[11px] font-mono text-gray-400">{{ $log->created_at->format('H:i:s') }}</p>
                </div>
            </div>

            {{-- Ligne 2 : description --}}
            <p class="text-sm text-gray-800 leading-snug">{{ $log->description }}</p>

            {{-- Ligne 3 : utilisateur + IP --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @if($log->user)
                    <div class="w-6 h-6 rounded-md bg-purple-50 flex items-center justify-center text-[10px] font-semibold text-purple-600 shrink-0">
                        {{ strtoupper(substr($log->user->name, 0, 2)) }}
                    </div>
                    <span class="text-sm font-medium text-gray-700">{{ $log->user->name }}</span>
                    @else
                    <span class="text-sm text-gray-400">—</span>
                    @endif
                </div>
                <span class="text-[11px] font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md">
                    {{ $log->ip_address ?? '—' }}
                </span>
            </div>

            {{-- Détails --}}
            @include('livewire.logs._details', ['log' => $log])
        </div>

        @empty
        <div class="py-16 flex flex-col items-center justify-center">
            <x-heroicon-o-clipboard-document-list class="w-10 h-10 text-gray-200 mb-3"/>
            <p class="text-sm text-gray-400">Aucun journal d'activité trouvé</p>
        </div>
        @endforelse

        {{-- Pagination --}}
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>
