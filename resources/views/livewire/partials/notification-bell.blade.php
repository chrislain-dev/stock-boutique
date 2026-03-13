<div x-data="{ open: false }" class="relative">
    {{-- Cloche --}}
    <button @click="open = !open" class="btn btn-ghost btn-sm relative">
        <x-mary-icon name="o-bell" class="w-5 h-5" />
        @if($unreadCount > 0)
        <span class="absolute -top-1 -right-1 w-4 h-4 bg-error rounded-full text-white text-xs flex items-center justify-center font-bold">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-cloak
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="absolute right-0 top-12 w-80 bg-base-100 rounded-xl shadow-2xl border border-base-200 z-[9999]"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-base-200">
            <p class="font-semibold text-sm">Notifications</p>
            @if($unreadCount > 0)
            <button wire:click="markAllRead" class="text-xs text-primary hover:underline">
                Tout marquer lu
            </button>
            @endif
        </div>

        {{-- Liste --}}
        <div class="max-h-96 overflow-y-auto divide-y divide-base-200">
            @forelse($notifications as $notif)
            @php $data = $notif->data; @endphp
            <div
                class="flex items-start gap-3 px-4 py-3 hover:bg-base-200 cursor-pointer transition {{ $notif->read_at ? 'opacity-60' : 'bg-primary/5' }}"
                wire:click="markRead('{{ $notif->id }}')"
                x-on:click="window.location.href = '{{ $data['url'] ?? '#' }}'"
            >
                <div class="mt-0.5 shrink-0">
                    @php
                        $color = match($data['color'] ?? 'primary') {
                            'success' => 'text-success',
                            'warning' => 'text-warning',
                            'error'   => 'text-error',
                            'info'    => 'text-info',
                            default   => 'text-primary',
                        };
                    @endphp
                    <x-mary-icon name="{{ $data['icon'] ?? 'o-bell' }}" class="w-5 h-5 {{ $color }}" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold">{{ $data['title'] ?? '' }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $data['message'] ?? '' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
                @if(!$notif->read_at)
                <div class="w-2 h-2 rounded-full bg-primary mt-2 shrink-0"></div>
                @endif
            </div>
            @empty
            <div class="px-4 py-10 text-center">
                <x-mary-icon name="o-bell-slash" class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                <p class="text-sm text-gray-400">Aucune notification</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
