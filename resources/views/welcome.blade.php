<x-layouts.app>
    @php
        $heroGalleryUrls = [];
        if (!empty($page->images['hero_gallery']) && is_array($page->images['hero_gallery'])) {
            foreach ($page->images['hero_gallery'] as $imagePath) {
                $heroGalleryUrls[] = Storage::url($imagePath);
            }
        }
    @endphp

    <section class="min-h-[85vh] relative flex flex-col justify-center items-center text-center py-20 overflow-hidden">
        <div class="swiper hero-swiper absolute inset-0 w-full h-full">
            <div class="swiper-wrapper">
                @forelse($heroGalleryUrls as $url)
                    <div class="swiper-slide">
                        <div class="absolute inset-0 w-full h-full bg-cover bg-center" style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 10%, rgba(10, 10, 10, 0.6) 70%), url('{{ $url }}')"></div>
                    </div>
                @empty
                    <div class="swiper-slide">
                        <div class="absolute inset-0 w-full h-full bg-cover bg-center" style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 10%, rgba(10, 10, 10, 0.6) 70%), url('https://placehold.co/1920x1080/111827/FFFFFF?text=Image+de+fond')"></div>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="container mx-auto px-6 relative z-10">
            <h1 class="text-6xl md:text-8xl lg:text-9xl font-bold uppercase text-white leading-none">
                {!! $page->titres['hero_title'] ?? '' !!}
            </h1>
            <p class="text-lg md:text-2xl text-slate-200 mt-6 mb-12 max-w-3xl mx-auto font-semibold">
                {{ $page->contenus['hero_subtitle'] ?? '' }}
            </p>
            <div class="flex justify-center items-center space-x-4">
                <a href="#produits" class="btn btn-primary py-4 px-10">Voir nos produits phares</a>
            </div>
        </div>
    </section>

    <section id="produits" class="py-24 bg-black fade-in-section">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-sm uppercase tracking-widest brand-red font-semibold">{{ $page->contenus['products_subtitle'] ?? '' }}</span>
                <h2 class="text-5xl md:text-6xl uppercase text-white mt-2">{{ $page->titres['products_title'] ?? '' }}</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @forelse($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-slate-400">Aucun produit phare à afficher pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="clients" class="py-24 fade-in-section">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-sm uppercase tracking-widest brand-red font-semibold">{{ $page->contenus['clients_subtitle'] ?? '' }}</span>
                <h2 class="text-5xl md:text-6xl uppercase text-white mt-2">{{ $page->titres['clients_title'] ?? '' }}</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <a href="{{ route('grossistes') }}" class="relative client-card rounded-lg overflow-hidden h-96 block group" style="background-image: linear-gradient(to top, rgba(0,0,0,0.9) 20%, transparent 60%), url('{{ !empty($page->images['clients_grossistes_bg']) ? Storage::url($page->images['clients_grossistes_bg']) : 'https://placehold.co/600x800/1E40AF/FFFFFF?text=Palette+de+cartons' }}');">
                    <div class="absolute bottom-0 left-0 p-8">
                        <h3 class="text-4xl text-white font-bold uppercase">{{ $page->contenus['clients_grossistes_title'] ?? '' }}</h3>
                        <p class="text-slate-300 mt-2">{{ $page->contenus['clients_grossistes_text'] ?? '' }}</p>
                        <div class="mt-4 text-brand-red font-bold opacity-0 group-hover:opacity-100 transition-opacity">En savoir plus <i class="fas fa-arrow-right"></i></div>
                    </div>
                </a>
                <a href="{{ route('hotels-restaurants') }}" class="relative client-card rounded-lg overflow-hidden h-96 block group" style="background-image: linear-gradient(to top, rgba(0,0,0,0.9) 20%, transparent 60%), url('{{ !empty($page->images['clients_hr_bg']) ? Storage::url($page->images['clients_hr_bg']) : 'https://placehold.co/600x800/374151/FFFFFF?text=Chef+en+cuisine' }}');">
                    <div class="absolute bottom-0 left-0 p-8">
                        <h3 class="text-4xl text-white font-bold uppercase">{{ $page->contenus['clients_hr_title'] ?? '' }}</h3>
                        <p class="text-slate-300 mt-2">{{ $page->contenus['clients_hr_text'] ?? '' }}</p>
                        <div class="mt-4 text-brand-red font-bold opacity-0 group-hover:opacity-100 transition-opacity">Découvrir nos solutions <i class="fas fa-arrow-right"></i></div>
                    </div>
                </a>
                <a href="{{ route('particuliers') }}" class="relative client-card rounded-lg overflow-hidden h-96 block group" style="background-image: linear-gradient(to top, rgba(0,0,0,0.9) 20%, transparent 60%), url('{{ !empty($page->images['clients_particuliers_bg']) ? Storage::url($page->images['clients_particuliers_bg']) : 'https://placehold.co/600x800/78350F/FFFFFF?text=Plat+familial' }}');">
                    <div class="absolute bottom-0 left-0 p-8">
                        <h3 class="text-4xl text-white font-bold uppercase">{{ $page->contenus['clients_particuliers_title'] ?? '' }}</h3>
                        <p class="text-slate-300 mt-2">{{ $page->contenus['clients_particuliers_text'] ?? '' }}</p>
                        <div class="mt-4 text-brand-red font-bold opacity-0 group-hover:opacity-100 transition-opacity">Trouver un point de vente <i class="fas fa-arrow-right"></i></div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <section id="societe" class="py-24 bg-black fade-in-section">
        <div class="container mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="text-sm uppercase tracking-widest brand-red font-semibold">{{ $page->contenus['infra_subtitle'] ?? '' }}</span>
                    <h2 class="text-5xl md:text-6xl uppercase text-white mt-2 mb-6">{{ $page->titres['infra_title'] ?? '' }}</h2>
                    <p class="text-slate-400 mb-8">{{ $page->contenus['infra_text'] ?? '' }}</p>
                    <a href="{{ route('societe') }}" class="btn btn-secondary py-3 px-8">Découvrir notre société</a>
                </div>
                <div class="h-full min-h-[300px] rounded-lg overflow-hidden">
                    <img src="{{ !empty($page->images['infra_image']) ? Storage::url($page->images['infra_image']) : 'https://placehold.co/600x500/171717/D32F2F?text=Intérieur+Chambre+Froide' }}" alt="Image de l'intérieur d'une chambre froide SOMACIF" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

    <section id="actualites" class="py-24 fade-in-section">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-sm uppercase tracking-widest brand-red font-semibold">{{ $page->contenus['news_subtitle'] ?? '' }}</span>
                <h2 class="text-5xl md:text-6xl uppercase text-white mt-2">{{ $page->titres['news_title'] ?? '' }}</h2>
            </div>
            <div class="grid lg:grid-cols-2 gap-8 max-w-4xl mx-auto">
                @forelse($latestPosts as $post)
                    <a href="{{ route('posts.show', $post) }}" class="block bg-slate-900/50 p-8 rounded-lg border border-slate-800 hover:border-slate-700 transition-colors">
                        <p class="text-sm text-slate-500 mb-2">{{ $post->date_publication->format('d F Y') }}</p>
                        <h3 class="text-2xl font-bold text-white mb-3">{{ $post->titre }}</h3>
                        <p class="text-slate-400 text-sm mb-4">{{ Str::limit(strip_tags($post->contenu), 150) }}</p>
                        <span class="font-bold text-sm brand-red hover:text-red-400 transition-colors">Lire la suite <i class="fas fa-arrow-right ml-1"></i></span>
                    </a>
                @empty
                    <p class="text-slate-400 col-span-full text-center">Aucune actualité à afficher pour le moment.</p>
                @endforelse
            </div>
            <div class="text-center mt-12">
                <a href="{{ route('actualites.index') }}" class="btn btn-secondary py-3 px-8">Toutes nos actualités</a>
            </div>
        </div>
    </section>

    <section id="points-vente" class="py-24 bg-black fade-in-section">
        <div class="container mx-auto px-6 text-center">
            <span class="text-sm uppercase tracking-widest brand-red font-semibold">{{ $page->contenus['pos_subtitle'] ?? '' }}</span>
            <h2 class="text-5xl md:text-6xl uppercase text-white mt-2 mb-6">{{ $page->titres['pos_title'] ?? '' }}</h2>
            <p class="text-slate-400 max-w-2xl mx-auto mb-10">{{ $page->contenus['pos_text'] ?? '' }}</p>
            <div class="max-w-4xl mx-auto rounded-lg overflow-hidden border-2 border-slate-800 mb-10">
                <img src="{{ !empty($page->images['pos_map_image']) ? Storage::url($page->images['pos_map_image']) : 'https://placehold.co/1000x400/0A0A0A/D32F2F?text=Carte+de+nos+points+de+vente+à+Bamako' }}" alt="Carte de Bamako" class="w-full h-full object-cover">
            </div>
            <a href="{{ route('points-de-vente') }}" class="btn btn-primary py-4 px-10">Voir tous nos points de vente</a>
        </div>
    </section>
</x-layouts.app>