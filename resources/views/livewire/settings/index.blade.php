<div>
    <x-mary-header title="Paramètres" subtitle="Configuration de la boutique" icon="o-cog-6-tooth" />

    {{-- Tabs --}}
    <div class="tabs tabs-bordered mb-6">
        @foreach($tabs as $tab)
        <button
            class="tab gap-2 {{ $activeTab === $tab['id'] ? 'tab-active' : '' }}"
            wire:click="$set('activeTab', '{{ $tab['id'] }}')"
        >
            <x-mary-icon name="{{ $tab['icon'] }}" class="w-4 h-4" />
            {{ $tab['name'] }}
        </button>
        @endforeach
    </div>

    {{-- Infos boutique --}}
    @if($activeTab === 'general')
    <x-mary-card title="Informations de la boutique">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-mary-input
                label="Nom de la boutique *"
                wire:model="nom"
                icon="o-building-storefront"
                placeholder="Ex: TechShop Cotonou"
            />
            <x-mary-input
                label="Slogan"
                wire:model="slogan"
                icon="o-chat-bubble-left"
                placeholder="Ex: Votre partenaire tech"
            />
            <x-mary-input
                label="Téléphone"
                wire:model="telephone"
                icon="o-phone"
                placeholder="+229 01 XX XX XX"
            />
            <x-mary-input
                label="Email"
                wire:model="email"
                type="email"
                icon="o-envelope"
                placeholder="contact@boutique.bj"
            />
            <div class="md:col-span-2">
                <x-mary-input
                    label="Adresse"
                    wire:model="adresse"
                    icon="o-map-pin"
                    placeholder="Cotonou, Bénin"
                />
            </div>
            <div class="md:col-span-2">
                <x-mary-input
                    label="Message bas de reçu"
                    wire:model="message_recu"
                    icon="o-document-text"
                    placeholder="Ex: Échanges sous 7 jours avec reçu."
                />
            </div>
        </div>

        {{-- Logo --}}
        <div class="mt-6">
            <p class="text-sm font-medium mb-2">Logo</p>
            <div class="flex items-center gap-4">
                @if($logoPreview)
                <img
                    src="{{ Storage::url($logoPreview) }}"
                    alt="Logo"
                    class="w-16 h-16 object-contain rounded-lg border border-base-300"
                />
                @else
                <div class="w-16 h-16 bg-base-200 rounded-lg flex items-center justify-center">
                    <x-mary-icon name="o-photo" class="w-8 h-8 text-gray-400" />
                </div>
                @endif
                <div>
                    <x-mary-file wire:model="logo" accept="image/*" />
                    <p class="text-xs text-gray-400 mt-1">PNG, JPG — max 2Mo</p>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Sauvegarder" icon="o-check" class="btn-primary" wire:click="saveGeneral" />
        </x-slot:actions>
    </x-mary-card>
    @endif

    {{-- Thème --}}
    @if($activeTab === 'theme')
    <x-mary-card title="Couleurs et thème">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach([
                ['field' => 'primary',      'label' => 'Couleur principale'],
                ['field' => 'primary_dark', 'label' => 'Couleur principale (sombre)'],
                ['field' => 'secondary',    'label' => 'Couleur secondaire'],
                ['field' => 'accent',       'label' => 'Couleur accent'],
                ['field' => 'sidebar_bg',   'label' => 'Fond sidebar'],
                ['field' => 'sidebar_text', 'label' => 'Texte sidebar'],
            ] as $color)
            <div>
                <p class="text-sm font-medium mb-1">{{ $color['label'] }}</p>
                <div class="flex items-center gap-3">
                    <input
                        type="color"
                        wire:model.live="{{ $color['field'] }}"
                        class="w-10 h-10 rounded cursor-pointer border border-base-300"
                    />
                    <x-mary-input
                        wire:model.live="{{ $color['field'] }}"
                        placeholder="#6366f1"
                        class="flex-1 font-mono text-sm"
                    />
                    <div
                        class="w-8 h-8 rounded"
                        style="background-color: {{ $this->{$color['field']} }}"
                    ></div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Aperçu --}}
        <div class="mt-6 p-4 rounded-lg border border-base-300">
            <p class="text-sm font-medium mb-3 text-gray-400">Aperçu</p>
            <div class="flex gap-3 flex-wrap">
                <span class="px-3 py-1 rounded text-white text-sm" style="background: {{ $primary }}">Principal</span>
                <span class="px-3 py-1 rounded text-white text-sm" style="background: {{ $secondary }}">Secondaire</span>
                <span class="px-3 py-1 rounded text-white text-sm" style="background: {{ $accent }}">Accent</span>
                <span class="px-3 py-1 rounded text-sm" style="background: {{ $sidebar_bg }}; color: {{ $sidebar_text }}">Sidebar</span>
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Sauvegarder le thème" icon="o-check" class="btn-primary" wire:click="saveTheme" />
        </x-slot:actions>
    </x-mary-card>
    @endif

    {{-- Règles vente --}}
    @if($activeTab === 'vente')
    <x-mary-card title="Règles métier — Ventes">
        <div class="space-y-6">
            <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                <div>
                    <p class="font-medium">Autoriser le crédit client</p>
                    <p class="text-sm text-gray-400">Permettre les ventes avec solde en attente pour les clients particuliers</p>
                </div>
                <input
                    type="checkbox"
                    wire:model="permettre_credit_client"
                    class="toggle toggle-primary"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-mary-input
                    label="Acompte minimum revendeur (%)"
                    wire:model="acompte_minimum_revendeur"
                    type="number"
                    min="0"
                    max="100"
                    icon="o-percent-badge"
                    hint="0 = pas de minimum"
                />
                <x-mary-input
                    label="Délai paiement maximum (jours)"
                    wire:model="delai_paiement_max_jours"
                    type="number"
                    min="1"
                    max="365"
                    icon="o-calendar"
                />
            </div>
        </div>

        <x-slot:actions>
            <x-mary-button label="Sauvegarder" icon="o-check" class="btn-primary" wire:click="saveVente" />
        </x-slot:actions>
    </x-mary-card>
    @endif
</div>
