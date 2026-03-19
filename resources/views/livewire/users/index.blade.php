<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Utilisateurs</h1>
            <p class="text-sm text-gray-400 mt-0.5">Gestion des comptes et des accès</p>
        </div>
        <button wire:click="openCreate"
                class="inline-flex items-center gap-1.5 bg-indigo-500 hover:bg-indigo-600 text-white
                       px-4 h-9 rounded-xl text-sm font-medium transition-colors">
            <x-heroicon-o-plus class="w-3.5 h-3.5"/>
            Nouvel utilisateur
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-4 gap-2.5 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-users class="w-4 h-4 text-purple-600"/>
            </div>
            <div><p class="text-base font-semibold">{{ $users->total() }}</p><p class="text-[11px] text-gray-400">Total</p></div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-check-circle class="w-4 h-4 text-green-700"/>
            </div>
            <div><p class="text-base font-semibold">{{ $activeCount }}</p><p class="text-[11px] text-gray-400">Actifs</p></div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-shield-check class="w-4 h-4 text-amber-700"/>
            </div>
            <div><p class="text-base font-semibold">{{ $adminCount }}</p><p class="text-[11px] text-gray-400">Admins</p></div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-no-symbol class="w-4 h-4 text-red-500"/>
            </div>
            <div><p class="text-base font-semibold">{{ $inactiveCount }}</p><p class="text-[11px] text-gray-400">Inactifs</p></div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-2xl px-4 py-3 mb-4">
        <div class="relative flex-1">
            <x-heroicon-o-magnifying-glass class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400"/>
            <input wire:model.live.debounce="search" type="text"
                   placeholder="Rechercher par nom ou email..."
                   class="w-full h-8 pl-8 pr-3 text-sm border border-gray-200 rounded-lg bg-gray-50
                          outline-none focus:border-gray-400 focus:bg-white transition-colors"/>
        </div>
        <select wire:model.live="roleFilter"
                class="h-8 px-2.5 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none
                       focus:border-gray-400 text-gray-600 cursor-pointer">
            <option value="">Tous les rôles</option>
            @foreach($roles as $r)
            <option value="{{ $r['id'] }}">{{ $r['name'] }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="grid grid-cols-[2fr_2fr_1fr_1fr_1fr_100px] px-4 py-2.5 border-b border-gray-100 bg-gray-50">
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Utilisateur</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Email</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Rôle</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Statut</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Créé le</span>
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider text-right">Actions</span>
        </div>

        @forelse($users as $user)
        @php
            $isMe = $user->id === auth()->id();
            $isAdmin = $user->role === \App\Enums\UserRole::ADMIN;
            $avatarBg = $isAdmin ? 'bg-purple-50 text-purple-600' : 'bg-gray-100 text-gray-500';
        @endphp
        <div class="grid grid-cols-[2fr_2fr_1fr_1fr_1fr_100px] px-4 py-3 border-b border-gray-100
                    last:border-none items-center hover:bg-gray-50 transition-colors
                    {{ !$user->is_active ? 'opacity-60' : '' }}">

            {{-- Utilisateur --}}
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[11px] font-semibold shrink-0 {{ $avatarBg }}">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900 flex items-center gap-1.5">
                        {{ $user->name }}
                        @if($isMe)
                        <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded font-normal">vous</span>
                        @endif
                    </p>
                    <p class="text-xs text-gray-400">{{ $isAdmin ? 'Administrateur' : 'Vendeur' }}</p>
                </div>
            </div>

            {{-- Email --}}
            <span class="text-sm text-gray-500 truncate">{{ $user->email }}</span>

            {{-- Rôle --}}
            <div>
                @if($isAdmin)
                <span class="inline-flex items-center gap-1.5 text-[11px] font-medium bg-purple-50 text-purple-800 px-2.5 py-1 rounded-lg">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                    {{ $user->role->label() }}
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 text-[11px] font-medium bg-gray-100 text-gray-600 px-2.5 py-1 rounded-lg">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                    {{ $user->role->label() }}
                </span>
                @endif
            </div>

            {{-- Statut --}}
            <div>
                @if($user->is_active)
                <span class="inline-flex items-center gap-1.5 text-[11px] font-medium bg-green-50 text-green-800 px-2.5 py-1 rounded-lg">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-700"></span>Actif
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 text-[11px] font-medium bg-red-50 text-red-700 px-2.5 py-1 rounded-lg">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Inactif
                </span>
                @endif
            </div>

            {{-- Date --}}
            <span class="text-xs text-gray-500">{{ $user->created_at->format('d/m/Y') }}</span>

            {{-- Actions --}}
            <div class="flex items-center gap-1 justify-end">
                <button wire:click="toggleActive({{ $user->id }})"
                        title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}"
                        class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors
                               {{ $user->is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-green-600 hover:bg-green-50' }}">
                    @if($user->is_active)
                        <x-heroicon-o-lock-closed class="w-3.5 h-3.5"/>
                    @else
                        <x-heroicon-o-lock-open class="w-3.5 h-3.5"/>
                    @endif
                </button>
                <button wire:click="openEdit({{ $user->id }})"
                        title="Modifier"
                        class="w-7 h-7 rounded-lg text-blue-600 hover:bg-blue-50 flex items-center justify-center transition-colors">
                    <x-heroicon-o-pencil class="w-3.5 h-3.5"/>
                </button>
                @if(!$isMe)
                <button wire:click="confirmDelete({{ $user->id }})"
                        title="Supprimer"
                        class="w-7 h-7 rounded-lg text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors">
                    <x-heroicon-o-trash class="w-3.5 h-3.5"/>
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="py-16 flex flex-col items-center justify-center">
            <x-heroicon-o-users class="w-10 h-10 text-gray-200 mb-3"/>
            <p class="text-sm text-gray-400">Aucun utilisateur trouvé</p>
        </div>
        @endforelse

        <div class="px-4 py-3 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Modal formulaire --}}
    <x-mary-modal wire:model="showModal"
                  :title="$editingId ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur'">
        <div class="space-y-4 pt-1">
            <x-mary-input label="Nom complet" wire:model="name"
                          placeholder="Prénom Nom" icon="o-user"/>
            <x-mary-input label="Adresse email" wire:model="email"
                          type="email" placeholder="email@techshop.bj" icon="o-envelope"/>
            <x-mary-select label="Rôle" wire:model="role"
                           :options="$roles" option-value="id" option-label="name"
                           icon="o-shield-check"/>
            <x-mary-input
                label="{{ $editingId ? 'Nouveau mot de passe (laisser vide pour conserver)' : 'Mot de passe' }}"
                wire:model="password" type="password" icon="o-key"/>
            <x-mary-input label="Confirmer le mot de passe"
                          wire:model="password_confirmation" type="password" icon="o-key"/>
            @if($editingId)
            <x-mary-toggle label="Compte actif" wire:model="is_active"/>
            @endif
        </div>
        <x-slot:actions>
            <x-mary-button label="Annuler" wire:click="$set('showModal', false)" class="btn-ghost"/>
            <x-mary-button label="Enregistrer" wire:click="save" class="btn-primary" spinner="save"/>
        </x-slot:actions>
    </x-mary-modal>

    {{-- Modal suppression --}}
    <x-mary-modal wire:model="showDeleteModal" title="Confirmer la suppression">
        <p class="text-sm text-gray-600">Cette action est irréversible. L'utilisateur sera définitivement supprimé.</p>
        <x-slot:actions>
            <x-mary-button label="Annuler" wire:click="$set('showDeleteModal', false)" class="btn-ghost"/>
            <x-mary-button label="Supprimer" wire:click="delete" class="btn-error" spinner="delete"/>
        </x-slot:actions>
    </x-mary-modal>
</div>
