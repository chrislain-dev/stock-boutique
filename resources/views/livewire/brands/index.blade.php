<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between mb-6 gap-3">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Marques</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $brands->total() }} marque(s) enregistrée(s)</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-3 h-9 flex-1 sm:flex-none min-w-0">
                <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
                <input wire:model.live.debounce="search" type="text"
                       placeholder="Rechercher..."
                       class="border-none outline-none text-sm text-gray-900 bg-transparent w-full sm:w-40 placeholder-gray-300 min-w-0"/>
            </div>
            <div class="flex bg-white border border-gray-200 rounded-xl overflow-hidden shrink-0">
                <button wire:click="$set('viewMode', 'grid')"
                        class="w-9 h-9 flex items-center justify-center transition-colors
                               {{ $viewMode === 'grid' ? 'bg-gray-900' : 'hover:bg-gray-50' }}">
                    <x-heroicon-o-squares-2x2 class="w-4 h-4 {{ $viewMode === 'grid' ? 'text-white' : 'text-gray-500' }}"/>
                </button>
                <button wire:click="$set('viewMode', 'table')"
                        class="w-9 h-9 flex items-center justify-center transition-colors
                               {{ $viewMode === 'table' ? 'bg-gray-900' : 'hover:bg-gray-50' }}">
                    <x-heroicon-o-bars-3 class="w-4 h-4 {{ $viewMode === 'table' ? 'text-white' : 'text-gray-500' }}"/>
                </button>
            </div>
            @if(auth()->user()->isAdmin())
            <button wire:click="openCreateModal"
                    class="inline-flex items-center gap-1.5 bg-indigo-500 hover:bg-indigo-600 text-white
                           px-4 h-9 rounded-xl text-sm font-medium transition-colors shrink-0">
                <x-heroicon-o-plus class="w-3.5 h-3.5"/>
                <span class="hidden sm:inline">Nouvelle marque</span>
                <span class="sm:hidden">Nouveau</span>
            </button>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-tag class="w-5 h-5 text-purple-600"/>
            </div>
            <div>
                <p class="text-xl font-semibold tracking-tight">{{ $brands->total() }}</p>
                <p class="text-xs text-gray-400">Total marques</p>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-check-circle class="w-5 h-5 text-green-700"/>
            </div>
            <div>
                <p class="text-xl font-semibold tracking-tight">{{ $activeCount }}</p>
                <p class="text-xs text-gray-400">Marques actives</p>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                <x-heroicon-o-exclamation-circle class="w-5 h-5 text-amber-700"/>
            </div>
            <div>
                <p class="text-xl font-semibold tracking-tight">{{ $inactiveCount }}</p>
                <p class="text-xs text-gray-400">Marques inactives</p>
            </div>
        </div>
    </div>

    {{-- Vue CARDS --}}
    @if($viewMode === 'grid')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5">
        @foreach($brands as $brand)
        <div class="bg-white border border-gray-200 rounded-2xl p-5 hover:-translate-y-1 hover:shadow-lg hover:border-gray-300 transition-all duration-200 group">
            <div class="flex items-center justify-between mb-3.5">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-sm font-semibold shrink-0 overflow-hidden"
                    style="background: {{ $brand->avatar_bg ?? '#f0f0f0' }}; color: {{ $brand->avatar_color ?? '#111' }}">
                    @if($brand->logo_url)
                        <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                            class="w-full h-full object-contain p-1"/>
                    @else
                        {{ strtoupper(substr($brand->name, 0, 2)) }}
                    @endif
                </div>
                @if(auth()->user()->isAdmin())
                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button wire:click="openEditModal({{ $brand->id }})"
                            class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition-colors">
                        <x-heroicon-o-pencil class="w-3.5 h-3.5"/>
                    </button>
                    <button wire:click="confirmDelete({{ $brand->id }})"
                            class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition-colors">
                        <x-heroicon-o-trash class="w-3.5 h-3.5"/>
                    </button>
                </div>
                @endif
            </div>
            <p class="text-[15px] font-medium text-gray-900 truncate">{{ $brand->name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $brand->products_count ?? 0 }} produits</p>
            <div class="flex items-center justify-between mt-3.5 pt-3 border-t border-gray-100">
                @if($brand->is_active)
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-green-800 bg-green-50 px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-700"></span>Actif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-red-800 bg-red-50 px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>Inactif
                    </span>
                @endif
                <span class="text-[11px] text-gray-300">{{ $brand->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Vue TABLE --}}
    @else
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <div class="min-w-140">
                <div class="grid grid-cols-[2fr_1fr_1fr_1fr_80px] px-5 py-2.5 border-b border-gray-100 bg-gray-50">
                    <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Marque</span>
                    <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Produits</span>
                    <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Statut</span>
                    <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Créée le</span>
                    <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider text-right">Actions</span>
                </div>
                @foreach($brands as $brand)
                <div class="grid grid-cols-[2fr_1fr_1fr_1fr_80px] px-5 py-3.5 border-b border-gray-100 last:border-none items-center hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-[13px] font-semibold shrink-0 overflow-hidden"
                            style="background: {{ $brand->avatar_bg ?? '#f0f0f0' }}; color: {{ $brand->avatar_color ?? '#111' }}">
                            @if($brand->logo_url)
                                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                                    class="w-full h-full object-contain p-1"/>
                            @else
                                {{ strtoupper(substr($brand->name, 0, 2)) }}
                            @endif
                        </div>
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $brand->name }}</p>
                    </div>
                    <span class="text-sm text-gray-600">{{ $brand->products_count ?? 0 }} produit(s)</span>
                    <div>
                        @if($brand->is_active)
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-green-800 bg-green-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-700"></span>Actif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-red-800 bg-red-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>Inactif
                            </span>
                        @endif
                    </div>
                    <span class="text-sm text-gray-500">{{ $brand->created_at->format('d/m/Y') }}</span>
                    @if(auth()->user()->isAdmin())
                    <div class="flex gap-1.5 justify-end">
                        <button wire:click="openEditModal({{ $brand->id }})"
                                class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition-colors">
                            <x-heroicon-o-pencil class="w-3.5 h-3.5"/>
                        </button>
                        <button wire:click="confirmDelete({{ $brand->id }})"
                                class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition-colors">
                            <x-heroicon-o-trash class="w-3.5 h-3.5"/>
                        </button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Pagination --}}
    <div class="mt-4">{{ $brands->links() }}</div>

    {{-- Modal création / édition --}}
    <x-mary-modal wire:model="showModal"
                  :title="$editingId ? 'Modifier la marque' : 'Nouvelle marque'"
                  box-class="w-full max-w-lg mx-4">
        <x-mary-form wire:submit="save">
            <x-mary-input label="Nom de la marque" wire:model="name"
                placeholder="Ex: Apple, Samsung..." icon="o-tag" required/>

            {{-- Logo upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Logo</label>

                {{-- Prévisualisation logo existant --}}
                @if($existingLogoPath && !$logo)
                    <div class="flex items-center gap-2 mb-2 p-2.5 bg-gray-50 border border-gray-200 rounded-xl min-w-0 overflow-hidden">
                        <img src="{{ Storage::url($existingLogoPath) }}"
                            alt="Logo actuel"
                            class="w-10 h-10 object-contain rounded-lg border border-gray-100 bg-white p-1 shrink-0"/>
                        <div class="flex-1 min-w-0 overflow-hidden">
                            <p class="text-xs font-medium text-gray-700 truncate">Logo actuel</p>
                            <p class="text-xs text-gray-400 truncate">{{ basename($existingLogoPath) }}</p>
                        </div>
                        <button type="button" wire:click="removeLogo"
                                class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100
                                    flex items-center justify-center transition-colors shrink-0">
                            <x-heroicon-o-trash class="w-3.5 h-3.5"/>
                        </button>
                    </div>
                @endif

                {{-- Prévisualisation fichier sélectionné (temporaire Livewire) --}}
                @if($logo)
                    <div class="flex items-center gap-2 mb-2 p-2.5 bg-indigo-50 border border-indigo-200 rounded-xl min-w-0 overflow-hidden">
                        <img src="{{ $logo->temporaryUrl() }}"
                            alt="Nouveau logo"
                            class="w-10 h-10 object-contain rounded-lg border border-indigo-100 bg-white p-1 shrink-0"/>
                        <div class="flex-1 min-w-0 overflow-hidden">
                            <p class="text-xs font-medium text-indigo-700 truncate">Nouveau logo sélectionné</p>
                            <p class="text-xs text-indigo-400 truncate">{{ $logo->getClientOriginalName() }}</p>
                        </div>
                        <button type="button" wire:click="$set('logo', null)"
                                class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100
                                    flex items-center justify-center transition-colors shrink-0">
                            <x-heroicon-o-x-mark class="w-3.5 h-3.5"/>
                        </button>
                    </div>
                @endif

                {{-- Input file --}}
                <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed
                            border-gray-200 rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100
                            hover:border-indigo-300 transition-colors group">
                    <div class="flex flex-col items-center gap-1">
                        <x-heroicon-o-arrow-up-tray class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-colors"/>
                        <p class="text-xs text-gray-500 group-hover:text-indigo-600 text-center">
                            <span class="font-medium">Cliquer pour uploader</span> ou glisser-déposer
                        </p>
                        <p class="text-[10px] text-gray-400">PNG, JPG, WebP — max 2 Mo</p>
                    </div>
                    <input type="file" wire:model="logo" accept="image/*" class="hidden"/>
                </label>

                @error('logo')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <x-mary-toggle label="Marque active" wire:model="is_active"/>

            <x-slot:actions>
                <x-mary-button label="Annuler" wire:click="$set('showModal', false)" class="btn-ghost"/>
                <x-mary-button label="Sauvegarder" type="submit" class="btn-primary" spinner="save"/>
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>

    {{-- Modal confirmation suppression --}}
    <x-mary-modal wire:model="showDeleteModal"
                  title="Confirmer la suppression"
                  box-class="w-full max-w-sm mx-4">
        <p class="text-gray-600">Êtes-vous sûr de vouloir supprimer cette marque ? Cette action est irréversible.</p>
        <x-slot:actions>
            <x-mary-button label="Annuler" wire:click="$set('showDeleteModal', false)" class="btn-ghost"/>
            <x-mary-button label="Supprimer" wire:click="delete" class="btn-error" spinner="delete"/>
        </x-slot:actions>
    </x-mary-modal>
</div>
