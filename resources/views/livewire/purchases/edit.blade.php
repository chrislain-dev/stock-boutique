<div>
    <x-mary-header
        title="Modifier {{ $purchase->reference }}"
        subtitle="{{ $purchase->supplier->name }}"
        icon="o-pencil"
    >
        <x-slot:actions>
            <x-mary-button label="Annuler" icon="o-x-mark" class="btn-ghost btn-sm" link="{{ route('purchases.show', $purchase->id) }}" />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-mary-select
                label="Fournisseur *"
                wire:model="supplier_id"
                :options="$suppliers"
                option-value="id"
                option-label="name"
                icon="o-building-office"
            />
            <x-mary-input
                label="Date d'achat *"
                wire:model="purchase_date"
                type="date"
                icon="o-calendar"
            />
            <x-mary-select
                label="Statut commande *"
                wire:model="status"
                :options="[
                    ['id'=>'received',  'name'=>'Reçu'],
                    ['id'=>'pending',   'name'=>'En attente'],
                    ['id'=>'cancelled', 'name'=>'Annulé'],
                ]"
                option-value="id"
                option-label="name"
                icon="o-clipboard-document-check"
            />
            <x-mary-select
                label="Statut paiement *"
                wire:model.live="payment_status"
                :options="$paymentStatuses"
                option-value="id"
                option-label="name"
                icon="o-banknotes"
            />
            @if($payment_status !== 'unpaid')
            <x-mary-select
                label="Mode de paiement"
                wire:model="payment_method"
                :options="$paymentMethods"
                option-value="id"
                option-label="name"
                icon="o-credit-card"
            />
            <x-mary-input
                label="Montant payé"
                wire:model="paid_amount"
                type="number"
                icon="o-banknotes"
                :suffix="config('boutique.devise_symbole')"
            />
            <x-mary-input
                label="Référence transaction"
                wire:model="transaction_reference"
                placeholder="Ex: TXN-123456"
                icon="o-hashtag"
            />
            @endif
            @if($payment_status === 'partial' || $payment_status === 'unpaid')
            <x-mary-input
                label="Date limite paiement"
                wire:model="due_date"
                type="date"
                icon="o-clock"
            />
            @endif
            <x-mary-textarea
                label="Notes"
                wire:model="notes"
                class="md:col-span-2"
                rows="2"
            />
        </div>

        <div class="flex justify-end mt-6 gap-3">
            <x-mary-button label="Annuler" class="btn-ghost" link="{{ route('purchases.show', $purchase->id) }}" />
            <x-mary-button label="Enregistrer" icon="o-check" class="btn-primary" wire:click="save" />
        </div>
    </x-mary-card>
</div>
