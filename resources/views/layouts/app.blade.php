<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? \App\Models\Setting::get('boutique.nom', config('boutique.nom')) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600&display=swap" rel="stylesheet">
    <x-theme-vars />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Geist', -apple-system, sans-serif;
            background: #f5f4f1;
            min-height: 100vh;
            margin: 0;
        }

        /* ── Navbar ─────────────────────────────────────── */
        .app-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 52px;
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 100;
            transition: box-shadow 0.3s ease;
        }
        .app-nav.scrolled { box-shadow: 0 1px 20px rgba(0,0,0,0.06); }

        .nav-left { display: flex; align-items: center; gap: 10px; }
        .nav-actions { display: flex; align-items: center; gap: 8px; }

        .nav-brand {
            display: flex; align-items: center; gap: 9px;
            text-decoration: none; flex-shrink: 0;
        }
        .nav-brand-logo {
            width: 28px; height: 28px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            background: var(--boutique-primary, #18181b);
            transition: transform 0.2s cubic-bezier(.34,1.56,.64,1);
            flex-shrink: 0;
            overflow: hidden;
        }
        .nav-brand:hover .nav-brand-logo { transform: scale(1.08) rotate(-3deg); }
        .nav-brand-name {
            font-size: 14px; font-weight: 600;
            color: #111; letter-spacing: -0.3px;
        }

        /* ── Sidebar toggle (mobile) ─────────────────── */
        .sidebar-toggle {
            display: none;
            width: 34px; height: 34px;
            border-radius: 9px;
            border: 1px solid #e8e6e2;
            background: #fff;
            align-items: center; justify-content: center;
            cursor: pointer;
            transition: background 0.15s;
            flex-shrink: 0;
        }
        .sidebar-toggle svg { width: 16px; height: 16px; color: #555; }
        .sidebar-toggle:hover { background: #f4f2ef; }

        /* ── User dropdown ───────────────────────────── */
        .user-dropdown { position: relative; }

        .nav-user-btn {
            display: flex; align-items: center; gap: 7px;
            padding: 5px 10px 5px 5px;
            border-radius: 10px;
            border: none; background: transparent;
            cursor: pointer;
            transition: background 0.15s;
            color: #444;
            font-family: 'Geist', sans-serif;
            font-size: 13px; font-weight: 500;
        }
        .nav-user-btn:hover { background: #f4f2ef; }

        .nav-avatar {
            width: 26px; height: 26px;
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 600; color: #fff;
            background: var(--boutique-primary, #18181b);
            flex-shrink: 0;
        }

        .user-dropdown-menu {
            position: absolute;
            top: calc(100% + 6px); right: 0;
            background: #fff;
            border: 1px solid #e8e6e2;
            border-radius: 14px;
            padding: 6px;
            min-width: 188px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1), 0 2px 8px rgba(0,0,0,0.06);
            opacity: 0;
            transform: translateY(-6px) scale(0.97);
            pointer-events: none;
            transition: opacity 0.18s ease, transform 0.18s cubic-bezier(.22,1,.36,1);
            z-index: 200;
        }
        .user-dropdown.open .user-dropdown-menu {
            opacity: 1; transform: translateY(0) scale(1); pointer-events: auto;
        }
        .dropdown-header {
            padding: 8px 10px 10px;
            border-bottom: 1px solid #f0ede9;
            margin-bottom: 4px;
        }
        .dropdown-header p { font-size: 13px; font-weight: 500; color: #111; margin: 0; }
        .dropdown-header span { font-size: 11px; color: #aaa; }
        .dropdown-item {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 13px; color: #444;
            text-decoration: none;
            transition: background 0.12s;
            cursor: pointer;
            border: none; background: transparent;
            width: 100%; text-align: left;
            font-family: 'Geist', sans-serif;
        }
        .dropdown-item:hover { background: #f4f2ef; color: #111; }
        .dropdown-item svg { width: 15px; height: 15px; color: #aaa; flex-shrink: 0; }
        .dropdown-item.danger:hover { background: #fef2f2; color: #dc2626; }
        .dropdown-item.danger:hover svg { color: #dc2626; }
        .dropdown-sep { height: 1px; background: #f0ede9; margin: 4px 0; }

        /* ── Layout ──────────────────────────────────── */
        .app-layout {
            display: flex;
            padding-top: 52px;
            min-height: 100vh;
        }

        /* ── Sidebar ─────────────────────────────────── */
        .app-sidebar {
            width: 220px;
            position: fixed;
            left: 0; top: 52px; bottom: 0;
            background: var(--boutique-sidebar-bg, #18181b);
            padding: 12px 10px 24px;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: none;
            display: flex;
            flex-direction: column;
            z-index: 90;
            transition: transform 0.3s cubic-bezier(.22,1,.36,1);
        }
        .app-sidebar::-webkit-scrollbar { display: none; }

        /* ── Sidebar overlay (mobile) ────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(2px);
            z-index: 89;
        }
        .sidebar-overlay.active { display: block; }

        /* ── Sidebar items ───────────────────────────── */
        .sidebar-section-label {
            font-size: 10px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 1px;
            padding: 0 8px; margin: 16px 0 4px;
            color: var(--boutique-sidebar-text, #a1a1aa);
            opacity: 0.4;
        }

        .sidebar-item {
            display: flex; align-items: center; gap: 9px;
            padding: 7px 10px;
            border-radius: 9px;
            text-decoration: none;
            font-size: 13px; font-weight: 400;
            color: var(--boutique-sidebar-text, #a1a1aa);
            opacity: 0.7;
            transition: background 0.15s, opacity 0.15s, color 0.15s;
            margin-bottom: 1px;
            position: relative;
            animation: sidebarItemIn 0.3s cubic-bezier(.22,1,.36,1) both;
            -webkit-tap-highlight-color: transparent;
        }
        .sidebar-item svg {
            width: 15px; height: 15px;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }
        .sidebar-item:hover {
            background: rgba(255,255,255,0.07);
            opacity: 1; color: #fff;
        }
        .sidebar-item:hover svg { transform: scale(1.1); }
        .sidebar-item.active {
            background: rgba(255,255,255,0.12);
            color: #fff; opacity: 1; font-weight: 500;
        }
        .sidebar-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 16px;
            background: #fff;
            border-radius: 0 3px 3px 0;
            opacity: 0.6;
        }

        .sidebar-toggle,
        .nav-user-btn,
        .dropdown-item {
            -webkit-tap-highlight-color: transparent;
        }

        /* ── Main content ────────────────────────────── */
        .app-content {
            margin-left: 220px;
            flex: 1;
            padding: 28px;
            max-width: calc(100% - 220px);
            min-height: calc(100vh - 52px);
            animation: contentFadeIn 0.35s cubic-bezier(.22,1,.36,1) both;
        }
        .app-content-inner { max-width: 1280px; margin: 0 auto; }

        /* ── Page transition ─────────────────────────── */
        .page-transition-overlay {
            position: fixed; inset: 0;
            background: rgba(255,255,255,0.35);
            z-index: 9999;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s ease;
        }
        .page-transition-overlay.active { opacity: 1; }

        /* ── Animations ──────────────────────────────── */
        @keyframes contentFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes sidebarItemIn {
            from { opacity: 0; transform: translateX(-8px); }
            to   { opacity: 0.7; transform: translateX(0); }
        }

        /* ── Responsive ──────────────────────────────── */
        @media (max-width: 1024px) {
            .app-sidebar { width: 200px; }
            .app-content { margin-left: 200px; max-width: calc(100% - 200px); padding: 20px; }
        }
        @media (max-width: 768px) {
            .sidebar-toggle { display: flex; }
            .app-sidebar { width: 220px; transform: translateX(-220px); box-shadow: none; }
            .app-sidebar.open { transform: translateX(0); box-shadow: 4px 0 24px rgba(0,0,0,0.15); }
            .app-content { margin-left: 0; max-width: 100%; padding: 16px; }
            .nav-brand-name { display: none; }
        }
    </style>
</head>
<body>

<div class="page-transition-overlay" id="pageTransition"></div>

{{-- ── Navbar ──────────────────────────────────────────────── --}}
<nav class="app-nav" id="appNav">
    <div class="nav-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>

        <a href="{{ route('dashboard') }}" class="nav-brand">
            @php $logo = \App\Models\Setting::get('boutique.logo'); @endphp
            @if($logo)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}"
                    alt="Logo"
                    style="height:36px;width:auto;object-fit:contain;display:block;"/>
            @else
                <div class="nav-brand-logo">
                    <span style="color:#fff;font-size:12px;font-weight:700;">
                        {{ strtoupper(substr(\App\Models\Setting::get('boutique.nom', config('boutique.nom')), 0, 1)) }}
                    </span>
                </div>
            @endif
            <span class="nav-brand-name">{{ \App\Models\Setting::get('boutique.nom', config('boutique.nom')) }}</span>
        </a>
    </div>

    <div class="nav-actions">
        <livewire:partials.notification-bell />

        <div class="user-dropdown" id="userDropdown">
            <button class="nav-user-btn" onclick="toggleUserMenu(event)">
                <div class="nav-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <span style="display:none;" class="md-name">{{ auth()->user()->name }}</span>
                <svg id="chevronIcon" style="width:12px;height:12px;color:#aaa;transition:transform 0.2s;flex-shrink:0;"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>
            <div class="user-dropdown-menu" id="userMenu">
                <div class="dropdown-header">
                    <p>{{ auth()->user()->name }}</p>
                    <span>{{ auth()->user()->role->label() }}</span>
                </div>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('settings.index') }}" class="dropdown-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
                    </svg>
                    Paramètres
                </a>
                @endif
                <div class="dropdown-sep"></div>
                <button class="dropdown-item danger" onclick="document.getElementById('logout-form').submit()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Déconnexion
                </button>
            </div>
        </div>
    </div>
</nav>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>

{{-- Overlay mobile sidebar ──────────────────────────────── --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- ── Layout ──────────────────────────────────────────────── --}}
<div class="app-layout">

    {{-- ── Sidebar ──────────────────────────────────────────── --}}
    <aside class="app-sidebar" id="appSidebar">

        <a href="{{ route('dashboard') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           style="animation-delay:0ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            Dashboard
        </a>

        <p class="sidebar-section-label" style="animation-delay:30ms">Catalogue</p>
        <a href="{{ route('products.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('products.*') ? 'active' : '' }}"
           style="animation-delay:45ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="5" y="2" width="14" height="20" rx="2"/><circle cx="12" cy="17" r="1"/>
            </svg>
            Produits
        </a>
        <a href="{{ route('product-models.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('product-models.*') ? 'active' : '' }}"
           style="animation-delay:60ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M3 6h18M3 12h18M3 18h18"/>
            </svg>
            Modèles
        </a>
        <a href="{{ route('brands.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('brands.*') ? 'active' : '' }}"
           style="animation-delay:75ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                <circle cx="7" cy="7" r="1.5" fill="currentColor"/>
            </svg>
            Marques
        </a>

        <p class="sidebar-section-label" style="animation-delay:95ms">Ventes</p>
        <a href="{{ route('sales.create') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('sales.create') ? 'active' : '' }}"
           style="animation-delay:110ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            Nouvelle vente
        </a>
        <a href="{{ route('sales.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('sales.index') || request()->routeIs('sales.show') ? 'active' : '' }}"
           style="animation-delay:125ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            Historique
        </a>
        <a href="{{ route('resellers.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('resellers.*') ? 'active' : '' }}"
           style="animation-delay:140ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
            Revendeurs
        </a>

        <p class="sidebar-section-label" style="animation-delay:158ms">Stock</p>
        <a href="{{ route('purchases.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('purchases.*') ? 'active' : '' }}"
           style="animation-delay:173ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Entrées stock
        </a>
        <a href="{{ route('stock-movements.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('stock-movements.*') ? 'active' : '' }}"
           style="animation-delay:188ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <polyline points="17 1 21 5 17 9"/>
                <path d="M3 11V9a4 4 0 014-4h14"/>
                <polyline points="7 23 3 19 7 15"/>
                <path d="M21 13v2a4 4 0 01-4 4H3"/>
            </svg>
            Mouvements
        </a>
        <a href="{{ route('reprises.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('reprises.*') ? 'active' : '' }}"
           style="animation-delay:203ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <polyline points="1 4 1 10 7 10"/>
                <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
            </svg>
            Reprises
        </a>
        <a href="{{ route('supplier-returns.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('supplier-returns.*') ? 'active' : '' }}"
           style="animation-delay:218ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <polyline points="9 14 4 19 9 24" transform="translate(0,-4)"/>
                <path d="M20 4H4a2 2 0 00-2 2v7"/>
                <polyline points="15 4 20 9 15 14" transform="translate(0,-4)"/>
            </svg>
            Retours fournisseur
        </a>
        <a href="{{ route('suppliers.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"
           style="animation-delay:233ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="1" y="3" width="15" height="13"/>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                <circle cx="5.5" cy="18.5" r="2.5"/>
                <circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
            Fournisseurs
        </a>

        @if(auth()->user()->isAdmin())
        <p class="sidebar-section-label" style="animation-delay:251ms">Admin</p>
        <a href="{{ route('reports.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('reports.*') ? 'active' : '' }}"
           style="animation-delay:266ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <line x1="18" y1="20" x2="18" y2="10"/>
                <line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6"  y1="20" x2="6"  y2="14"/>
            </svg>
            Rapports
        </a>
        <a href="{{ route('activity-logs.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}"
           style="animation-delay:281ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
            Activités
        </a>
        <a href="{{ route('users.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('users.*') ? 'active' : '' }}"
           style="animation-delay:296ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
            Utilisateurs
        </a>
        <a href="{{ route('settings.index') }}" wire:navigate
           class="sidebar-item {{ request()->routeIs('settings.*') ? 'active' : '' }}"
           style="animation-delay:311ms">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
            </svg>
            Paramètres
        </a>
        @endif

        <div style="margin-top:auto;padding:20px 8px 0;">
            <div style="height:1px;background:rgba(255,255,255,0.06);margin-bottom:12px;"></div>
            <p style="font-size:10px;color:rgba(255,255,255,0.18);letter-spacing:0.3px;margin:0;">
                {{ \App\Models\Setting::get('boutique.nom', config('boutique.nom')) }}
            </p>
        </div>
    </aside>

    {{-- ── Contenu ───────────────────────────────────────────── --}}
    <main class="app-content">
        <div class="app-content-inner">
            {{ $slot }}
        </div>
    </main>
</div>

<x-mary-toast />
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
@stack('scripts')
@livewireScripts

<script>
// ── Nom utilisateur responsive ─────────────────────────────
(function() {
    const nameEl = document.querySelector('.md-name');
    if (nameEl) {
        function updateName() {
            nameEl.style.display = window.innerWidth >= 768 ? 'inline' : 'none';
        }
        updateName();
        window.addEventListener('resize', updateName, { passive: true });
    }
})();

// ── User dropdown ──────────────────────────────────────────
function toggleUserMenu(e) {
    e.stopPropagation();
    const dd = document.getElementById('userDropdown');
    const icon = document.getElementById('chevronIcon');
    const isOpen = dd.classList.toggle('open');
    icon.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0)';
}
document.addEventListener('click', function(e) {
    const dd = document.getElementById('userDropdown');
    if (dd && !dd.contains(e.target)) {
        dd.classList.remove('open');
        const icon = document.getElementById('chevronIcon');
        if (icon) icon.style.transform = 'rotate(0)';
    }
});

// ── Navbar shadow on scroll ────────────────────────────────
window.addEventListener('scroll', function() {
    const nav = document.getElementById('appNav');
    if (nav) nav.classList.toggle('scrolled', window.scrollY > 4);
}, { passive: true });

// ── Mobile sidebar ─────────────────────────────────────────
function closeSidebar() {
    document.getElementById('appSidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('active');
}

const sidebarToggle = document.getElementById('sidebarToggle');
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const isOpen = sidebar.classList.toggle('open');
        overlay.classList.toggle('active', isOpen);
    });
}

// Fermer sidebar au clic sur un lien (mobile)
document.querySelectorAll('.sidebar-item').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 768) closeSidebar();
    });
});

// ── Page transition ────────────────────────────────────────
document.addEventListener('livewire:navigating', function() {
    const overlay = document.getElementById('pageTransition');
    if (overlay) overlay.classList.add('active');
});
document.addEventListener('livewire:navigated', function() {
    const overlay = document.getElementById('pageTransition');
    if (overlay) overlay.classList.remove('active');
});
</script>
</body>
</html>
