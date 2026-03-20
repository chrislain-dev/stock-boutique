@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=Inter:wght@300;400;500&display=swap');

    * { box-sizing: border-box; margin: 0; padding: 0; }

    .login-root {
        min-height: 100vh;
        display: flex;
        font-family: 'Inter', sans-serif;
        background: #0a0a0f;
    }

    /* ─── LEFT PANEL ─── */
    .login-left {
        flex: 0 0 45%;
        min-width: 420px;
        max-width: 560px;
        background: #fafaf8;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 3rem 4rem;
        position: relative;
        z-index: 2;
    }

    .login-left::after {
        content: '';
        position: absolute;
        right: -1px; top: 0; bottom: 0; width: 1px;
        background: linear-gradient(to bottom, transparent, #e0ddd6 20%, #e0ddd6 80%, transparent);
    }

    /* ─── ANIMATIONS ─── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .brand-mark    { animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both; animation-delay: 0.05s; }
    .login-heading { animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both; animation-delay: 0.14s; }
    .form-wrapper  { animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both; animation-delay: 0.22s; }
    .login-footer  { animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both; animation-delay: 0.30s; }

    /* ─── BRAND ─── */
    .brand-mark {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 2.5rem; align-self: flex-start;
    }
    .brand-icon {
        width: 40px; height: 40px; border-radius: 10px;
        background: #0a0a0f;
        display: flex; align-items: center; justify-content: center;
    }
    .brand-icon span {
        color: #fafaf8; font-family: 'Bricolage Grotesque', sans-serif;
        font-weight: 700; font-size: 14px; letter-spacing: 0.05em;
    }
    .brand-name {
        font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700;
        font-size: 18px; color: #0a0a0f; letter-spacing: -0.02em;
    }

    /* ─── HEADING ─── */
    .login-heading { align-self: flex-start; margin-bottom: 2rem; }
    .login-heading h1 {
        font-family: 'Bricolage Grotesque', sans-serif; font-size: 2rem; font-weight: 800;
        color: #0a0a0f; letter-spacing: -0.04em; line-height: 1.1;
    }
    .login-heading p {
        font-size: 14px; color: #8a8882; margin-top: 6px;
        font-weight: 300; letter-spacing: 0.01em;
    }

    /* ─── FORM ─── */
    .form-wrapper { width: 100%; }

    .form-wrapper label,
    .form-wrapper .label-text {
        font-size: 11.5px !important;
        font-weight: 500 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        color: #6b6b6b !important;
    }

    /* Checkbox label — annuler uppercase */
    .form-wrapper .cursor-pointer .label-text {
        font-size: 13px !important;
        font-weight: 400 !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
    }

    /* Inputs Vercel */
    .form-wrapper input[type="email"],
    .form-wrapper input[type="password"],
    .form-wrapper input[type="text"],
    .form-wrapper .input {
        height: 38px !important;
        background: #ffffff !important;
        border: 1px solid #d9d9d9 !important;
        border-radius: 6px !important;
        font-family: 'Inter', sans-serif !important;
        font-size: 13.5px !important;
        color: #0a0a0f !important;
        outline: none !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }

    .form-wrapper input::placeholder { color: #b0aca7 !important; }
    .form-wrapper input:hover:not(:focus) { border-color: #b3b3b3 !important; }
    .form-wrapper input:focus {
        border-color: #0a0a0f !important;
        box-shadow: 0 0 0 1px #0a0a0f, 0 0 0 4px rgba(10,10,15,0.07) !important;
    }

    .form-wrapper input[type="checkbox"] {
        width: 15px !important; height: 15px !important;
        accent-color: #0a0a0f; cursor: pointer;
    }

    .form-wrapper .label-text-alt {
        font-size: 12px !important; color: #dc2626 !important;
        text-transform: none !important; letter-spacing: 0 !important;
    }

    /* Bouton — override DaisyUI */
    .form-wrapper .btn-primary {
        height: 38px !important;
        min-height: 38px !important;
        width: 100% !important;
        border-radius: 6px !important;
        background: #0a0a0f !important;
        border-color: #0a0a0f !important;
        color: #fafaf8 !important;
        font-family: 'Bricolage Grotesque', sans-serif !important;
        font-weight: 600 !important;
        font-size: 13.5px !important;
        letter-spacing: 0.03em !important;
        position: relative !important;
        overflow: hidden !important;
        transition: background 0.18s ease, transform 0.14s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.18s ease !important;
    }
    .form-wrapper .btn-primary:hover {
        background: #1a1a2e !important;
        border-color: #1a1a2e !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 14px rgba(10,10,15,0.25) !important;
    }
    .form-wrapper .btn-primary:active {
        transform: translateY(0) scale(0.99) !important;
        box-shadow: none !important;
    }

    /* ─── FOOTER ─── */
    .login-footer {
        margin-top: 2rem; font-size: 11px;
        color: #b8b4ae; letter-spacing: 0.04em; align-self: flex-start;
    }

    /* ─── RIGHT PANEL ─── */
    .login-right {
        flex: 1; background: #0a0a0f;
        position: relative; overflow: hidden;
        display: flex; flex-direction: column;
        justify-content: flex-end; padding: 3.5rem;
    }

    .grid-overlay {
        position: absolute; inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
        background-size: 48px 48px;
    }

    .orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.35; animation: drift 12s ease-in-out infinite alternate; }
    .orb-1 { width:420px;height:420px;background:radial-gradient(circle,#4f46e5,transparent 70%);top:-120px;right:-80px; }
    .orb-2 { width:320px;height:320px;background:radial-gradient(circle,#06b6d4,transparent 70%);bottom:80px;left:-60px;animation-delay:-4s; }
    .orb-3 { width:200px;height:200px;background:radial-gradient(circle,#a855f7,transparent 70%);top:40%;left:40%;animation-delay:-8s; }

    @keyframes drift { from{transform:translate(0,0) scale(1);}to{transform:translate(30px,20px) scale(1.08);} }

    .geo-shapes { position:absolute;inset:0;pointer-events:none; }
    .geo-ring { position:absolute;border-radius:50%;border:1px solid rgba(255,255,255,0.08);animation:rotate-slow linear infinite; }
    .ring-1{width:380px;height:380px;top:5%;right:5%;animation-duration:30s;}
    .ring-2{width:240px;height:240px;top:12%;right:12%;animation-duration:20s;animation-direction:reverse;border-style:dashed;}
    .ring-3{width:160px;height:160px;top:19%;right:19%;animation-duration:15s;}
    @keyframes rotate-slow{from{transform:rotate(0deg);}to{transform:rotate(360deg);}}

    .geo-diamond{position:absolute;width:60px;height:60px;border:1px solid rgba(255,255,255,0.12);transform:rotate(45deg);top:55%;left:20%;animation:float-y 6s ease-in-out infinite;}
    .geo-diamond-sm{position:absolute;width:28px;height:28px;border:1px solid rgba(79,70,229,0.5);transform:rotate(45deg);top:35%;left:55%;animation:float-y 8s ease-in-out infinite;animation-delay:-3s;}
    .geo-cross{position:absolute;top:70%;left:70%;opacity:0.2;animation:float-y 7s ease-in-out infinite;animation-delay:-5s;}
    @keyframes float-y{0%,100%{transform:rotate(45deg) translateY(0);}50%{transform:rotate(45deg) translateY(-12px);}}

    .center-visual{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);}
    .hex-container{position:relative;width:220px;height:220px;}
    .hex-ring{position:absolute;inset:0;border-radius:50%;animation:spin-slow linear infinite;}
    .hex-ring svg{width:100%;height:100%;}
    @keyframes spin-slow{from{transform:rotate(0deg);}to{transform:rotate(360deg);}}

    .hex-inner{position:absolute;inset:40px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);}
    .hex-dot{width:8px;height:8px;background:#fff;border-radius:50%;box-shadow:0 0 20px 4px rgba(255,255,255,0.4);animation:pulse 2s ease-in-out infinite;}
    @keyframes pulse{0%,100%{opacity:1;transform:scale(1);}50%{opacity:0.5;transform:scale(0.7);}}

    .right-content{position:relative;z-index:2;}
    .tag-line{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:100px;padding:4px 12px 4px 6px;margin-bottom:1.25rem;}
    .tag-dot{width:6px;height:6px;background:#4ade80;border-radius:50%;box-shadow:0 0 8px 2px rgba(74,222,128,0.5);animation:blink 2s ease-in-out infinite;}
    @keyframes blink{0%,100%{opacity:1;}50%{opacity:0.4;}}
    .tag-line span{font-size:11px;color:rgba(255,255,255,0.6);letter-spacing:0.08em;text-transform:uppercase;font-weight:500;}
    .right-headline{font-family:'Bricolage Grotesque',sans-serif;font-size:2.8rem;font-weight:800;color:#fff;line-height:1.05;letter-spacing:-0.04em;margin-bottom:1rem;}
    .right-headline em{font-style:normal;background:linear-gradient(135deg,#818cf8,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .right-desc{font-size:14px;color:rgba(255,255,255,0.45);line-height:1.7;max-width:380px;font-weight:300;}
    .stats-row{display:flex;gap:2rem;margin-top:2rem;padding-top:2rem;border-top:1px solid rgba(255,255,255,0.07);}
    .stat-number{font-family:'Bricolage Grotesque',sans-serif;font-size:1.5rem;font-weight:800;color:#fff;letter-spacing:-0.04em;}
    .stat-label{font-size:11px;color:rgba(255,255,255,0.35);letter-spacing:0.06em;text-transform:uppercase;margin-top:2px;}

    @media (max-width:900px){
        .login-root{flex-direction:column;}
        .login-left{flex:none;width:100%;min-height:100vh;padding:2.5rem 2rem;min-width:unset;}
        .login-right{display:none;}
    }
</style>
@endpush

{{-- ✅ Élément racine UNIQUE — obligatoire pour Livewire --}}
<div class="login-root">

    {{-- ─── LEFT: FORM PANEL ─── --}}
    <div class="login-left">

        <div class="brand-mark">
            <div class="brand-icon">
                <span>{{ strtoupper(substr(Setting::get('boutique.nom', config('boutique.nom')), 0, 2)) }}</span>
            </div>
            <span class="brand-name">{{ Setting::get('boutique.nom', config('boutique.nom')) }}</span>
        </div>

        <div class="login-heading">
            <h1>Bienvenue<br>de retour.</h1>
            <p>Votre espace de gestion vous attend</p>
        </div>

        <div class="form-wrapper">
            <x-mary-form wire:submit="login">

                <x-mary-input
                    label="Adresse email"
                    wire:model="email"
                    type="email"
                    placeholder="admin@techshop.bj"
                    icon="o-envelope"
                    autofocus
                />

                <x-mary-input
                    label="Mot de passe"
                    wire:model="password"
                    type="password"
                    placeholder="••••••••"
                    icon="o-lock-closed"
                />

                <x-mary-checkbox
                    label="Se souvenir de moi"
                    wire:model="remember"
                />

                <x-slot:actions>
                    <x-mary-button
                        label="Se connecter"
                        type="submit"
                        class="btn-primary w-full"
                        spinner="login"
                    />
                </x-slot:actions>

            </x-mary-form>
        </div>

        <p class="login-footer">
            {{ Setting::get('boutique.nom', config('boutique.nom')) }} &copy; {{ date('Y') }} — Tous droits réservés
        </p>

    </div>

    {{-- ─── RIGHT: DESIGN PANEL ─── --}}
    <div class="login-right">

        <div class="grid-overlay"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <div class="geo-shapes">
            <div class="geo-ring ring-1"></div>
            <div class="geo-ring ring-2"></div>
            <div class="geo-ring ring-3"></div>
            <div class="geo-diamond"></div>
            <div class="geo-diamond-sm"></div>
            <svg class="geo-cross" width="32" height="32" viewBox="0 0 32 32" fill="none">
                <path d="M16 4v24M4 16h24" stroke="white" stroke-width="1" stroke-linecap="round"/>
            </svg>
        </div>

        <div class="center-visual">
            <div class="hex-container">
                <div class="hex-ring" style="animation-duration:16s;">
                    <svg viewBox="0 0 220 220" fill="none">
                        <circle cx="110" cy="110" r="106" stroke="rgba(255,255,255,0.08)" stroke-width="1" stroke-dasharray="8 6"/>
                        <circle cx="110" cy="4"   r="3" fill="rgba(79,70,229,0.8)"/>
                        <circle cx="216" cy="110" r="3" fill="rgba(6,182,212,0.8)"/>
                        <circle cx="110" cy="216" r="3" fill="rgba(168,85,247,0.8)"/>
                        <circle cx="4"   cy="110" r="3" fill="rgba(255,255,255,0.4)"/>
                    </svg>
                </div>
                <div class="hex-ring" style="animation-duration:24s;animation-direction:reverse;inset:20px;">
                    <svg viewBox="0 0 180 180" fill="none">
                        <path d="M90 10 L163 50 L163 130 L90 170 L17 130 L17 50 Z" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
                    </svg>
                </div>
                <div class="hex-inner">
                    <div class="hex-dot"></div>
                </div>
            </div>
        </div>

        <div class="right-content">
            <div class="tag-line">
                <div class="tag-dot"></div>
                <span>Système opérationnel</span>
            </div>
            <h2 class="right-headline">
                Gérez votre<br>boutique <em>sans<br>friction.</em>
            </h2>
            <p class="right-desc">
                Une interface pensée pour la performance. Commandes, stocks, clients —
                tout sous contrôle depuis un seul tableau de bord.
            </p>
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-number">99.9%</div>
                    <div class="stat-label">Disponibilité</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">&lt; 80ms</div>
                    <div class="stat-label">Temps de réponse</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">AES-256</div>
                    <div class="stat-label">Chiffrement</div>
                </div>
            </div>
        </div>

    </div>

</div>
