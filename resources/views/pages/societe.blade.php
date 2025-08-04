<x-layouts.app>
    @slot('metaTitle', $page->meta_titre ?? 'Notre Société - SOMACIF')

    <section class="page-header-bg py-24 md:py-32 bg-cover bg-center"
        style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 5%, rgba(10, 10, 10, 0.7) 100%), url('{{ !empty($page->images['header_background']) ? Storage::url($page->images['header_background']) : 'https://placehold.co/1920x600/111827/FFFFFF?text=Bâtiment+SOMACIF' }}')">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-6xl md:text-8xl font-bold uppercase text-white">{{ $page->titres['header_title'] ?? '' }}</h1>
            <p class="text-xl text-slate-300 mt-2">{{ $page->contenus['header_subtitle'] ?? '' }}</p>
        </div>
    </section>

    <section class="py-24 bg-black">
        <div class="container mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="order-2 lg:order-1">
                    <span class="text-sm uppercase tracking-widest brand-red font-semibold">{{ $page->contenus['history_subtitle'] ?? '' }}</span>
                    <h2 class="text-5xl md:text-6xl uppercase text-white mt-2 mb-6">{{ $page->titres['history_title'] ?? '' }}</h2>
                    <div class="text-slate-400 space-y-4 prose prose-invert max-w-none">
                        {!! $page->contenus['history_text'] ?? '' !!}
                    </div>
                </div>
                <div class="order-1 lg:order-2 h-full min-h-[400px]">
                    <img src="{{ !empty($page->images['history_image']) ? Storage::url($page->images['history_image']) : 'https://placehold.co/600x700/171717/D32F2F?text=Portrait' }}" alt="Portrait du fondateur de SOMACIF" class="w-full h-full object-cover rounded-lg">
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 bg-gray-900/50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-sm uppercase tracking-widest brand-red font-semibold">{{ $page->contenus['infra_subtitle'] ?? '' }}</span>
                <h2 class="text-5xl md:text-6xl uppercase text-white mt-2">{{ $page->titres['infra_title'] ?? '' }}</h2>
                <p class="text-slate-400 max-w-3xl mx-auto mt-4">{{ $page->contenus['infra_text'] ?? '' }}</p>
            </div>
            <div class="grid lg:grid-cols-2 gap-8 items-center">
                {{-- La galerie est maintenant dynamique --}}
                <div class="grid grid-cols-2 gap-4">
                    @if(!empty($page->images['infra_gallery']) && is_array($page->images['infra_gallery']))
                        @foreach(collect($page->images['infra_gallery'])->take(2) as $imagePath)
                            <img src="{{ Storage::url($imagePath) }}" alt="Image d'infrastructure SOMACIF" class="rounded-lg object-cover w-full h-full min-h-[400px]">
                        @endforeach
                    @else
                        {{-- Images par défaut si la galerie est vide --}}
                        <img src="https://placehold.co/400x500/171717/FFFFFF?text=Chambre+Froide+1" alt="Chambre froide SOMACIF" class="rounded-lg object-cover w-full h-full">
                        <img src="https://placehold.co/400x500/171717/FFFFFF?text=Zone+de+chargement" alt="Zone de chargement" class="rounded-lg object-cover w-full h-full">
                    @endif
                </div>
                <div>
                    {{-- Boucle dynamique pour les statistiques --}}
                    <div class="grid sm:grid-cols-2 gap-6">
                        @if(!empty($page->contenus['stats']) && is_array($page->contenus['stats']))
                            @foreach($page->contenus['stats'] as $stat)
                                <div class="stat-card p-6 rounded-lg text-center">
                                    <span class="text-5xl font-teko brand-red">{{ $stat['stat'] ?? '' }}</span>
                                    <p class="text-white mt-1">{{ $stat['label'] ?? '' }}</p>
                                </div>
                            @endforeach
                        @else
                            {{-- Stats par défaut si le contenu est vide --}}
                            <div class="stat-card p-6 rounded-lg text-center"><span class="text-5xl font-teko brand-red">5000+</span><p class="text-white mt-1">Tonnes de Stockage</p></div>
                            <div class="stat-card p-6 rounded-lg text-center"><span class="text-5xl font-teko brand-red">-20°C</span><p class="text-white mt-1">Température Contrôlée</p></div>
                            <div class="stat-card p-6 rounded-lg text-center"><span class="text-5xl font-teko brand-red">24/7</span><p class="text-white mt-1">Surveillance Continue</p></div>
                            <div class="stat-card p-6 rounded-lg text-center"><span class="text-5xl font-teko brand-red">100%</span><p class="text-white mt-1">Chaîne du Froid Maîtrisée</p></div>
                        @endif
                    </div>
                    <p class="text-slate-400 mt-8">{{ $page->contenus['infra_conclusion'] ?? '' }}</p>
                </div>
            </div>
        </div>
    </section>
        
    <section class="py-24 bg-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-sm uppercase tracking-widest brand-red font-semibold">{{ $page->contenus['commitments_subtitle'] ?? '' }}</span>
                <h2 class="text-5xl md:text-6xl uppercase text-white mt-2">{{ $page->titres['commitments_title'] ?? '' }}</h2>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-6xl mx-auto">
                @if(!empty($page->contenus['engagements']) && is_array($page->contenus['engagements']))
                    @foreach($page->contenus['engagements'] as $engagement)
                        <div class="engagement-card p-8 text-center">
                            <i class="{{ $engagement['icon'] ?? '' }} brand-red text-5xl mb-4"></i>
                            <h3 class="text-2xl text-white mb-2 font-teko">{{ $engagement['title'] ?? '' }}</h3>
                            <p class="text-slate-400 text-sm">{{ $engagement['description'] ?? '' }}</p>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>

    @if(!$authenticatedClient || ($authenticatedClient && $authenticatedClient->status !== 'approved'))
    <section class="py-24">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-5xl md:text-6xl uppercase text-white">{{ $page->titres['partner_cta_title'] ?? '' }}</h2>
                <p class="text-slate-400 max-w-2xl mx-auto mt-4">{{ $page->contenus['partner_cta_subtitle'] ?? '' }}</p>
            </div>
            <div class="max-w-4xl mx-auto">
                <div class="text-center">
                    <a href="{{ route('devenir-partenaire') }}" class="bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 transform hover:scale-105 py-4 px-12 rounded-sm">
                        Remplir le formulaire de demande
                    </a>
                </div>
            </div>
        </div>
    </section>
    @endif
</x-layouts.app>