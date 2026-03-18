<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? \App\Models\Setting::get('boutique.nom', config('boutique.nom')) }}</title>
    <x-theme-vars />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen" style="background: #fafafa;">

<x-mary-nav sticky full-width class="border-b border-zinc-100 bg-white/90 backdrop-blur-md shadow-none px-4" style="height: 56px;">
    <x-slot:brand>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-1">
            @php $logo = \App\Models\Setting::get('boutique.logo'); @endphp
            @if($logo)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}" alt="Logo" class="w-7 h-7 object-contain rounded-lg" />
            @else
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                     style="background: var(--boutique-primary, #18181b);">
                    {{ strtoupper(substr(\App\Models\Setting::get('boutique.nom', config('boutique.nom')), 0, 1)) }}
                </div>
            @endif
            <span class="font-semibold text-sm text-zinc-900 tracking-tight hidden lg:block">
                {{ \App\Models\Setting::get('boutique.nom', config('boutique.nom')) }}
            </span>
        </a>
    </x-slot:brand>

    <x-slot:actions>
        <livewire:partials.notification-bell />

        <x-mary-dropdown>
            <x-slot:trigger>
                <button class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-zinc-50 transition-colors text-sm text-zinc-700 font-medium">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-semibold"
                         style="background: var(--boutique-primary, #18181b);">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="hidden md:block">{{ auth()->user()->name }}</span>
                    <x-mary-icon name="o-chevron-down" class="w-3 h-3 text-zinc-400" />
                </button>
            </x-slot:trigger>

            <div class="p-1 min-w-44">
                <div class="px-3 py-2 mb-1">
                    <p class="text-xs font-medium text-zinc-900">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-zinc-400">{{ auth()->user()->role->label() }}</p>
                </div>
                <div class="border-t border-zinc-100 my-1"></div>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 rounded-lg transition-colors">
                    <x-mary-icon name="o-cog-6-tooth" class="w-4 h-4 text-zinc-400" />
                    Paramètres
                </a>
                @endif
                <button onclick="document.getElementById('logout-form').submit()"
                        class="w-full flex items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 rounded-lg transition-colors text-left">
                    <x-mary-icon name="o-arrow-right-start-on-rectangle" class="w-4 h-4 text-zinc-400" />
                    Déconnexion
                </button>
            </div>
        </x-mary-dropdown>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    </x-slot:actions>
</x-mary-nav>

<x-mary-main with-nav full-width>
    <x-slot:sidebar drawer="main-drawer" collapsible
        class="border-r border-white/10 pt-2 pb-6 px-3"
        style="background: var(--boutique-sidebar-bg, #18181b);"
    >
        <x-mary-menu activate-by-route class="space-y-0.5">

            <x-mary-menu-item title="Dashboard" icon="o-home" link="{{ route('dashboard') }}" />

            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-semibold uppercase tracking-widest" style="color: var(--boutique-sidebar-text, #a1a1aa); opacity: 0.5;">Catalogue</p>
            </div>
            <x-mary-menu-item title="Produits"  icon="o-device-phone-mobile" link="{{ route('products.index') }}" />
            <x-mary-menu-item title="Modèles"   icon="o-rectangle-stack"    link="{{ route('product-models.index') }}" />
            <x-mary-menu-item title="Marques"   icon="o-tag"                link="{{ route('brands.index') }}" />

            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-semibold uppercase tracking-widest" style="color: var(--boutique-sidebar-text, #a1a1aa); opacity: 0.5;">Ventes</p>
            </div>
            <x-mary-menu-item title="Nouvelle vente" icon="o-plus-circle" link="{{ route('sales.create') }}" />
            <x-mary-menu-item title="Historique"     icon="o-clock"       link="{{ route('sales.index') }}" />
            <x-mary-menu-item title="Revendeurs"     icon="o-users"       link="{{ route('resellers.index') }}" />

            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-semibold uppercase tracking-widest" style="color: var(--boutique-sidebar-text, #a1a1aa); opacity: 0.5;">Stock</p>
            </div>
            <x-mary-menu-item title="Entrées stock" icon="o-arrow-down-tray"    link="{{ route('purchases.index') }}" />
            <x-mary-menu-item title="Mouvements"    icon="o-arrows-right-left"  link="{{ route('stock-movements.index') }}" />
            <x-mary-menu-item title="Fournisseurs"  icon="o-truck"              link="{{ route('suppliers.index') }}" />

            @if(auth()->user()->isAdmin())
            <div class="pt-3 pb-1 px-3">
                <p class="text-[10px] font-semibold uppercase tracking-widest" style="color: var(--boutique-sidebar-text, #a1a1aa); opacity: 0.5;">Admin</p>
            </div>
            <x-mary-menu-item title="Rapports"    icon="o-chart-bar"              link="{{ route('reports.index') }}" />
            <x-mary-menu-item title="Activités"   icon="o-eye"                    link="{{ route('activity-logs.index') }}" />
            <x-mary-menu-item title="Utilisateurs" icon="o-user-group"            link="{{ route('users.index') }}" />
            <x-mary-menu-item title="Paramètres"  icon="o-cog-6-tooth"            link="{{ route('settings.index') }}" />
            @endif

        </x-mary-menu>
    </x-slot:sidebar>

    <x-slot:content class="p-6 max-w-[1400px] mx-auto">
        {{ $slot }}
    </x-slot:content>
</x-mary-main>

<x-mary-toast />
@livewireScripts
@stack('scripts')
</body>
</html>
