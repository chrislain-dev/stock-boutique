<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Reçu {{ $sale->reference }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Geist+Mono:wght@400;500&display=swap');

        :root {
            --primary: #18181b;
            --accent: {{ config('boutique.couleur_principale', '#18181b') }};
            --text: #18181b;
            --muted: #71717a;
            --border: #e4e4e7;
            --bg-subtle: #fafafa;
            --success: #16a34a;
            --warning: #d97706;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Geist', ui-sans-serif, system-ui, sans-serif;
            background: #f4f4f5;
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 32px 16px 48px;
        }

        /* ── Toolbar (screen only) ───────────────────────────── */
        .toolbar {
            width: 100%;
            max-width: 560px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .toolbar-left { display: flex; align-items: center; gap: 8px; }
        .toolbar-title { font-size: 13px; font-weight: 600; color: #3f3f46; }
        .toolbar-sub   { font-size: 12px; color: #a1a1aa; font-family: 'Geist Mono', monospace; }

        .btn-toolbar {
            display: inline-flex; align-items: center; gap-x: 6px; gap: 6px;
            height: 36px; padding: 0 16px; border-radius: 10px; font-size: 13px;
            font-weight: 500; cursor: pointer; transition: all .15s ease;
            text-decoration: none; border: none;
        }
        .btn-outline {
            background: #fff; border: 1.5px solid var(--border); color: #3f3f46;
        }
        .btn-outline:hover { background: #fafafa; border-color: #d4d4d8; }
        .btn-primary {
            background: var(--primary); color: #fff;
        }
        .btn-primary:hover { background: #27272a; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.15); }
        .btn-primary:active { transform: scale(.97); }

        /* ── Receipt paper ───────────────────────────────────── */
        .receipt {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 8px 40px rgba(0,0,0,.08);
            overflow: hidden;
            position: relative;
        }

        /* ── Header band ─────────────────────────────────────── */
        .receipt-header {
            background: var(--primary);
            padding: 28px 32px 24px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .receipt-header::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.07) 0%, transparent 60%);
        }
        .receipt-header::after {
            content: '';
            position: absolute; bottom: -1px; left: 0; right: 0;
            height: 20px;
            background: #fff;
            clip-path: ellipse(55% 100% at 50% 100%);
        }

        .shop-logo-area {
            display: flex; align-items: center; gap: 14px; margin-bottom: 16px;
        }
        .shop-logo-placeholder {
            width: 48px; height: 48px; border-radius: 12px;
            background: rgba(255,255,255,.15);
            border: 1.5px solid rgba(255,255,255,.25);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 700; color: #fff;
            letter-spacing: -.02em; flex-shrink: 0;
        }
        .shop-name {
            font-size: 20px; font-weight: 700; letter-spacing: -.03em; color: #fff;
        }
        .shop-sub { font-size: 12px; color: rgba(255,255,255,.6); margin-top: 2px; }

        .receipt-ref-band {
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 10px; padding: 10px 14px; margin-top: 4px;
        }
        .ref-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: rgba(255,255,255,.5); }
        .ref-value { font-size: 14px; font-weight: 700; color: #fff; font-family: 'Geist Mono', monospace; letter-spacing: .02em; }
        .ref-date  { font-size: 12px; color: rgba(255,255,255,.7); text-align: right; }
        .ref-time  { font-size: 10px; color: rgba(255,255,255,.45); text-align: right; margin-top: 1px; }

        /* ── Body ────────────────────────────────────────────── */
        .receipt-body { padding: 24px 32px; }

        .section { margin-bottom: 20px; }
        .section-label {
            font-size: 9px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .1em; color: #a1a1aa; margin-bottom: 10px;
        }

        /* Client info */
        .client-row {
            display: flex; align-items: center; gap: 12px;
            background: var(--bg-subtle); border: 1px solid var(--border);
            border-radius: 10px; padding: 12px 14px;
        }
        .client-avatar {
            width: 36px; height: 36px; border-radius: 9px;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; flex-shrink: 0;
        }
        .client-name  { font-size: 14px; font-weight: 600; color: var(--text); }
        .client-phone { font-size: 12px; color: var(--muted); margin-top: 1px; font-family: 'Geist Mono', monospace; }
        .client-badge {
            margin-left: auto; font-size: 10px; font-weight: 600;
            background: #ede9fe; color: #6d28d9;
            padding: 3px 8px; border-radius: 6px;
        }

        /* Articles */
        .items-table { width: 100%; border-collapse: collapse; }
        .items-header th {
            font-size: 9px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .08em; color: #a1a1aa;
            padding: 0 0 8px; text-align: left;
        }
        .items-header th:last-child { text-align: right; }

        .item-row { border-top: 1px solid #f4f4f5; }
        .item-row td { padding: 10px 0; vertical-align: top; }
        .item-name { font-size: 13px; font-weight: 500; color: var(--text); line-height: 1.3; }
        .item-id {
            font-size: 11px; color: var(--muted); font-family: 'Geist Mono', monospace;
            margin-top: 2px; letter-spacing: .02em;
        }
        .item-price-info { font-size: 11px; color: var(--muted); margin-top: 2px; }
        .item-discount {
            display: inline-flex; align-items: center;
            font-size: 10px; font-weight: 600;
            background: #fef9c3; color: #92400e;
            padding: 1px 6px; border-radius: 4px; margin-left: 4px;
        }
        .item-total {
            font-size: 14px; font-weight: 700; color: var(--text);
            text-align: right; white-space: nowrap;
        }

        /* Troc row */
        .tradein-row { background: #fffbeb; }
        .tradein-row td { padding: 10px 12px; border-radius: 8px; }

        /* Separator */
        .sep {
            height: 1px; background: var(--border); margin: 16px 0;
        }
        .sep-dashed {
            height: 0; border-top: 1.5px dashed #e4e4e7; margin: 16px 0;
        }

        /* Totals */
        .totals-section { background: var(--bg-subtle); border-radius: 10px; padding: 14px 16px; }
        .total-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 5px 0;
        }
        .total-row + .total-row { border-top: 1px solid #f0f0f0; }
        .total-label { font-size: 12px; color: var(--muted); }
        .total-value { font-size: 13px; font-weight: 600; color: var(--text); }
        .total-row.main .total-label { font-size: 13px; font-weight: 700; color: var(--text); }
        .total-row.main .total-value { font-size: 18px; font-weight: 800; letter-spacing: -.02em; color: var(--text); }
        .total-row.paid .total-value  { color: var(--success); }
        .total-row.due  .total-label  { color: var(--warning); font-weight: 600; }
        .total-row.due  .total-value  { color: var(--warning); }

        /* Payments */
        .payment-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 8px 0; border-top: 1px solid #f4f4f5;
        }
        .payment-item:first-child { border-top: none; }
        .payment-method { font-size: 12px; font-weight: 500; color: var(--text); }
        .payment-ref    { font-size: 11px; color: var(--muted); font-family: 'Geist Mono', monospace; }
        .payment-amount { font-size: 13px; font-weight: 600; color: var(--success); }

        /* Status stamp */
        .status-stamp {
            text-align: center; margin: 20px 0 4px;
        }
        .stamp-paid {
            display: inline-flex; align-items: center; gap: 8px;
            background: #f0fdf4; border: 1.5px solid #bbf7d0;
            color: var(--success); font-size: 12px; font-weight: 700;
            padding: 8px 20px; border-radius: 40px;
            text-transform: uppercase; letter-spacing: .06em;
        }
        .stamp-pending {
            display: inline-flex; align-items: center; gap: 8px;
            background: #fffbeb; border: 1.5px solid #fde68a;
            color: var(--warning); font-size: 12px; font-weight: 700;
            padding: 8px 20px; border-radius: 40px;
            text-transform: uppercase; letter-spacing: .06em;
        }

        /* Footer */
        .receipt-footer {
            background: var(--bg-subtle);
            border-top: 1px solid var(--border);
            padding: 20px 32px;
            text-align: center;
        }
        .footer-message { font-size: 13px; color: var(--muted); margin-bottom: 8px; }
        .footer-contacts { font-size: 11px; color: #a1a1aa; line-height: 1.6; }

        /* Tear line */
        .tear-line {
            display: flex; align-items: center; gap: 8px;
            padding: 0 32px; margin: 0 0 0;
            position: relative;
        }
        .tear-circle {
            width: 14px; height: 14px; border-radius: 50%;
            background: #f4f4f5; flex-shrink: 0;
            margin-top: -7px;
        }
        .tear-dash {
            flex: 1; height: 0;
            border-top: 1.5px dashed #e4e4e7;
        }

        /* ── Print styles ────────────────────────────────────── */
        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
            }

            body {
                background: #fff !important;
                padding: 0 !important;
                display: block;
            }

            .toolbar { display: none !important; }

            .receipt {
                max-width: 100% !important;
                width: 100% !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .receipt-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .totals-section, .client-row { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .stamp-paid, .stamp-pending  { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    {{-- ── Toolbar (screen only) ──────────────────────────────────────── --}}
    <div class="toolbar">
        <div class="toolbar-left">
            <div>
                <div class="toolbar-title">Reçu de vente</div>
                <div class="toolbar-sub">{{ $sale->reference }}</div>
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('sales.show', $sale->id) }}" class="btn-toolbar btn-outline">
                ← Retour
            </a>
            <button onclick="downloadPdf()" class="btn-toolbar btn-outline" title="Télécharger en PDF">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                </svg>
                Télécharger PDF
            </button>
            <button onclick="window.print()" class="btn-toolbar btn-primary">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                </svg>
                Imprimer
            </button>
        </div>
    </div>

    {{-- ── Receipt ─────────────────────────────────────────────────────── --}}
    <div class="receipt" id="receipt-content">

        {{-- Header --}}
        <div class="receipt-header">
            <div class="shop-logo-area">
                @if(config('boutique.logo_url'))
                <img src="{{ config('boutique.logo_url') }}" alt="Logo"
                     style="width:48px; height:48px; border-radius:12px; object-fit:cover; border:1.5px solid rgba(255,255,255,.25);"/>
                @else
                <div class="shop-logo-placeholder">
                    {{ strtoupper(substr(config('boutique.nom', 'B'), 0, 1)) }}
                </div>
                @endif
                <div>
                    <div class="shop-name">{{ config('boutique.nom', 'Ma Boutique') }}</div>
                    @if(config('boutique.slogan'))
                    <div class="shop-sub">{{ config('boutique.slogan') }}</div>
                    @endif
                </div>
            </div>

            <div class="receipt-ref-band">
                <div>
                    <div class="ref-label">Référence</div>
                    <div class="ref-value">{{ $sale->reference }}</div>
                </div>
                <div>
                    <div class="ref-date">{{ $sale->created_at->format('d/m/Y') }}</div>
                    <div class="ref-time">{{ $sale->created_at->format('H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="receipt-body">

            {{-- Infos vente --}}
            <div class="section">
                <div class="section-label">Informations</div>
                <table style="width:100%; font-size:12px; border-collapse:collapse;">
                    <tr>
                        <td style="color:#a1a1aa; padding:3px 0; width:90px;">Vendeur</td>
                        <td style="font-weight:500; color:#18181b;">{{ $sale->createdBy?->name ?? '—' }}</td>
                    </tr>
                    @if(config('boutique.adresse'))
                    <tr>
                        <td style="color:#a1a1aa; padding:3px 0;">Adresse</td>
                        <td style="color:#3f3f46;">{{ config('boutique.adresse') }}</td>
                    </tr>
                    @endif
                    @if(config('boutique.telephone'))
                    <tr>
                        <td style="color:#a1a1aa; padding:3px 0;">Téléphone</td>
                        <td style="color:#3f3f46; font-family:'Geist Mono',monospace;">{{ config('boutique.telephone') }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            {{-- Client --}}
            <div class="section">
                <div class="section-label">Client</div>
                <div class="client-row">
                    <div class="client-avatar">
                        @if($sale->customer_type === 'reseller')
                            {{ strtoupper(substr($sale->reseller?->name ?? 'R', 0, 2)) }}
                        @else
                            {{ strtoupper(substr($sale->customer_name ?: 'A', 0, 2)) }}
                        @endif
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="client-name">
                            @if($sale->customer_type === 'reseller')
                                {{ $sale->reseller?->name ?? '—' }}
                            @else
                                {{ $sale->customer_name ?: 'Client anonyme' }}
                            @endif
                        </div>
                        @if($sale->customer_phone)
                        <div class="client-phone">{{ $sale->customer_phone }}</div>
                        @endif
                    </div>
                    @if($sale->customer_type === 'reseller')
                    <div class="client-badge">Revendeur</div>
                    @endif
                </div>
            </div>

            {{-- Articles --}}
            <div class="section">
                <div class="section-label">Articles ({{ $sale->items->count() }})</div>
                <table class="items-table">
                    <thead class="items-header">
                        <tr>
                            <th>Produit</th>
                            <th style="text-align:right; white-space:nowrap;">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr class="item-row">
                            <td>
                                <div class="item-name">{{ $item->productModel->display_label }}</div>
                                @if($item->product)
                                <div class="item-id">
                                    {{ $item->product->imei ? 'IMEI : ' . $item->product->imei : '' }}
                                    {{ $item->product->serial_number && !$item->product->imei ? 'S/N : ' . $item->product->serial_number : '' }}
                                </div>
                                @endif
                                <div class="item-price-info">
                                    {{ $item->quantity }} × {{ number_format($item->unit_price, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                                    @if($item->discount > 0)
                                    <span class="item-discount">−{{ number_format($item->discount, 0, ',', ' ') }} remise</span>
                                    @endif
                                </div>
                            </td>
                            <td class="item-total">
                                {{ number_format($item->line_total, 0, ',', ' ') }}
                                <span style="font-size:11px; font-weight:400; color:#a1a1aa;">{{ config('boutique.devise_symbole') }}</span>
                            </td>
                        </tr>
                        @endforeach

                        {{-- Troc --}}
                        @if($sale->is_trade_in && $sale->trade_in_value > 0)
                        <tr class="item-row tradein-row">
                            <td>
                                <div class="item-name" style="color:#92400e;">Troc déduit</div>
                                @if($sale->tradeInProduct)
                                <div class="item-id">
                                    {{ $sale->tradeInProduct->imei ?? $sale->tradeInProduct->serial_number ?? '—' }}
                                </div>
                                @endif
                                @if($sale->trade_in_notes)
                                <div class="item-price-info">{{ $sale->trade_in_notes }}</div>
                                @endif
                            </td>
                            <td class="item-total" style="color:#d97706;">
                                −{{ number_format($sale->trade_in_value, 0, ',', ' ') }}
                                <span style="font-size:11px; font-weight:400; color:#a1a1aa;">{{ config('boutique.devise_symbole') }}</span>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Totaux --}}
            <div class="section">
                <div class="totals-section">
                    <div class="total-row main">
                        <span class="total-label">Total net</span>
                        <span class="total-value">
                            {{ number_format($sale->total_amount, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    <div class="total-row paid">
                        <span class="total-label">Montant payé</span>
                        <span class="total-value">
                            {{ number_format($sale->paid_amount, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    @if($sale->remaining_amount > 0)
                    <div class="total-row due">
                        <span class="total-label">Reste dû</span>
                        <span class="total-value">
                            {{ number_format($sale->remaining_amount, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                        </span>
                    </div>
                    @if($sale->due_date)
                    <div class="total-row">
                        <span class="total-label">Échéance</span>
                        <span class="total-value" style="font-size:12px;">{{ $sale->due_date->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            {{-- Paiements --}}
            @if($sale->payments->count() > 0)
            <div class="section">
                <div class="section-label">Historique des paiements</div>
                @foreach($sale->payments as $payment)
                <div class="payment-item">
                    <div>
                        <div class="payment-method">{{ $payment->payment_method->label() }}</div>
                        <div class="payment-ref">
                            {{ $payment->payment_date->format('d/m/Y') }}
                            @if($payment->transaction_reference)
                                · {{ $payment->transaction_reference }}
                            @endif
                        </div>
                    </div>
                    <div class="payment-amount">
                        +{{ number_format($payment->amount, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Status stamp --}}
            <div class="status-stamp">
                @if($sale->payment_status === \App\Enums\PaymentStatus::PAID)
                <div class="stamp-paid">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Payé intégralement
                </div>
                @else
                <div class="stamp-pending">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Solde en attente
                </div>
                @endif
            </div>
        </div>

        {{-- Tear line --}}
        <div class="tear-line">
            <div class="tear-circle"></div>
            <div class="tear-dash"></div>
            <div class="tear-circle"></div>
        </div>

        {{-- Footer --}}
        <div class="receipt-footer">
            @if(config('boutique.message_recu'))
            <div class="footer-message">{{ config('boutique.message_recu') }}</div>
            @else
            <div class="footer-message">Merci pour votre confiance !</div>
            @endif
            <div class="footer-contacts">
                @if(config('boutique.adresse')){{ config('boutique.adresse') }}<br>@endif
                @if(config('boutique.telephone'))Tél : {{ config('boutique.telephone') }}<br>@endif
                @if(config('boutique.email')){{ config('boutique.email') }}@endif
            </div>
            <div style="margin-top: 12px; font-size: 10px; color: #d4d4d8;">
                Document généré le {{ now()->format('d/m/Y à H:i') }}
            </div>
        </div>

    </div><!-- /.receipt -->

    <script>
        // Téléchargement PDF via l'impression navigateur
        function downloadPdf() {
            const originalTitle = document.title;
            document.title = 'Recu-{{ $sale->reference }}';
            window.print();
            document.title = originalTitle;
        }
    </script>
</body>
</html>
