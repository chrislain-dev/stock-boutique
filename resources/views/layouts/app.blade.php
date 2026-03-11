<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? Setting::get('boutique.nom', config('boutique.nom')) }}</title>
    <x-theme-vars />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-base-200">

    {{-- Sidebar --}}
    <x-mary-nav sticky full-width>

        <x-slot:brand>
            <div class="flex items-center gap-3 px-2">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                     style="background-color: var(--boutique-primary)">
                    <span class="text-white text-sm font-bold">
                        {{ strtoupper(substr(Setting::get('boutique.nom', config('boutique.nom')), 0, 2)) }}
                    </span>
                </div>
                <span class="font-bold text-base hidden lg:block">
                    {{ Setting::get('boutique.nom', config('boutique.nom')) }}
                </span>
            </div>
        </x-slot:brand>

        <x-slot:actions>
            {{-- Alerte stock bas --}}
            <livewire:partials.stock-alert-badge />

            {{-- User menu --}}
            <x-mary-dropdown>
                <x-slot:trigger>
                    <x-mary-button icon="o-user-circle" class="btn-ghost btn-sm">
                        <span class="hidden md:block">{{ auth()->user()->name }}</span>
                    </x-mary-button>
                </x-slot:trigger>

                <x-mary-menu-item
                    title="{{ auth()->user()->role->label() }}"
                    icon="o-identification"
                    class="text-xs opacity-60 pointer-events-none"
                />

                <x-mary-menu-separator />

                @if(auth()->user()->isAdmin())
                <x-mary-menu-item
                    title="Paramètres"
                    icon="o-cog-6-tooth"
                    link="{{ route('settings.index') }}"
                />
                @endif

                <x-mary-menu-item
                    title="Déconnexion"
                    icon="o-arrow-right-start-on-rectangle"
                    x-on:click="document.getElementById('logout-form').submit()"
                />
            </x-mary-dropdown>

            <form id="logout-form" action="{{ route('logout') }}"
                  method="POST" class="hidden">
                @csrf
            </form>
        </x-slot:actions>

    </x-mary-nav>

    {{-- Drawer sidebar pour mobile + desktop --}}
    <x-mary-main with-nav full-width>

        <x-slot:sidebar drawer="main-drawer" collapsible
            class="bg-base-100 border-r border-base-200"
        >
            <x-mary-menu activate-by-route>

                {{-- Dashboard --}}
                <x-mary-menu-item
                    title="Dashboard"
                    icon="o-home"
                    link="{{ route('dashboard') }}"
                />

                {{-- Catalogue --}}
                <x-mary-menu-sub title="Catalogue" icon="o-squares-2x2">
                    <x-mary-menu-item
                        title="Produits"
                        icon="o-device-phone-mobile"
                        link="{{ route('products.index') }}"
                    />
                    <x-mary-menu-item
                        title="Modèles"
                        icon="o-rectangle-stack"
                        link="{{ route('product-models.index') }}"
                    />
                    <x-mary-menu-item
                        title="Marques"
                        icon="o-tag"
                        link="{{ route('brands.index') }}"
                    />
                </x-mary-menu-sub>

                {{-- Ventes --}}
                <x-mary-menu-sub title="Ventes" icon="o-shopping-bag">
                    <x-mary-menu-item
                        title="Nouvelle vente"
                        icon="o-plus-circle"
                        link="{{ route('sales.create') }}"
                    />
                    <x-mary-menu-item
                        title="Historique"
                        icon="o-clock"
                        link="{{ route('sales.index') }}"
                    />
                    <x-mary-menu-item
                        title="Revendeurs"
                        icon="o-users"
                        link="{{ route('resellers.index') }}"
                    />
                </x-mary-menu-sub>

                {{-- Stock --}}
                <x-mary-menu-sub title="Stock" icon="o-archive-box">
                    <x-mary-menu-item
                        title="Entrées stock"
                        icon="o-arrow-down-tray"
                        link="{{ route('purchases.index') }}"
                    />
                    <x-mary-menu-item
                        title="Mouvements"
                        icon="o-arrows-right-left"
                        link="{{ route('stock-movements.index') }}"
                    />
                    <x-mary-menu-item
                        title="Fournisseurs"
                        icon="o-truck"
                        link="{{ route('suppliers.index') }}"
                    />
                </x-mary-menu-sub>

                {{-- Admin uniquement --}}
                @if(auth()->user()->isAdmin())
                <x-mary-menu-sub title="Rapports" icon="o-chart-bar">
                    <x-mary-menu-item
                        title="Statistiques"
                        icon="o-presentation-chart-line"
                        link="{{ route('reports.index') }}"
                    />
                    <x-mary-menu-item
                        title="Activités"
                        icon="o-eye"
                        link="{{ route('activity-logs.index') }}"
                    />
                </x-mary-menu-sub>

                <x-mary-menu-item
                    title="Utilisateurs"
                    icon="o-user-group"
                    link="{{ route('users.index') }}"
                />

                <x-mary-menu-item
                    title="Paramètres"
                    icon="o-cog-6-tooth"
                    link="{{ route('settings.index') }}"
                />
                @endif

            </x-mary-menu>
        </x-slot:sidebar>

        {{-- Contenu principal --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>

    </x-mary-main>

    @livewireScripts
</body>
</html>
