<x-layouts.app>
    @slot('metaTitle', $page->meta_titre ?? $page->titres['header_title'])
    @slot('metaDescription', $page->meta_description ?? $page->contenus['header_subtitle'])

    <main class="page-particuliers">
        <section class="page-header-bg py-24 md:py-32 bg-cover bg-center"
                 style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 5%, rgba(10, 10, 10, 0.7) 100%), url('{{ !empty($page->images['header_background']) ? Storage::url($page->images['header_background']) : 'https://placehold.co/1920x600/111827/FFFFFF?text=Famille' }}')">
            <div class="container mx-auto px-6 text-center">
                <h1 class="text-6xl md:text-8xl font-bold uppercase text-white">{{ $page->titres['header_title'] }}</h1>
                <p class="text-xl md:text-2xl text-slate-300 mt-4">{{ $page->contenus['header_subtitle'] ?? '' }}</p>
            </div>
        </section>

        <section class="py-24">
            <div class="container mx-auto px-6 grid lg:grid-cols-2 gap-12 items-center">
                <div class="h-[500px] rounded-lg overflow-hidden">
                    <img src="{{ !empty($page->images['presentation_image']) ? Storage::url($page->images['presentation_image']) : 'https://placehold.co/600x800/1E40AF/FFFFFF?text=Produits+de+qualité' }}"
                         alt="Image de présentation pour particuliers" class="w-full h-full object-cover">
                </div>
                <div>
                    <span class="text-sm uppercase tracking-widest brand-red font-semibold">Notre Offre</span>
                    <h2 class="text-5xl md:text-6xl uppercase text-white mt-2 mb-6">{{ $page->titres['presentation_title'] }}</h2>
                    <p class="text-slate-400 mb-8">{{ $page->contenus['presentation_text'] ?? '' }}</p>
                </div>
            </div>
        </section>

        <section class="py-24 bg-gray-900/50">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-5xl md:text-6xl uppercase text-white mb-12">{{ $page->titres['services_title'] }}</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    @foreach($page->contenus['services'] as $service)
                    <div class="bg-gray-800 p-8 rounded-lg shadow-lg">
                        <i class="{{ $service['icon'] }} fa-3x mb-4 brand-red"></i>
                        <h3 class="text-2xl font-semibold text-white mb-2">{{ $service['title'] }}</h3>
                        <p class="text-slate-400">{{ $service['description'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="py-24">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-5xl md:text-6xl uppercase text-white mb-12">{{ $page->titres['how_it_works_title'] }}</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    @foreach($page->contenus['how_it_works_steps'] as $index => $step)
                    <div>
                        <div class="w-16 h-16 mx-auto mb-4 bg-brand-red rounded-full flex items-center justify-center text-white text-3xl font-bold">
                            {{ $index + 1 }}
                        </div>
                        <h3 class="text-2xl font-semibold text-white mb-2">{{ $step['title'] }}</h3>
                        <p class="text-slate-400">{{ $step['description'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        
        <section class="py-24 bg-gray-900/50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-5xl md:text-6xl uppercase text-white">{{ $page->titres['form_title'] }}</h2>
                    <p class="text-slate-400 max-w-2xl mx-auto mt-4">{{ $page->contenus['form_subtitle'] }}</p>
                </div>
                <div class="max-w-4xl mx-auto">
                    <livewire:partner-application-form />
                </div>
            </div>
        </section>
    </main>
</x-layouts.app>