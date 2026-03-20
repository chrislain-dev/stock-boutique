{{--
    Modal de suppression d'une vente avec confirmation par mot de passe.
    À inclure dans resources/views/livewire/sales/show.blade.php :

    @include('livewire.sales._delete-modal')
--}}

<x-mary-modal wire:model="showDeleteModal" title="Supprimer la vente" class="backdrop-blur" persistent>

    {{-- Bandeau d'avertissement --}}
    <div class="flex items-start gap-3 p-4 mb-5 rounded-xl bg-error/10 border border-error/20">
        <x-mary-icon name="o-exclamation-triangle" class="w-5 h-5 text-error mt-0.5 shrink-0"/>
        <div>
            <p class="text-sm font-semibold text-error">Action irréversible</p>
            <p class="text-xs text-base-content/60 mt-0.5">
                La vente <strong>{{ $sale->reference }}</strong> sera annulée et les produits vendus
                retourneront automatiquement en stock.
            </p>
        </div>
    </div>

    {{-- Récapitulatif vente --}}
    <div class="grid grid-cols-3 gap-3 mb-5 text-center">
        <div class="bg-base-200 rounded-xl p-3">
            <p class="text-xs text-base-content/50 mb-1">Total</p>
            <p class="text-sm font-bold">{{ number_format($sale->total_amount, 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-base-200 rounded-xl p-3">
            <p class="text-xs text-base-content/50 mb-1">Articles</p>
            <p class="text-sm font-bold">{{ $sale->items->count() }}</p>
        </div>
        <div class="bg-base-200 rounded-xl p-3">
            <p class="text-xs text-base-content/50 mb-1">Paiements</p>
            <p class="text-sm font-bold">{{ $sale->payments->count() }}</p>
        </div>
    </div>

    <div class="space-y-4">

        {{-- Motif obligatoire --}}
        <x-mary-textarea
            label="Motif de suppression *"
            wire:model="delete_reason"
            placeholder="Expliquez pourquoi cette vente est supprimée (min. 10 caractères)…"
            rows="3"
            hint="Ce motif sera conservé dans les logs d'activité."
        />

        {{-- Confirmation mot de passe --}}
        <div>
            <x-mary-input
                label="Votre mot de passe *"
                wire:model="delete_password"
                type="password"
                placeholder="••••••••"
                icon="o-lock-closed"
                :class="$delete_password_error ? 'input-error' : ''"
            />
            @if ($delete_password_error)
                <p class="text-xs text-error mt-1 flex items-center gap-1">
                    <x-mary-icon name="o-x-circle" class="w-3.5 h-3.5"/>
                    Mot de passe incorrect. Veuillez réessayer.
                </p>
            @endif
        </div>

    </div>

    <x-slot:actions>
        {{-- Annuler --}}
        <x-mary-button
            label="Annuler"
            wire:click="$set('showDeleteModal', false)"
            class="btn-ghost"
        />

        {{-- Confirmer la suppression --}}
        <x-mary-button
            label="Supprimer définitivement"
            wire:click="deleteSale"
            class="btn-error"
            spinner="deleteSale"
            icon="o-trash"
        />
    </x-slot:actions>

</x-mary-modal>
