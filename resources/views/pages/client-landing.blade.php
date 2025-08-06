<x-layouts.app>
    @slot('metaTitle', $page->meta_titre ?? $page->titres['header_title'] ?? '')

    <section class="page-header-bg py-24 md:py-32 bg-cover bg-center" 
             style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 5%, rgba(10, 10, 10, 0.7) 100%), url('{{ !empty($page->images['header_background']) ? Storage::url($page->images['header_background']) : '' }}')">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-6xl md:text-8xl font-bold uppercase text-white">{{ $page->titres['header_title'] ?? '' }}</h1>
            <p class="text-xl text-slate-300 mt-2">{{ $page->contenus['header_subtitle'] ?? '' }}</p>
        </div>
    </section>

    <section class="py-24 bg-black">
        <div class="container mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="order-2 lg:order-1">
                    <h2 class="text-5xl md:text-6xl uppercase text-white mt-2 mb-6">{{ $page->titres['presentation_title'] ?? '' }}</h2>
                    <p class="text-slate-400">{{ $page->contenus['presentation_text'] ?? '' }}</p>
                </div>
                <div class="order-1 lg:order-2 h-[500px] rounded-lg overflow-hidden">
                    <img src="{{ !empty($page->images['presentation_image']) ? Storage::url($page->images['presentation_image']) : 'https://placehold.co/600x800/171717/FFFFFF?text=Illustration' }}" alt="Illustration Partenariat SOMACIF" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

    <section class="py-24">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-5xl md:text-6xl uppercase text-white mt-2">{{ $page->titres['services_title'] ?? '' }}</h2>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                @if(!empty($page->contenus['services']) && is_array($page->contenus['services']))
                    @foreach($page->contenus['services'] as $service)
                        <div class="engagement-card p-8 text-center">
                            <i class="{{ $service['icon'] ?? '' }} brand-red text-5xl mb-4"></i>
                            <h3 class="text-2xl text-white mb-2 font-teko">{{ $service['title'] ?? '' }}</h3>
                            <p class="text-slate-400 text-sm">{{ $service['description'] ?? '' }}</p>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>

    <section class="py-24 bg-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-5xl md:text-6xl uppercase text-white mt-2">{{ $page->titres['how_it_works_title'] ?? '' }}</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                 @if(!empty($page->contenus['how_it_works_steps']) && is_array($page->contenus['how_it_works_steps']))
                    @foreach($page->contenus['how_it_works_steps'] as $step)
                        <div class="text-center">
                            <div class="w-24 h-24 rounded-full bg-dark-card border-2 border-brand-red flex items-center justify-center text-4xl brand-red font-teko mx-auto mb-4">{{ $loop->iteration }}</div>
                            <h3 class="text-2xl text-white font-bold mb-2">{{ $step['title'] ?? '' }}</h3>
                            <p class="text-slate-400 text-sm">{{ $step['description'] ?? '' }}</p>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>
    
    @if(!$authenticatedClient)
        <section class="py-24 bg-gray-900/50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-5xl md:text-6xl uppercase text-white">{{ $page->titres['form_title'] ?? '' }}</h2>
                    <p class="text-slate-400 max-w-2xl mx-auto mt-4">{{ $page->contenus['form_subtitle'] ?? '' }}</p>
                </div>
                <div class="max-w-4xl mx-auto">
                    <livewire:partner-application-form />
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>