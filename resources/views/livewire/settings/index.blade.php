<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Paramètres</h1>
            <p class="text-sm text-gray-400 mt-0.5">Configuration de la boutique et des règles métier</p>
        </div>
    </div>

    {{-- Layout : stack sur mobile, side-by-side sur lg+ --}}
    <div class="flex flex-col lg:grid lg:grid-cols-[220px_1fr] gap-5 items-start">

        {{-- ── Nav latérale ──────────────────────────────────── --}}
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden lg:sticky lg:top-20 w-full">
            <div class="p-3 border-b border-gray-100">
                <p class="text-[10px] font-medium text-gray-300 uppercase tracking-widest px-1 mb-2">Configuration</p>

                {{-- Sur mobile : tabs horizontaux --}}
                <div class="flex lg:flex-col gap-1 overflow-x-auto pb-1 lg:pb-0">
                    <button wire:click="$set('activeTab', 'general')"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm transition-all whitespace-nowrap shrink-0 lg:w-full
                                   {{ $activeTab === 'general' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                        <div class="w-6 h-6 lg:w-7 lg:h-7 rounded-lg flex items-center justify-center shrink-0
                                    {{ $activeTab === 'general' ? 'bg-white/10' : 'bg-gray-100' }}">
                            <x-heroicon-o-building-storefront class="w-3 h-3 lg:w-3.5 lg:h-3.5 {{ $activeTab === 'general' ? 'text-white' : 'text-gray-500' }}"/>
                        </div>
                        Boutique
                    </button>

                    <button wire:click="$set('activeTab', 'theme')"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm transition-all whitespace-nowrap shrink-0 lg:w-full
                                   {{ $activeTab === 'theme' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                        <div class="w-6 h-6 lg:w-7 lg:h-7 rounded-lg flex items-center justify-center shrink-0
                                    {{ $activeTab === 'theme' ? 'bg-white/10' : 'bg-gray-100' }}">
                            <x-heroicon-o-swatch class="w-3 h-3 lg:w-3.5 lg:h-3.5 {{ $activeTab === 'theme' ? 'text-white' : 'text-gray-500' }}"/>
                        </div>
                        Thème & couleurs
                    </button>

                    <button wire:click="$set('activeTab', 'vente')"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm transition-all whitespace-nowrap shrink-0 lg:w-full
                                   {{ $activeTab === 'vente' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                        <div class="w-6 h-6 lg:w-7 lg:h-7 rounded-lg flex items-center justify-center shrink-0
                                    {{ $activeTab === 'vente' ? 'bg-white/10' : 'bg-gray-100' }}">
                            <x-heroicon-o-shopping-bag class="w-3 h-3 lg:w-3.5 lg:h-3.5 {{ $activeTab === 'vente' ? 'text-white' : 'text-gray-500' }}"/>
                        </div>
                        Règles vente
                    </button>
                </div>
            </div>
            <div class="hidden lg:block px-4 py-3">
                <p class="text-[11px] text-gray-300">{{ Setting::get('boutique.nom', config('boutique.nom')) }} · v1.0</p>
            </div>
        </div>

        {{-- ── Contenu ─────────────────────────────────────────── --}}
        <div class="w-full min-w-0">

            {{-- ── Boutique ── --}}
            @if($activeTab === 'general')
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                        <x-heroicon-o-building-storefront class="w-4 h-4 text-blue-600"/>
                    </div>
                    <div>
                        <p class="text-sm font-medium">Informations de la boutique</p>
                        <p class="text-xs text-gray-400">Identité et coordonnées affichées sur les reçus</p>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    {{-- Grille : 1 col mobile, 2 col md+ --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Nom de la boutique *</label>
                            <input wire:model="nom" type="text" placeholder="Ex: TechShop Cotonou"
                                   class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 focus:bg-white transition-colors"/>
                            @error('nom')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Slogan</label>
                            <input wire:model="slogan" type="text" placeholder="Ex: Votre partenaire tech"
                                   class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 focus:bg-white transition-colors"/>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Téléphone</label>
                            <input wire:model="telephone" type="text" placeholder="+229 01 XX XX XX"
                                   class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 focus:bg-white transition-colors"/>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Email</label>
                            <input wire:model="email" type="email" placeholder="contact@boutique.bj"
                                   class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 focus:bg-white transition-colors"/>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Adresse</label>
                            <input wire:model="adresse" type="text" placeholder="Cotonou, Bénin"
                                   class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 focus:bg-white transition-colors"/>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Message bas de reçu</label>
                            <input wire:model="message_recu" type="text" placeholder="Ex: Échanges sous 7 jours avec reçu."
                                   class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 focus:bg-white transition-colors"/>
                        </div>
                    </div>

                    {{-- Logo --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Logo de la boutique</label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                            <div class="w-14 h-14 bg-white border border-gray-200 rounded-xl flex items-center justify-center shrink-0 overflow-hidden">
                                @if($logoPreview)
                                    <img src="{{ Storage::url($logoPreview) }}" alt="Logo" class="w-full h-full object-contain"/>
                                @else
                                    <x-heroicon-o-photo class="w-6 h-6 text-gray-300"/>
                                @endif
                            </div>
                            <div>
                                <x-mary-file wire:model="logo" accept="image/*" class="text-sm"/>
                                <p class="text-xs text-gray-400 mt-1.5">PNG, JPG — max 2 Mo</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end px-5 py-3.5 border-t border-gray-100 bg-gray-50">
                    <button wire:click="saveGeneral"
                            class="inline-flex items-center gap-2 h-9 px-5 bg-gray-900 text-white text-sm font-medium rounded-xl hover:opacity-85 transition-opacity">
                        <x-heroicon-o-check class="w-3.5 h-3.5"/>
                        Sauvegarder
                    </button>
                </div>
            </div>
            @endif

            {{-- ── Thème ── --}}
            @if($activeTab === 'theme')
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                        <x-heroicon-o-swatch class="w-4 h-4 text-purple-600"/>
                    </div>
                    <div>
                        <p class="text-sm font-medium">Couleurs et thème</p>
                        <p class="text-xs text-gray-400">Personnalise l'apparence de l'interface</p>
                    </div>
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach([
                            ['field' => 'primary',      'label' => 'Couleur principale'],
                            ['field' => 'primary_dark', 'label' => 'Couleur principale (sombre)'],
                            ['field' => 'secondary',    'label' => 'Couleur secondaire'],
                            ['field' => 'accent',       'label' => 'Couleur accent'],
                            ['field' => 'sidebar_bg',   'label' => 'Fond sidebar'],
                            ['field' => 'sidebar_text', 'label' => 'Texte sidebar'],
                        ] as $c)
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $c['label'] }}</label>
                            <div class="flex items-center gap-2">
                                <div class="w-9 h-9 rounded-lg border border-gray-200 overflow-hidden shrink-0">
                                    <input type="color" wire:model.live="{{ $c['field'] }}"
                                           class="w-full h-full border-none cursor-pointer"/>
                                </div>
                                <input type="text" wire:model.live="{{ $c['field'] }}"
                                       class="flex-1 h-9 px-3 text-xs font-mono border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 focus:bg-white transition-colors min-w-0"/>
                                <div class="w-5 h-5 rounded-md shrink-0"
                                     style="background: {{ $this->{$c['field']} }}; border: 1px solid #e5e3df;"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Aperçu --}}
                    <div class="flex items-center gap-2 mt-5 p-3.5 bg-gray-50 border border-gray-200 rounded-xl flex-wrap">
                        <span class="text-xs text-gray-400 mr-1">Aperçu :</span>
                        <span class="px-3 py-1 rounded-lg text-xs font-medium text-white" style="background: {{ $primary }}">Principal</span>
                        <span class="px-3 py-1 rounded-lg text-xs font-medium text-white" style="background: {{ $secondary }}">Secondaire</span>
                        <span class="px-3 py-1 rounded-lg text-xs font-medium text-white" style="background: {{ $accent }}">Accent</span>
                        <span class="px-3 py-1 rounded-lg text-xs font-medium" style="background: {{ $sidebar_bg }}; color: {{ $sidebar_text }}">Sidebar</span>
                    </div>
                </div>

                <div class="flex justify-end px-5 py-3.5 border-t border-gray-100 bg-gray-50">
                    <button wire:click="saveTheme"
                            class="inline-flex items-center gap-2 h-9 px-5 bg-gray-900 text-white text-sm font-medium rounded-xl hover:opacity-85 transition-opacity">
                        <x-heroicon-o-check class="w-3.5 h-3.5"/>
                        Sauvegarder le thème
                    </button>
                </div>
            </div>
            @endif

            {{-- ── Règles vente ── --}}
            @if($activeTab === 'vente')
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                        <x-heroicon-o-shopping-bag class="w-4 h-4 text-amber-700"/>
                    </div>
                    <div>
                        <p class="text-sm font-medium">Règles métier — Ventes</p>
                        <p class="text-xs text-gray-400">Crédit, acomptes et délais de paiement</p>
                    </div>
                </div>

                <div class="p-5 space-y-5">
                    {{-- Toggle crédit --}}
                    <div class="flex items-start sm:items-center justify-between gap-4 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Autoriser le crédit client</p>
                            <p class="text-xs text-gray-400 mt-0.5">Permettre les ventes avec solde en attente pour les clients particuliers</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" wire:model="permettre_credit_client" class="sr-only peer"/>
                            <div class="w-10 h-6 bg-gray-200 peer-checked:bg-indigo-500 rounded-full transition-colors relative
                                        after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                        after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-transform after:shadow-sm
                                        peer-checked:after:translate-x-4"></div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Acompte minimum revendeur (%)</label>
                            <input wire:model="acompte_minimum_revendeur" type="number" min="0" max="100"
                                   class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 focus:bg-white transition-colors"/>
                            <p class="text-xs text-gray-400 mt-1">0 = pas de minimum requis</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Délai paiement maximum (jours)</label>
                            <input wire:model="delai_paiement_max_jours" type="number" min="1" max="365"
                                   class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-gray-50 outline-none focus:border-gray-400 focus:bg-white transition-colors"/>
                            <p class="text-xs text-gray-400 mt-1">Au-delà, une alerte créance est déclenchée</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end px-5 py-3.5 border-t border-gray-100 bg-gray-50">
                    <button wire:click="saveVente"
                            class="inline-flex items-center gap-2 h-9 px-5 bg-gray-900 text-white text-sm font-medium rounded-xl hover:opacity-85 transition-opacity">
                        <x-heroicon-o-check class="w-3.5 h-3.5"/>
                        Sauvegarder
                    </button>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
