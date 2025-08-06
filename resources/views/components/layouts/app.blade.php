<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $metaTitle ?? 'SOMACIF - N°1 du Poisson Congelé au Mali' }}</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Teko:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- On charge les styles de Swiper.js --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    {{-- On charge Alpine.js pour l'interactivité du header --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased bg-dark-main text-dark-text">

    <x-site.header />

    <main>
        {{ $slot }}
    </main>

    <x-site.footer />

    <livewire:shopping-cart />

    {{-- On charge le script de Swiper.js --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    {{-- On charge notre script personnalisé --}}
    <script src="{{ asset('js/custom.js') }}"></script>
    @stack('scripts')
</body>
</html>