<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — {{ Setting::get('boutique.nom', config('boutique.nom')) }}</title>
    <x-theme-vars />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')  {{-- ← ajouter ceci --}}
</head>
<body style="margin:0;padding:0;overflow:hidden;">
    {{ $slot }}
    @livewireScripts
</body>
</html>
