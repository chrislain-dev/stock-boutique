<div>
    <x-mary-header title="Journaux d'activité" subtitle="Traçabilité complète" icon="o-clipboard-document-list" />

    {{-- Filtres --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <x-mary-input
            wire:model.live.debounce="search"
            placeholder="Rechercher..."
            icon="o-magnifying-glass"
            clearable
        />
        <x-mary-select
            wire:model.live="actionFilter"
            :options="$actions"
            option-value="id"
            option-label="name"
        />
        <x-mary-select
            wire:model.live="userFilter"
            :options="$users"
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
            ['key'=>'action',      'label'=>'Action'],
            ['key'=>'description', 'label'=>'Description'],
            ['key'=>'user',        'label'=>'Utilisateur'],
            ['key'=>'ip_address',  'label'=>'IP'],
            ['key'=>'created_at',  'label'=>'Date'],
        ]" :rows="$logs" with-pagination>

            @scope('cell_action', $log)
                @php
                    $color = match($log->action) {
                        'create'  => 'success',
                        'update'  => 'info',
                        'delete'  => 'error',
                        'login'   => 'primary',
                        'logout'  => 'ghost',
                        'export'  => 'warning',
                        default   => 'ghost',
                    };
                    $label = match($log->action) {
                        'create'  => 'Création',
                        'update'  => 'Modification',
                        'delete'  => 'Suppression',
                        'restore' => 'Restauration',
                        'login'   => 'Connexion',
                        'logout'  => 'Déconnexion',
                        'export'  => 'Export',
                        default   => $log->action,
                    };
                @endphp
                <x-mary-badge value="{{ $label }}" class="badge-sm badge-{{ $color }}" />
            @endscope

            @scope('cell_description', $log)
                <p class="text-sm">{{ $log->description }}</p>
                @if($log->old_values || $log->new_values)
                <details class="mt-1">
                    <summary class="text-xs text-gray-400 cursor-pointer">Détails</summary>
                    <div class="text-xs mt-1 space-y-1">
                        @if($log->old_values)
                        <p class="text-error">Avant: {{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE) }}</p>
                        @endif
                        @if($log->new_values)
                        <p class="text-success">Après: {{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE) }}</p>
                        @endif
                    </div>
                </details>
                @endif
            @endscope

            @scope('cell_user', $log)
                <p class="text-sm font-medium">{{ $log->user?->name ?? '—' }}</p>
            @endscope

            @scope('cell_ip_address', $log)
                <span class="text-xs font-mono text-gray-400">{{ $log->ip_address ?? '—' }}</span>
            @endscope

            @scope('cell_created_at', $log)
                {{ $log->created_at->format('d/m/Y H:i:s') }}
            @endscope

        </x-mary-table>
    </x-mary-card>
</div>
