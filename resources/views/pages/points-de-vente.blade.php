<x-layouts.app>
    @slot('metaTitle', $page->meta_titre ?? 'Nos Points de Vente - SOMACIF')

    <section class="page-header-bg py-24 md:py-32 bg-cover bg-center"
             style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 5%, rgba(10, 10, 10, 0.7) 100%), url('{{ !empty($page->images['header_background']) ? Storage::url($page->images['header_background']) : 'https://placehold.co/1920x600/111827/FFFFFF?text=Carte+de+Bamako' }}')">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-6xl md:text-8xl font-bold uppercase text-white">{{ $page->titres['header_title'] ?? '' }}</h1>
            <p class="text-xl text-slate-300 mt-2">{{ $page->contenus['header_subtitle'] ?? '' }}</p>
        </div>
    </section>

    <section class="py-24">
        <div class="container mx-auto px-6">
            <div class="mb-16 rounded-lg overflow-hidden border-2 border-slate-800">
                <a href="#">
                    {{-- Plus tard, nous remplacerons ceci par une vraie carte interactive --}}
                    <img src="https://placehold.co/1200x500/0A0A0A/D32F2F?text=Carte+interactive+de+nos+points+de+vente" alt="Carte des points de vente SOMACIF à Bamako" class="w-full h-full object-cover">
                </a>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($pointsDeVente as $pointDeVente)
                    <div class="location-card rounded-lg p-8">
                        <h3 class="text-3xl text-white mb-2 font-teko">{{ $pointDeVente->nom }}</h3>
                        <p class="brand-red font-bold mb-4">{{ $pointDeVente->type }}</p>
                        <div class="space-y-3 text-slate-300">
                            <p class="flex items-start"><i class="fas fa-map-marker-alt w-5 mt-1 mr-3 text-slate-500"></i><span>{{ $pointDeVente->adresse }}</span></p>
                            @if($pointDeVente->telephone)
                            <p class="flex items-center"><i class="fas fa-phone w-5 mr-3 text-slate-500"></i><span>{{ $pointDeVente->telephone }}</span></p>
                            @endif
                            @if($pointDeVente->horaires)
                            <p class="flex items-center"><i class="fas fa-clock w-5 mr-3 text-slate-500"></i><span>{{ $pointDeVente->horaires }}</span></p>
                            @endif
                        </div>
                        @if($pointDeVente->Maps_link)
                        <a href="{{ $pointDeVente->Maps_link }}" target="_blank" class="inline-block mt-6 bg-slate-800 hover:bg-brand-red text-white font-bold text-sm uppercase tracking-wider py-3 px-5 rounded-md transition-colors">
                            Itinéraire <i class="fas fa-directions ml-2"></i>
                        </a>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-slate-400">Aucun point de vente n'a été ajouté pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.app>