<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ Setting::get('boutique.nom', config('boutique.nom')) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,300;12..96,400;12..96,600;12..96,700;12..96,800&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #060608;
            --bg2:     #0d0d12;
            --border:  rgba(255,255,255,0.07);
            --border2: rgba(255,255,255,0.12);
            --text1:   #ffffff;
            --text2:   rgba(255,255,255,0.55);
            --text3:   rgba(255,255,255,0.28);
        }

        html { min-height: 100%; background: var(--bg); }

        body {
            height: 100vh;
            overflow: hidden;
            background: var(--bg);
            font-family: 'Inter', sans-serif;
            color: var(--text1);
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Grid ─── */
        .grid-bg {
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.022) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.022) 1px, transparent 1px);
            background-size: 64px 64px;
            pointer-events: none; z-index: 0;
        }

        /* ─── Orbs ─── */
        .orb { position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; }
        .orb-1 {
            width: 700px; height: 700px;
            top: -200px; left: 50%; transform: translateX(-50%);
            background: radial-gradient(circle at center, rgba(120,100,255,0.12) 0%, transparent 65%);
            animation: orb-drift-1 14s ease-in-out infinite alternate;
        }
        .orb-2 {
            width: 500px; height: 500px;
            bottom: 0; right: -100px;
            background: radial-gradient(circle at center, rgba(40,180,160,0.09) 0%, transparent 65%);
            animation: orb-drift-2 18s ease-in-out infinite alternate;
        }
        @keyframes orb-drift-1 {
            from { transform: translateX(-50%) translateY(0) scale(1); }
            to   { transform: translateX(-50%) translateY(40px) scale(1.06); }
        }
        @keyframes orb-drift-2 {
            from { transform: translateY(0) scale(1); }
            to   { transform: translateY(-60px) scale(1.1); }
        }

        /* ─── BONUS : floating particles ─── */
        .particles {
            position: fixed; inset: 0;
            pointer-events: none; z-index: 0;
            overflow: hidden;
        }
        .particle {
            position: absolute;
            width: 2px; height: 2px;
            border-radius: 50%;
            background: rgba(255,255,255,0.35);
            animation: float-up linear infinite;
        }
        .particle:nth-child(1)  { left:8%;   animation-duration:12s; animation-delay:0s;    width:1px; height:1px; }
        .particle:nth-child(2)  { left:18%;  animation-duration:16s; animation-delay:-4s;   width:2px; height:2px; opacity:.5; }
        .particle:nth-child(3)  { left:28%;  animation-duration:10s; animation-delay:-1s;   width:1px; height:1px; opacity:.3; }
        .particle:nth-child(4)  { left:42%;  animation-duration:14s; animation-delay:-6s;   }
        .particle:nth-child(5)  { left:55%;  animation-duration:11s; animation-delay:-3s;   width:1px; height:1px; opacity:.4; }
        .particle:nth-child(6)  { left:65%;  animation-duration:17s; animation-delay:-8s;   width:2px; height:2px; opacity:.3; }
        .particle:nth-child(7)  { left:75%;  animation-duration:13s; animation-delay:-2s;   }
        .particle:nth-child(8)  { left:85%;  animation-duration:15s; animation-delay:-5s;   width:1px; height:1px; opacity:.5; }
        .particle:nth-child(9)  { left:92%;  animation-duration:9s;  animation-delay:-7s;   opacity:.2; }
        .particle:nth-child(10) { left:48%;  animation-duration:18s; animation-delay:-9s;   width:1px; height:1px; opacity:.4; }

        @keyframes float-up {
            0%   { transform: translateY(110vh) scale(1); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(-10vh) scale(0.5); opacity: 0; }
        }

        /* ─── Page ─── */
        .page {
            position: relative; z-index: 1;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─── Nav ─── */
        nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 2rem; height: 60px;
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px);
            background: rgba(6,6,8,0.7);
            animation: fadeDown .5s cubic-bezier(.22,1,.36,1) both;
            flex-shrink: 0;
        }

        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }

        .nav-logo {
            width: 30px; height: 30px; border-radius: 8px; background: #fff;
            display: flex; align-items: center; justify-content: center;
        }
        .nav-logo span {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 800; font-size: 12px; color: #060608; letter-spacing: -0.02em;
        }
        .nav-name {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 700; font-size: 15px; color: var(--text1); letter-spacing: -0.02em;
        }
        .nav-badge {
            font-size: 11px; font-weight: 400; color: var(--text3);
            border: 1px solid var(--border2); padding: 2px 8px; border-radius: 100px;
        }

        /* ─── Hero : flex:1 pour occuper tout l'espace restant ─── */
        .hero {
            flex: 1;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center;
            padding: 0 2rem;
            gap: 0;
        }

        /* ─── Logo pill ─── */
        .hero-logo-pill {
            display: inline-flex; align-items: center; gap: 10px;
            border: 1px solid var(--border2);
            background: rgba(255,255,255,0.04);
            padding: 6px 16px 6px 8px;
            border-radius: 100px;
            margin-bottom: 2rem;
            animation: fadeUp .6s cubic-bezier(.22,1,.36,1) .05s both;
        }
        .hero-logo-icon {
            width: 26px; height: 26px; border-radius: 6px; background: #fff;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .hero-logo-icon span {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 800; font-size: 10px; color: #060608; letter-spacing: -0.02em;
        }
        .hero-logo-name {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 600; font-size: 13px; color: var(--text2); letter-spacing: -0.01em;
        }
        .hero-logo-dot {
            width: 5px; height: 5px; border-radius: 50%; background: #4ade80;
            box-shadow: 0 0 0 2px rgba(74,222,128,0.2);
            animation: pulse-dot 2.5s ease-in-out infinite; flex-shrink: 0;
        }
        @keyframes pulse-dot {
            0%,100% { box-shadow: 0 0 0 2px rgba(74,222,128,0.2); }
            50%      { box-shadow: 0 0 0 4px rgba(74,222,128,0.12); }
        }

        .hero-title {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-size: clamp(2.4rem, 5vw, 4.8rem);
            font-weight: 800; line-height: 1.0; letter-spacing: -0.04em;
            color: var(--text1); margin-bottom: 1.2rem;
            animation: fadeUp .6s cubic-bezier(.22,1,.36,1) .12s both;
        }
        .hero-title .dim { color: rgba(255,255,255,0.28); }

        .hero-sub {
            font-size: 15px; font-weight: 300; line-height: 1.65;
            color: var(--text2); max-width: 460px;
            margin: 0 auto 2.2rem;
            animation: fadeUp .6s cubic-bezier(.22,1,.36,1) .2s both;
        }

        /* ─── CTA ─── */
        .cta-group {
            display: flex; align-items: center; gap: 12px;
            flex-wrap: wrap; justify-content: center;
            margin-bottom: 3rem;
            animation: fadeUp .6s cubic-bezier(.22,1,.36,1) .28s both;
        }

        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            height: 42px; padding: 0 22px;
            background: #ffffff; color: #060608;
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 700; font-size: 14px; letter-spacing: -0.01em;
            border-radius: 10px; text-decoration: none; border: none; cursor: pointer;
            position: relative; overflow: hidden;
            transition: transform .15s cubic-bezier(.34,1.56,.64,1), box-shadow .2s ease;
        }
        .btn-primary::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(110deg, transparent 35%, rgba(0,0,0,0.06) 50%, transparent 65%);
            transform: translateX(-100%); transition: transform .55s ease;
        }
        .btn-primary:hover::before { transform: translateX(100%); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,255,255,0.15); }
        .btn-primary:active { transform: translateY(0) scale(0.98); }
        .btn-primary svg { width: 15px; height: 15px; transition: transform .2s ease; }
        .btn-primary:hover svg { transform: translateX(2px); }

        .btn-outline {
            display: inline-flex; align-items: center; gap: 6px;
            height: 42px; padding: 0 18px;
            background: transparent !important;
            color: rgba(255,255,255,0.55) !important;
            font-size: 14px; font-weight: 400; border-radius: 10px;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.12) !important;
            transition: color .15s ease, border-color .15s ease, background .15s ease;
        }
        .btn-outline:hover {
            color: #ffffff !important;
            border-color: rgba(255,255,255,0.2) !important;
            background: rgba(255,255,255,0.04) !important;
        }

        /* ─── Features ─── */
        .features {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1px;
            border: 1px solid var(--border);
            border-radius: 14px; overflow: hidden;
            width: 100%; max-width: 860px;
            animation: fadeUp .6s cubic-bezier(.22,1,.36,1) .36s both;
        }

        .feat {
            padding: 20px 22px 18px;
            background: var(--bg2);
            transition: background .2s ease;
        }
        .feat:hover { background: rgba(255,255,255,0.03); }

        .feat-icon {
            width: 28px; height: 28px; border-radius: 7px;
            border: 1px solid var(--border2);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 10px;
            background: rgba(255,255,255,0.04);
        }
        .feat-icon svg { width: 13px; height: 13px; color: var(--text2); }

        .feat-title {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 700; font-size: 13px; color: var(--text1);
            letter-spacing: -0.02em; margin-bottom: 5px;
        }
        .feat-desc { font-size: 12px; font-weight: 300; color: var(--text3); line-height: 1.5; }

        /* ─── Footer ─── */
        footer {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 2rem; height: 48px;
            font-size: 11px; color: var(--text3);
            border-top: 1px solid var(--border);
            flex-shrink: 0;
            animation: fadeUp .5s ease .5s both;
        }
        footer a {
            color: var(--text3); text-decoration: none;
            transition: color .15s ease;
        }
        footer a:hover { color: var(--text2); }

        /* ─── Animations ─── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="grid-bg"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="particles">
        <div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div>
    </div>

    <div class="page">

        <nav>
            <a href="#" class="nav-brand">
                <div class="nav-logo">
                    <span>{{ strtoupper(substr(Setting::get('boutique.nom', config('boutique.nom')), 0, 2)) }}</span>
                </div>
                <span class="nav-name">{{ Setting::get('boutique.nom', config('boutique.nom')) }}</span>
            </a>
            <span class="nav-badge">v{{ config('app.version', '1.0') }}</span>
        </nav>

        <main class="hero">

            <h1 class="hero-title">
                Gérez votre stock<br>
                <span class="dim">sans friction.</span>
            </h1>

            <p class="hero-sub">
                Commandes, inventaire, revendeurs et paiements —
                tout centralisé dans un tableau de bord pensé pour la performance.
            </p>

            <div class="cta-group">
                @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">
                    Accéder à mon tableau de bord
                    <svg viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                @else
                <a href="{{ route('login') }}" class="btn-primary">
                    Accéder à mon tableau de bord
                    <svg viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                <a href="https://chrislain-portfolio.vercel.app/contact" class="btn-outline" target="_blank" rel="noopener">En savoir plus</a>
                @endauth
            </div>

            <div class="features">
                <div class="feat">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 3H8l-2 4h12l-2-4z"/></svg>
                    </div>
                    <p class="feat-title">Stock en temps réel</p>
                    <p class="feat-desc">Suivez chaque unité avec IMEI, état et localisation.</p>
                </div>
                <div class="feat">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <p class="feat-title">Analytics avancés</p>
                    <p class="feat-desc">CA, marges, ventes par période et vendeur.</p>
                </div>
                <div class="feat">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <p class="feat-title">Revendeurs & crédits</p>
                    <p class="feat-desc">Dettes, acomptes et historiques de paiement.</p>
                </div>
                <div class="feat">
                    <div class="feat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <p class="feat-title">Accès par rôle</p>
                    <p class="feat-desc">Admin et vendeur avec permissions granulaires.</p>
                </div>
            </div>

        </main>

        <footer>
            <span>{{ Setting::get('boutique.nom', config('boutique.nom')) }} &copy; {{ date('Y') }}</span>
            <span>
                Conçu & développé par
                <a href="https://chrislain-portfolio.vercel.app" target="_blank" rel="noopener">Chrislain Avocegan</a>
            </span>
        </footer>

    </div>

</body>
</html>
