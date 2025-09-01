<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $metaTitle ?? 'SOMACIF - N°1 du Poisson Congelé au Mali' }}</title>
    
    {{-- On garde le CDN pour Tailwind, car c'est votre configuration actuelle --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    
    {{-- Polices, Icônes & Styles Externes --}}
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Teko:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    {{-- Vos styles personnalisés --}}
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    {{-- CORRECTION 1 : On charge les styles de Livewire --}}
    @livewireStyles
</head>
<body class="antialiased bg-dark-main text-dark-text">

    <x-site.header />

    {{-- Le composant panier est appelé une seule fois --}}
    <livewire:shopping-cart />

    <main>
        {{ $slot }}
    </main>

    <x-site.footer />

    {{-- CORRECTION 2 : Le script Alpine.js externe a été SUPPRIMÉ --}}
    {{-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    
    {{-- CORRECTION 3 : On charge les scripts de Livewire, qui incluent DÉJÀ Alpine.js --}}
    @livewireScripts

    {{-- Vos autres scripts externes ou personnalisés --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    @stack('scripts')
</body>
</html>