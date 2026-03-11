@if($count > 0)
    <x-mary-button
        icon="o-bell-alert"
        class="btn-ghost btn-sm text-warning"
        link="{{ route('products.index', ['filter' => 'low_stock']) }}"
        tooltip="{{ $count }} produit(s) en stock bas"
    >
        <x-mary-badge value="{{ $count }}" class="badge-warning badge-sm" />
    </x-mary-button>
@else
    <x-mary-button
        icon="o-bell"
        class="btn-ghost btn-sm"
        tooltip="Aucune alerte stock"
    />
@endif
