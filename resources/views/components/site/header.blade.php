<header id="header" class="sticky-header">
    <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
        {{-- Logo dynamique --}}
        <a href="{{ route('home') }}">
            @if(!empty($siteHeader) && !empty($siteHeader->images['logo']))
                <img src="{{ Storage::url($siteHeader->images['logo']) }}" alt="Logo SOMACIF" class="h-10 md:h-12">
            @else
                <div class="text-4xl tracking-wider font-teko">SOMA<span class="brand-red">CIF</span></div>
            @endif
        </a>

        {{-- Navigation principale (Desktop) --}}
        {{-- Dans la navigation principale du header.blade.php --}}
<div class="hidden lg:flex items-center space-x-10 text-sm uppercase tracking-wider">
    @if(!empty($siteHeader) && !empty($siteHeader->contenus['menu_items']))
        @foreach($siteHeader->contenus['menu_items'] as $item)
            <a href="{{ url($item['url']) }}" 
               class="{{ request()->is(ltrim($item['url'], '/')) ? 'text-primary font-bold' : 'text-slate-300' }} hover:text-primary transition-colors">
               {{ $item['label'] }}
            </a>
        @endforeach
    @endif
</div>

        {{-- Actions et état de connexion --}}
        <div class="flex items-center space-x-6">
            @if($authenticatedClient)
                {{-- Si le client est connecté --}}
                <div class="hidden lg:flex items-center space-x-4 text-sm uppercase tracking-wider">
                    <a href="{{ route('client.dashboard') }}" class="text-white font-bold hover:text-primary transition-colors">Mon Compte</a>
                    <span class="text-slate-600">|</span>
                    <livewire:auth.logout-client />
                </div>
            @else
                {{-- Si le client est déconnecté --}}
                <a href="{{ route('nos-offres') }}" class="hidden lg:block bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 transform hover:scale-105 py-2 px-6 rounded-sm">
                    Passer Commande
                </a>
            @endif

            {{-- Bouton pour le menu mobile --}}
            <button class="lg:hidden text-white" id="mobile-menu-button">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
    </nav>

    <div id="mobile-menu" class="hidden lg:hidden bg-black/95 backdrop-blur-md">
        @if(!empty($siteHeader) && !empty($siteHeader->contenus['menu_items']))
            @foreach($siteHeader->contenus['menu_items'] as $item)
                <a href="{{ url($item['url']) }}" class="block py-3 px-6 text-white hover:bg-slate-800">{{ $item['label'] }}</a>
            @endforeach
        @endif
    </div>
</header>