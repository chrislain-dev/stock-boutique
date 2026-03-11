<div class="w-full max-w-md">

    {{-- Logo / Nom boutique --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary mb-4">
            <span class="text-white text-2xl font-bold">
                {{ strtoupper(substr(Setting::get('boutique.nom', config('boutique.nom')), 0, 2)) }}
            </span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">
            {{ Setting::get('boutique.nom', config('boutique.nom')) }}
        </h1>
        <p class="text-gray-500 text-sm mt-1">Connectez-vous à votre espace</p>
    </div>

    {{-- Formulaire --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

        <x-mary-form wire:submit="login">

            <x-mary-input
                label="Adresse email"
                wire:model="email"
                type="email"
                placeholder="admin@techshop.bj"
                icon="o-envelope"
                autofocus
            />

            <x-mary-input
                label="Mot de passe"
                wire:model="password"
                type="password"
                placeholder="••••••••"
                icon="o-lock-closed"
            />

            <x-mary-checkbox
                label="Se souvenir de moi"
                wire:model="remember"
            />

            <x-slot:actions>
                <x-mary-button
                    label="Se connecter"
                    type="submit"
                    class="btn-primary w-full"
                    spinner="login"
                />
            </x-slot:actions>

        </x-mary-form>

    </div>

    <p class="text-center text-xs text-gray-400 mt-6">
        {{ Setting::get('boutique.nom', config('boutique.nom')) }} &copy; {{ date('Y') }}
    </p>

</div>
