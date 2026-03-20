<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Erreur serveur</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Geist+Mono:wght@300;400;500&family=Geist:wght@300;400;500;600&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #000000;
            --bg2:       #0a0a0a;
            --border:    rgba(255,255,255,0.08);
            --border2:   rgba(255,255,255,0.14);
            --text1:     #ffffff;
            --text2:     rgba(255,255,255,0.5);
            --text3:     rgba(255,255,255,0.25);
            --accent:    #ffffff;
        }

        html, body {
            height: 100%;
            background: var(--bg);
            color: var(--text1);
            font-family: 'Geist', -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none; z-index: 0; opacity: .6;
        }

        .grid {
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none; z-index: 0;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 0%, transparent 100%);
        }

        .page {
            position: relative; z-index: 1;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .separator {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 40px;
            animation: fadeUp .5s cubic-bezier(.22,1,.36,1) both;
        }

        .sep-line {
            width: 1px;
            height: 40px;
            background: var(--border2);
        }

        .sep-code {
            font-family: 'Geist Mono', monospace;
            font-size: 13px;
            font-weight: 400;
            color: var(--text3);
            letter-spacing: .08em;
        }

        .block {
            display: flex;
            align-items: center;
            gap: 32px;
            animation: fadeUp .5s cubic-bezier(.22,1,.36,1) .06s both;
        }

        .divider {
            width: 1px;
            height: 56px;
            background: var(--border2);
            flex-shrink: 0;
        }

        .code {
            font-family: 'Geist Mono', monospace;
            font-size: clamp(1.1rem, 3vw, 1.35rem);
            font-weight: 500;
            color: var(--text1);
            letter-spacing: -.01em;
            white-space: nowrap;
        }

        .message {
            font-size: clamp(.9rem, 2vw, 1rem);
            font-weight: 400;
            color: var(--text2);
            letter-spacing: -.01em;
            white-space: nowrap;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 40px;
            animation: fadeUp .5s cubic-bezier(.22,1,.36,1) .12s both;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            height: 36px;
            padding: 0 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Geist', sans-serif;
            text-decoration: none;
            letter-spacing: -.01em;
            transition: opacity .15s ease, background .15s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: #ffffff;
            color: #000000;
        }
        .btn-primary:hover { opacity: .88; }

        .btn-ghost {
            background: transparent;
            color: var(--text2);
            border: 1px solid var(--border2);
        }
        .btn-ghost:hover {
            background: rgba(255,255,255,0.05);
            color: var(--text1);
            border-color: rgba(255,255,255,0.2);
        }

        footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-top: 1px solid var(--border);
            animation: fadeUp .4s ease .2s both;
        }

        .footer-inner {
            font-family: 'Geist Mono', monospace;
            font-size: 11px;
            color: var(--text3);
            letter-spacing: .04em;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .block { flex-direction: column; gap: 16px; text-align: center; }
            .divider { width: 40px; height: 1px; }
            .actions { flex-direction: column; width: 100%; max-width: 280px; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="grid"></div>

    <div class="page">

        <div class="separator">
            <div class="sep-line"></div>
            <span class="sep-code">ERR_INTERNAL_SERVER</span>
            <div class="sep-line"></div>
        </div>

        <div class="block">
            <span class="code">500</span>
            <div class="divider"></div>
            <span class="message">Une erreur interne s'est produite.</span>
        </div>

        <div class="actions">
            <a href="{{ url('/') }}" class="btn btn-primary">
                Retour à l'accueil
            </a>
            <a href="javascript:location.reload()" class="btn btn-ghost">
                Réessayer
            </a>
        </div>

    </div>

    <footer>
        <span class="footer-inner">
            {{ config('app.name') }} &mdash; <a href="{{ url('/') }}">{{ parse_url(url('/'), PHP_URL_HOST) }}</a>
        </span>
    </footer>
</body>
</html>
