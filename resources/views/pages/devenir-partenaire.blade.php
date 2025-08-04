<x-layouts.app>
    @slot('metaTitle', "Devenir Partenaire - SOMACIF")

    <section class="page-header-bg py-24 md:py-32 bg-cover bg-center" 
             style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 5%, rgba(10, 10, 10, 0.7) 100%), url('{{ !empty($page->images['header_background']) ? Storage::url($page->images['header_background']) : 'https://placehold.co/1920x600/111827/FFFFFF?text=Chef+en+cuisine' }}')">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-6xl md:text-8xl font-bold uppercase text-white">{{ $page->titres['header_title'] ?? 'Solutions Professionnelles' }}</h1>
        </div>
    </section>
    
    <section class="py-24">
        <div class="container mx-auto px-6 space-y-24">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="text-sm uppercase tracking-widest brand-red font-semibold">{{ $page->contenus['offer_hr_subtitle'] ?? '' }}</span>
                    <h2 class="text-5xl md:text-6xl uppercase text-white mt-2 mb-6">{{ $page->titres['offer_hr_title'] ?? '' }}</h2>
                    <p class="text-slate-400 mb-8">{{ $page->contenus['offer_hr_text'] ?? '' }}</p>
                </div>
                <div class="h-[500px] rounded-lg overflow-hidden">
                    <img src="{{ !empty($page->images['offer_hr_image']) ? Storage::url($page->images['offer_hr_image']) : 'https://placehold.co/600x800/374151/FFFFFF?text=Plat+gastronomique' }}" alt="Plat de poisson gastronomique" class="w-full h-full object-cover">
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="lg:order-2">
                    <span class="text-sm uppercase tracking-widest brand-red font-semibold">{{ $page->contenus['offer_gros_subtitle'] ?? '' }}</span>
                    <h2 class="text-5xl md:text-6xl uppercase text-white mt-2 mb-6">{{ $page->titres['offer_gros_title'] ?? '' }}</h2>
                    <p class="text-slate-400 mb-8">{{ $page->contenus['offer_gros_text'] ?? '' }}</p>
                </div>
                <div class="lg:order-1 h-[500px] rounded-lg overflow-hidden">
                    <img src="{{ !empty($page->images['offer_gros_image']) ? Storage::url($page->images['offer_gros_image']) : 'https://placehold.co/600x800/1E40AF/FFFFFF?text=Chargement+de+camion' }}" alt="Chargement d'un camion de livraison SOMACIF" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

    {{-- CONDITION AJOUTÉE ICI --}}
    @if(!$authenticatedClient)
        <section class="py-24 bg-gray-900/50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-5xl md:text-6xl uppercase text-white">{{ $page->titres['form_title'] ?? 'Devenez Partenaire' }}</h2>
                    <p class="text-slate-400 max-w-2xl mx-auto mt-4">{{ $page->contenus['form_subtitle'] ?? 'Remplissez ce formulaire pour être contacté par notre équipe commerciale.' }}</p>
                </div>
                <div class="max-w-4xl mx-auto">
                    <livewire:partner-application-form />
                </div>
            </div>
        </section>
    @endif

</x-layouts.app>