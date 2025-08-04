<x-layouts.app>
    @slot('metaTitle', $page->meta_titre ?? 'Notre Catalogue - SOMACIF')

    <section class="page-header-bg py-24 md:py-32 bg-cover bg-center"
             style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 5%, rgba(10, 10, 10, 0.7) 100%), url('{{ !empty($page->images['header_background']) ? Storage::url($page->images['header_background']) : 'https://placehold.co/1920x600/111827/FFFFFF?text=Variété+de+Poissons' }}')">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-6xl md:text-8xl font-bold uppercase text-white">{{ $page->titres['header_title'] ?? '' }}</h1>
            <p class="text-xl text-slate-300 mt-2">{{ $page->contenus['header_subtitle'] ?? '' }}</p>
        </div>
    </section>

    <section class="py-24">
        <div class="container mx-auto px-6">
            {{-- On appelle simplement notre composant Livewire ici --}}
            <livewire:product-catalog />
        </div>
    </section>
</x-layouts.app>