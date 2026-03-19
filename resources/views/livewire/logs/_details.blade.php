@if($log->old_values || $log->new_values)
<details class="mt-1.5 group">
    <summary class="text-[11px] text-gray-400 cursor-pointer list-none flex items-center gap-1 hover:text-gray-600 transition-colors">
        <svg class="w-3 h-3 transition-transform group-open:rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="9 18 15 12 9 6"/>
        </svg>
        Voir les détails
    </summary>

    @php
    $fieldLabels = [
        'supplier_id'             => 'Fournisseur',
        'reference'               => 'Référence',
        'total_amount'            => 'Montant total',
        'paid_amount'             => 'Montant payé',
        'payment_status'          => 'Statut paiement',
        'status'                  => 'Statut',
        'payment_method'          => 'Méthode de paiement',
        'transaction_reference'   => 'Réf. transaction',
        'purchase_date'           => 'Date d\'achat',
        'due_date'                => 'Date d\'échéance',
        'notes'                   => 'Notes',
        'created_by'              => 'Créé par',
        'created_at'              => 'Créé le',
        'updated_at'              => 'Modifié le',
        'id'                      => 'ID',
        'name'                    => 'Nom',
        'price'                   => 'Prix',
        'quantity'                => 'Quantité',
        'stock'                   => 'Stock',
        'customer_name'           => 'Client',
        'customer_phone'          => 'Téléphone',
        'sale_status'             => 'Statut vente',
        'reseller_id'             => 'Revendeur',
        'customer_type'           => 'Type client',
        'line_total'              => 'Total ligne',
        'purchase_price_snapshot' => 'Prix d\'achat',
        'email'                   => 'Email',
        'role'                    => 'Rôle',
        'brand_id'                => 'Marque',
        'barcode'                 => 'Code-barres',
        'selling_price'           => 'Prix de vente',
        'description'             => 'Description',
    ];

    $statusLabels = [
        'unpaid'    => 'Non payé',
        'partial'   => 'Partiel',
        'paid'      => 'Payé',
        'received'  => 'Reçu',
        'pending'   => 'En attente',
        'completed' => 'Terminé',
        'cancelled' => 'Annulé',
        'client'    => 'Client',
        'reseller'  => 'Revendeur',
        'cash'      => 'Espèces',
        'card'      => 'Carte',
        'transfer'  => 'Virement',
        'bank_transfer' => 'Virement bancaire',
        'mobile'    => 'Mobile Money',
        'admin'     => 'Administrateur',
        'seller'    => 'Vendeur',
    ];

    $amountFields = ['total_amount', 'paid_amount', 'price', 'selling_price', 'line_total', 'purchase_price_snapshot'];
    $dateFields   = ['created_at', 'updated_at', 'purchase_date', 'due_date'];
    $skipFields   = ['id', 'created_by', 'supplier_id', 'reseller_id', 'brand_id', 'updated_at'];

    $formatValue = function($key, $value) use ($statusLabels, $amountFields, $dateFields) {
        if (is_null($value))                              return '—';
        if (is_bool($value))                              return $value ? 'Oui' : 'Non';
        if (in_array($key, $amountFields) && is_numeric($value))
            return number_format((float) $value, 0, ',', ' ') . ' F';
        if (in_array($key, $dateFields) && $value)
            return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
        if (isset($statusLabels[$value]))                 return $statusLabels[$value];
        return $value;
    };
    @endphp

    <div class="mt-2 rounded-lg border border-gray-100 overflow-hidden text-[11px]">

        {{-- ── Modification : tableau Champ / Avant / Après ── --}}
        @if($log->old_values && $log->new_values)
            @php
                $keys    = array_unique(array_merge(array_keys($log->old_values), array_keys($log->new_values)));
                $keys    = array_filter($keys, fn($k) => !in_array($k, $skipFields));
                $changed = array_values(array_filter($keys, fn($k) =>
                    ($log->old_values[$k] ?? null) !== ($log->new_values[$k] ?? null)
                ));
            @endphp
            @if(count($changed))
                <div class="grid grid-cols-3 bg-gray-50 px-3 py-1.5 border-b border-gray-100">
                    <span class="font-semibold text-gray-500">Champ</span>
                    <span class="font-semibold text-red-500">Avant</span>
                    <span class="font-semibold text-green-700">Après</span>
                </div>
                @foreach($changed as $key)
                <div class="grid grid-cols-3 px-3 py-1.5 border-b border-gray-100 last:border-none hover:bg-gray-50 transition-colors">
                    <span class="text-gray-500">{{ $fieldLabels[$key] ?? $key }}</span>
                    <span class="text-red-500">{{ $formatValue($key, $log->old_values[$key] ?? null) }}</span>
                    <span class="text-green-700">{{ $formatValue($key, $log->new_values[$key] ?? null) }}</span>
                </div>
                @endforeach
            @else
                <p class="px-3 py-2.5 text-gray-400 italic">Aucun changement détecté</p>
            @endif

        {{-- ── Création ── --}}
        @elseif($log->new_values)
            @php
                $filtered = array_filter(
                    $log->new_values,
                    fn($v, $k) => !in_array($k, $skipFields) && !is_null($v),
                    ARRAY_FILTER_USE_BOTH
                );
            @endphp
            @foreach($filtered as $key => $value)
            <div class="flex items-center justify-between gap-4 px-3 py-1.5 border-b border-gray-100 last:border-none hover:bg-gray-50 transition-colors">
                <span class="text-gray-500 shrink-0">{{ $fieldLabels[$key] ?? $key }}</span>
                <span class="text-green-700 font-medium text-right truncate">{{ $formatValue($key, $value) }}</span>
            </div>
            @endforeach

        {{-- ── Suppression ── --}}
        @elseif($log->old_values)
            @php
                $filtered = array_filter(
                    $log->old_values,
                    fn($v, $k) => !in_array($k, $skipFields) && !is_null($v),
                    ARRAY_FILTER_USE_BOTH
                );
            @endphp
            @foreach($filtered as $key => $value)
            <div class="flex items-center justify-between gap-4 px-3 py-1.5 border-b border-gray-100 last:border-none hover:bg-gray-50 transition-colors">
                <span class="text-gray-500 shrink-0">{{ $fieldLabels[$key] ?? $key }}</span>
                <span class="text-red-500 font-medium text-right truncate">{{ $formatValue($key, $value) }}</span>
            </div>
            @endforeach
        @endif

    </div>
</details>
@endif
