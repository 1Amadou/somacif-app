<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $metaTitle ?? 'Portail Livreur - SOMACIF' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Teko:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body class="antialiased bg-dark-main">
    <header class="bg-dark-card border-b border-border-dark">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="text-3xl tracking-wider font-teko">
                SOMA<span class="brand-red">CIF</span> <span class="text-slate-400">| Portail Livreur</span>
            </div>
            <div>
                @auth('livreur')
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">
                            <i class="fas fa-sign-out-alt mr-1"></i> Se Déconnecter
                        </button>
                    </form>
                @endauth
            </div>
        </nav>
    </header>
    <main>
        {{ $slot }}
    </main>
</body>
</html>