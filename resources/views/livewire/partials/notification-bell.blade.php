<div class="relative" x-data="{ open: false }">
    {{-- Cloche --}}
    <button
        @click="open = !open"
        class="btn btn-ghost btn-sm relative"
    >
        <x-mary-icon name="o-bell" class="w-5 h-5" />
        @if($unreadCount > 0)
        <span class="absolute -top-1 -right-1 w-4 h-4 bg-error rounded-full text-white text-xs flex items-center justify-center">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        @click.outside="open = false"
        x-transition
        class="absolute right-0 top-10 w-80 bg-base-100 rounded-xl shadow-xl border border-base-200 z-50"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-base-200">
            <p class="font-semibold text-sm">Notifications</p>
            @if($unreadCount > 0)
            <button wire:click="markAllRead" class="text-xs text-primary hover:underline">
                Tout marquer lu
            </button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse($notifications as $notif)
            @php $data = $notif->data; @endphp

                href="{{ $data['url'] ?? '#' }}"
                wire:click="markRead('{{ $notif->id }}')"
                class="flex items-start gap-3 px-4 py-3 hover:bg-base-200 transition {{ $notif->read_at ? 'opacity-60' : '' }} border-b border-base-100"
            >
                <div class="mt-0.5">
                    <x-mary-icon
                        name="{{ $data['icon'] ?? 'o-bell' }}"
                        class="w-5 h-5 text-{{ $data['color'] ?? 'primary' }}"
                    />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium {{ !$notif->read_at ? 'text-base-content' : 'text-gray-400' }}">
                        {{ $data['title'] }}
                    </p>
                    <p class="text-xs text-gray-400 truncate">{{ $data['message'] }}</p>
                    <p class="text-xs text-gray-300 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
                @if(!$notif->read_at)
                <div class="w-2 h-2 rounded-full bg-primary mt-1 shrink-0"></div>
                @endif
            </a>
            @empty
            <div class="px-4 py-8 text-center text-gray-400 text-sm">
                Aucune notification
            </div>
            @endforelse
        </div>
    </div>
</div>
