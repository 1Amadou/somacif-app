<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $metaTitle ?? 'SOMACIF - N°1 du Poisson Congelé au Mali' }}</title>
    
    {{-- On charge Tailwind via le CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- On charge les polices et les icônes --}}
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Teko:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- On charge notre propre fichier de style --}}
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>
<body class="antialiased">

    <x-site.header />

    <main>
        {{ $slot }}
    </main>

    <x-site.footer />
    <livewire:shopping-cart />
    {{-- <x-cookie-consent-banner /> --}}

    {{-- On charge notre propre fichier JavaScript à la fin --}}
    <script src="{{ asset('js/custom.js') }}"></script>
</body>
</html>