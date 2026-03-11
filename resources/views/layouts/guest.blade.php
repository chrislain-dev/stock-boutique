<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — {{ Setting::get('boutique.nom', config('boutique.nom')) }}</title>
    <x-theme-vars />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">

    {{ $slot }}

    @livewireScripts
</body>
</html>
