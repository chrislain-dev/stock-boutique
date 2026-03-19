<div>
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Fournisseurs</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $suppliers->total() }} fournisseur(s) enregistré(s)</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-3 h-9">
                <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 text-gray-400"/>
                <input wire:model.live.debounce="search" type="text"
                       placeholder="Nom, téléphone, pays..."
                       class="border-none outline-none text-sm text-gray-900 bg-transparent w-48 placeholder-gray-300"/>
            </div>
            @if(auth()->user()->isAdmin())
            <button wire:click="openCreateModal"
                    class="inline-flex items-center gap-1.5 bg-sky-500 hover:bg-sky-600 text-white
                           px-4 h-9 rounded-xl text-sm font-medium transition-colors">
                <x-heroicon-o-plus class="w-3.5 h-3.5"/>
                Nouveau fournisseur
            </button>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-4 gap-3 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-truck class="w-4.5 h-4.5 text-sky-700"/>
            </div>
            <div>
                <p class="text-lg font-semibold tracking-tight">{{ $suppliers->total() }}</p>
                <p class="text-xs text-gray-400">Total fournisseurs</p>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-check-circle class="w-4.5 h-4.5 text-green-700"/>
            </div>
            <div>
                <p class="text-lg font-semibold tracking-tight">{{ $activeCount }}</p>
                <p class="text-xs text-gray-400">Fournisseurs actifs</p>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-shopping-bag class="w-4.5 h-4.5 text-purple-600"/>
            </div>
            <div>
                <p class="text-lg font-semibold tracking-tight">{{ $totalPurchases }}</p>
                <p class="text-xs text-gray-400">Commandes totales</p>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-banknotes class="w-4.5 h-4.5 text-amber-700"/>
            </div>
            <div>
                <p class="text-lg font-semibold tracking-tight">{{ number_format($totalSpent / 1000000, 1) }}M</p>
                <p class="text-xs text-gray-400">FCFA engagés</p>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        {{-- En-tête --}}
        <div class="grid grid-cols-[2.5fr_1.5fr_1.2fr_1fr_1fr_90px] px-5 py-2.5 border-b border-gray-100 bg-gray-50">
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Fournisseur</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Téléphone</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Pays</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Achats</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Statut</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider text-right">Actions</span>
        </div>

        {{-- Lignes --}}
        @forelse($suppliers as $supplier)
        <div class="grid grid-cols-[2.5fr_1.5fr_1.2fr_1fr_1fr_90px] px-5 py-3.5 border-b border-gray-100 last:border-none items-center hover:bg-gray-50 transition-colors group">

            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-[12px] font-semibold shrink-0"
                     style="background: {{ $supplier->avatar_bg ?? '#E0F2FE' }}; color: {{ $supplier->avatar_color ?? '#0369a1' }}">
                    {{ strtoupper(substr($supplier->name, 0, 2)) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $supplier->name }}</p>
                    <p class="text-xs text-gray-400">{{ $supplier->email ?? '—' }}</p>
                </div>
            </div>

            <span class="text-[12px] font-mono text-gray-600">{{ $supplier->phone }}</span>

            <div class="flex items-center gap-1.5 text-sm text-gray-600">
                @if($supplier->country)
                    <span class="w-2 h-2 rounded-sm bg-green-600 shrink-0"></span>
                @endif
                {{ $supplier->country ?? '—' }}
            </div>

            <span class="text-sm text-gray-600">{{ $supplier->purchases_count ?? 0 }} achats</span>

            <div>
                @if($supplier->is_active)
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-green-800 bg-green-50 px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-700"></span>Actif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-red-800 bg-red-50 px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>Inactif
                    </span>
                @endif
            </div>

            @if(auth()->user()->isAdmin())
            <div class="flex gap-1.5 justify-end">
                <button wire:click="openEditModal({{ $supplier->id }})"
                        class="w-7 h-7 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 flex items-center justify-center transition-colors">
                    <x-heroicon-o-pencil class="w-3.5 h-3.5"/>
                </button>
                <button wire:click="confirmDelete({{ $supplier->id }})"
                        class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition-colors">
                    <x-heroicon-o-trash class="w-3.5 h-3.5"/>
                </button>
            </div>
            @endif
        </div>
        @empty
        <div class="py-16 text-center">
            <x-heroicon-o-truck class="w-10 h-10 text-gray-200 mx-auto mb-3"/>
            <p class="text-sm text-gray-400">Aucun fournisseur trouvé</p>
        </div>
        @endforelse

        {{-- Pagination --}}
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $suppliers->links() }}
        </div>
    </div>

    {{-- Modal création / édition --}}
    <x-mary-modal wire:model="showModal"
        :title="$editingId ? 'Modifier le fournisseur' : 'Nouveau fournisseur'"
        box-class="max-w-2xl">
        <x-mary-form wire:submit="save">
            <div class="grid grid-cols-2 gap-4">
                <x-mary-input label="Nom" wire:model="name" placeholder="Ex: TechImport Cotonou" icon="o-building-office" required/>
                <x-mary-input label="Pays" wire:model="country" placeholder="Ex: Bénin, Togo..." icon="o-globe-alt"/>
                <x-mary-input label="Téléphone principal" wire:model="phone" placeholder="+229 01 XX XX XX" icon="o-phone" required/>
                <x-mary-input label="Téléphone secondaire" wire:model="phone_secondary" placeholder="+229 01 XX XX XX" icon="o-phone"/>
                <x-mary-input label="Email" wire:model="email" type="email" placeholder="contact@..." icon="o-envelope"/>
                <x-mary-input label="Adresse" wire:model="address" placeholder="Adresse complète" icon="o-map-pin"/>
            </div>
            <x-mary-textarea label="Notes" wire:model="notes" placeholder="Informations supplémentaires..." rows="3"/>
            <x-mary-toggle label="Fournisseur actif" wire:model="is_active"/>
            <x-slot:actions>
                <x-mary-button label="Annuler" wire:click="$set('showModal', false)" class="btn-ghost"/>
                <x-mary-button label="Sauvegarder" type="submit" class="btn-primary" spinner="save"/>
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>

    {{-- Modal suppression --}}
    <x-mary-modal wire:model="showDeleteModal" title="Confirmer la suppression">
        <p class="text-sm text-gray-600">Êtes-vous sûr de vouloir supprimer ce fournisseur ? Cette action est irréversible.</p>
        <x-slot:actions>
            <x-mary-button label="Annuler" wire:click="$set('showDeleteModal', false)" class="btn-ghost"/>
            <x-mary-button label="Supprimer" wire:click="delete" class="btn-error" spinner="delete"/>
        </x-slot:actions>
    </x-mary-modal>
</div>
