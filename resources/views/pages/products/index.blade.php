<x-layouts.app>
    @slot('metaTitle', $page->meta_titre ?? 'Notre Catalogue - SOMACIF')

    @if($authenticatedClient)
        
        {{-- ======================== VUE POUR CLIENT CONNECTÉ ======================== --}}
        <section class="page-header-bg py-24 md:py-32 bg-cover bg-center" style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 5%, rgba(10, 10, 10, 0.7) 100%), url('{{ !empty($page->images['header_background']) ? Storage::url($page->images['header_background']) : '' }}')">
            <div class="container mx-auto px-6 text-center">
                <h1 class="text-6xl md:text-8xl font-bold uppercase text-white">Passer une Commande</h1>
                <p class="text-xl text-slate-300 mt-2">Bienvenue, {{ $authenticatedClient->nom }}.</p>
            </div>
        </section>
        <section class="py-24">
            <div class="container mx-auto px-6">
                <livewire:product-catalog />
            </div>
        </section>

    @else

        {{-- ======================== VUE POUR VISITEUR PUBLIC ======================== --}}
        @php
            $visitorPage = \App\Models\Page::where('slug', 'catalogue-visiteur')->first();
            $sliderImages = [];
            if ($visitorPage && !empty($visitorPage->images['slider_gallery'])) {
                foreach ($visitorPage->images['slider_gallery'] as $imagePath) {
                    $sliderImages[] = Storage::url($imagePath);
                }
            }
        @endphp

        <section class="page-header-bg py-24 md:py-32 bg-cover bg-center" style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 5%, rgba(10, 10, 10, 0.7) 100%), url('{{ !empty($page->images['header_background']) ? Storage::url($page->images['header_background']) : '' }}')">
            <div class="container mx-auto px-6 text-center">
                <h1 class="text-6xl md:text-8xl font-bold uppercase text-white">{{ $page->titres['header_title'] ?? 'Notre Catalogue' }}</h1>
                <p class="text-xl text-slate-300 mt-2">{{ $page->contenus['header_subtitle'] ?? 'Une sélection rigoureuse des meilleurs produits de la mer.' }}</p>
            </div>
        </section>

        <section class="py-24 bg-black fade-in-section">
            <div class="container mx-auto px-6">
                <h2 class="text-5xl md:text-6xl uppercase text-white text-center mb-12">{{ $visitorPage->titres['header_title'] ?? '' }}</h2>
                <div class="swiper product-showcase-swiper rounded-lg">
                    <div class="swiper-wrapper">
                        @forelse($sliderImages as $imageUrl)
                            <div class="swiper-slide"><img src="{{ $imageUrl }}" class="w-full h-96 object-cover" /></div>
                        @empty
                             <div class="swiper-slide"><img src="https://placehold.co/1200x500/171717/FFFFFF?text=Produits+SOMACIF" class="w-full h-96 object-cover" /></div>
                        @endforelse
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>
        
        <section id="produits-publics" class="py-24">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    @foreach($products as $product)
                        <x-public-product-card :product="$product" />
                    @endforeach
                </div>
                <div class="mt-16">
                    {{ $products->links() }}
                </div>
            </div>
        </section>

        <section class="py-24 bg-black fade-in-section">
             <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-5xl md:text-6xl uppercase text-white mt-2">{{ $visitorPage->titres['how_to_order_title'] ?? '' }}</h2>
                </div>
                <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                     @if(!empty($visitorPage->contenus['etapes_commande']))
                        @foreach($visitorPage->contenus['etapes_commande'] as $step)
                            <div class="text-center">
                                <div class="w-24 h-24 rounded-full bg-dark-card border-2 border-brand-red flex items-center justify-center text-4xl brand-red font-teko mx-auto mb-4">{{ $loop->iteration }}</div>
                                <h3 class="text-2xl text-white font-bold mb-2">{{ $step['titre'] ?? '' }}</h3>
                                <p class="text-slate-400 text-sm">{{ $step['description'] ?? '' }}</p>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </section>
        
        <section class="py-20 bg-gray-900/50">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-4xl md:text-5xl uppercase text-white mt-2">{{ $visitorPage->titres['login_title'] ?? '' }}</h2>
                <p class="text-slate-400 mt-4 mb-8 max-w-3xl mx-auto">{{ $visitorPage->contenus['login_subtitle'] ?? '' }}</p>
                <a href="{{ route('login') }}" class="btn btn-primary py-3 px-8">Portail de Connexion Partenaire</a>
            </div>
        </section>
    @endif
</x-layouts.app>











{{-- <x-layouts.app>
    @slot('metaTitle', $page->meta_titre ?? 'Notre Catalogue - SOMACIF')

    <section class="page-header-bg py-24 md:py-32 bg-cover bg-center"
             style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 5%, rgba(10, 10, 10, 0.7) 100%), url('{{ !empty($page->images['header_background']) ? Storage::url($page->images['header_background']) : 'https://placehold.co/1920x600/111827/FFFFFF?text=Variété+de+Poissons' }}')">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-6xl md:text-8xl font-bold uppercase text-white">{{ $page->titres['header_title'] ?? '' }}</h1>
            <p class="text-xl text-slate-300 mt-2">{{ $page->contenus['header_subtitle'] ?? '' }}</p>
        </div>
    </section>

    @if($authenticatedClient)
        <section class="py-24">
            <div class="container mx-auto px-6">
                <livewire:product-catalog />
            </div>
        </section>
    @else
        <section class="py-24">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    @foreach($products as $product)
                        <x-public-product-card :product="$product" />
                    @endforeach
                </div>
                <div class="mt-16">
                    {{ $products->links() }}
                </div>
            </div>
        </section>

        <section class="py-20 bg-gray-900/50">
            <div class="container mx-auto px-6 text-center">
                 <span class="text-sm uppercase tracking-widest brand-red font-semibold">Pour les Professionnels</span>
                <h2 class="text-4xl md:text-5xl uppercase text-white mt-2">Vous êtes un Restaurant ou un Revendeur ?</h2>
                <p class="text-slate-400 mt-4 mb-8 max-w-3xl mx-auto">Accédez à nos tarifs préférentiels, à notre catalogue complet et à notre système de commande en ligne en devenant partenaire SOMACIF.</p>
                <a href="{{ route('devenir-partenaire') }}" class="bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 transform hover:scale-105 py-3 px-8 rounded-sm">Devenir Partenaire</a>
            </div>
        </section>
    @endif

</x-layouts.app> --}}