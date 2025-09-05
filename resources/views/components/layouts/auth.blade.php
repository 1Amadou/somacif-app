<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Connexion - ' . config('app.name', 'SOMACIF') }}</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Teko:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @livewireStyles
</head>
<body class="antialiased bg-dark-main text-dark-text">

    {{-- Affichage des notifications Filament --}}
    @livewire('notifications') 

    <main>
        {{-- Le contenu de votre page de connexion sera inséré ici --}}
        {{ $slot }}
    </main>
    
    @livewireScripts
</body>
</html>