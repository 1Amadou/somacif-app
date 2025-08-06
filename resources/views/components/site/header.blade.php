{{-- Le x-data initialise Alpine.js pour ce composant. "open" est notre variable pour l'état du menu mobile. --}}
<header x-data="{ open: false }" id="header" class="sticky-header">
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
                <div class="hidden lg:flex items-center space-x-4 text-sm uppercase tracking-wider">
                    <a href="{{ route('client.dashboard') }}" class="text-white font-semibold hover:text-primary">Mon Compte</a>
                    <span class="text-slate-600">|</span>
                    <form method="POST" action="{{ route('client.logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-300 hover:text-white">Déconnexion</button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="hidden lg:block btn btn-primary">
                    Passer Commande
                </a>
            @endif

            {{-- Bouton pour le menu mobile --}}
            {{-- @click="open = !open" inverse la valeur de "open" (vrai/faux) à chaque clic --}}
            <button class="lg:hidden text-white z-50" @click="open = !open">
                <i x-show="!open" class="fas fa-bars text-2xl"></i>
                <i x-show="open" class="fas fa-times text-2xl"></i>
            </button>
        </div>
    </nav>

    {{-- x-show="open" affiche ou cache ce bloc. x-transition ajoute une animation. --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translateY-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translateY-0"
         x-transition:leave-end="opacity-0 transform -translate-y-4"
         class="lg:hidden absolute top-full left-0 w-full bg-black/95 backdrop-blur-md border-t border-border-dark"
         @click.away="open = false" {{-- Permet de fermer le menu en cliquant à l'extérieur --}}
         style="display: none;">
        
        @if(!empty($siteHeader) && !empty($siteHeader->contenus['menu_items']))
            @foreach($siteHeader->contenus['menu_items'] as $item)
                <a href="{{ url($item['url']) }}" class="block py-4 px-6 text-white text-center hover:bg-slate-800 {{ request()->is(ltrim($item['url'], '/')) ? 'text-primary font-bold' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        @endif

        {{-- On ajoute les actions spécifiques au mobile --}}
        <div class="p-6 border-t border-slate-800">
             @if($authenticatedClient)
                <a href="{{ route('client.dashboard') }}" class="block w-full text-center bg-slate-700 hover:bg-slate-600 text-white font-bold tracking-widest uppercase transition duration-300 py-3 rounded-sm mb-3">
                    Mon Compte
                </a>
             @else
                <a href="{{ route('login') }}" class="block w-full text-center bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 py-3 rounded-sm">
                    Passer Commande
                </a>
             @endif
        </div>
    </div>
</header>