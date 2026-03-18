<div>
    {{-- En-tête boutique --}}
    <div class="center" style="margin-bottom: 12px;">
        <p class="bold" style="font-size: 16px;">{{ config('boutique.nom', 'Ma Boutique') }}</p>
        @if(config('boutique.adresse'))
        <p>{{ config('boutique.adresse') }}</p>
        @endif
        @if(config('boutique.telephone'))
        <p>Tél: {{ config('boutique.telephone') }}</p>
        @endif
    </div>

    <div class="divider-double"></div>

    {{-- Infos vente --}}
    <div style="margin: 8px 0;">
        <div class="row">
            <span class="label">Réf:</span>
            <span class="bold">{{ $sale->reference }}</span>
        </div>
        <div class="row">
            <span class="label">Date:</span>
            <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span class="label">Vendeur:</span>
            <span>{{ $sale->createdBy?->name ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="label">Client:</span>
            <span>
                @if($sale->customer_type === 'reseller')
                    {{ $sale->reseller?->name ?? '—' }} (Rev.)
                @else
                    {{ $sale->customer_name ?: 'Anonyme' }}
                @endif
            </span>
        </div>
        @if($sale->customer_phone)
        <div class="row">
            <span class="label">Tél:</span>
            <span>{{ $sale->customer_phone }}</span>
        </div>
        @endif
    </div>

    <div class="divider"></div>

    {{-- Articles --}}
    <p class="bold center" style="margin-bottom: 6px;">ARTICLES</p>
    @foreach($sale->items as $item)
    <div class="row-item">
        <p class="bold">{{ $item->productModel->display_label }}</p>
        @if($item->product)
        <p class="label" style="font-size: 10px;">
            {{ $item->product->imei ? 'IMEI: ' . $item->product->imei : '' }}
            {{ $item->product->serial_number ? 'S/N: ' . $item->product->serial_number : '' }}
        </p>
        @endif
        <div class="row" style="margin-top: 2px;">
            <span>{{ $item->quantity }} × {{ number_format($item->unit_price, 0, ',', ' ') }}
                @if($item->discount > 0)
                    <span class="label">(-{{ number_format($item->discount, 0, ',', ' ') }})</span>
                @endif
            </span>
            <span class="bold">
                {{ number_format($item->line_total, 0, ',', ' ') }}
                {{ config('boutique.devise_symbole') }}
            </span>
        </div>
    </div>
    @endforeach

    @if($sale->is_trade_in && $sale->trade_in_value > 0)
    <div class="divider"></div>
    <div class="row">
        <span class="label">Troc déduit</span>
        <span>- {{ number_format($sale->trade_in_value, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
    </div>
    @endif

    <div class="divider-double"></div>

    {{-- Totaux --}}
    <div class="row">
        <span class="bold">TOTAL NET</span>
        <span class="text-large">
            {{ number_format($sale->total_amount, 0, ',', ' ') }}
            {{ config('boutique.devise_symbole') }}
        </span>
    </div>

    <div class="divider"></div>

    {{-- Paiements --}}
    <p class="bold" style="margin-bottom: 4px;">PAIEMENTS</p>
    @foreach($sale->payments as $payment)
    <div class="row">
        <span>{{ $payment->payment_method->label() }}
            @if($payment->transaction_reference)
                <span class="label">({{ $payment->transaction_reference }})</span>
            @endif
        </span>
        <span>{{ number_format($payment->amount, 0, ',', ' ') }} {{ config('boutique.devise_symbole') }}</span>
    </div>
    @endforeach

    <div class="divider"></div>

    <div class="row">
        <span class="bold">PAYÉ</span>
        <span class="bold">
            {{ number_format($sale->paid_amount, 0, ',', ' ') }}
            {{ config('boutique.devise_symbole') }}
        </span>
    </div>

    @if($sale->remaining_amount > 0)
    <div class="row">
        <span class="bold">RESTE DÛ</span>
        <span class="bold">
            {{ number_format($sale->remaining_amount, 0, ',', ' ') }}
            {{ config('boutique.devise_symbole') }}
        </span>
    </div>
    @if($sale->due_date)
    <div class="row">
        <span class="label">Échéance:</span>
        <span>{{ $sale->due_date->format('d/m/Y') }}</span>
    </div>
    @endif
    @endif

    <div class="divider-double"></div>

    {{-- Pied de page --}}
    <div class="center" style="margin-top: 12px;">
        @if($sale->payment_status === \App\Enums\PaymentStatus::PAID)
        <p class="bold">✓ PAYÉ INTÉGRALEMENT</p>
        @else
        <p class="bold">⚠ SOLDE EN ATTENTE</p>
        @endif
        @if(config('boutique.message_recu'))
        <p style="margin-top: 8px; font-size: 11px;">{{ config('boutique.message_recu') }}</p>
        @endif
        <p style="margin-top: 8px; font-size: 10px; color: #555;">
            Merci pour votre achat !
        </p>
    </div>
</div>
