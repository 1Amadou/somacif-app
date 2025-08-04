<x-layouts.app>
    @slot('metaTitle', $page->titres['header_title'] ?? '')
    <section class="py-24">
        <div class="container mx-auto px-6">
            <h1 class="text-5xl font-teko uppercase text-white mb-8">{{ $page->titres['header_title'] ?? '' }}</h1>
            <div class="prose prose-invert max-w-none text-slate-300">
                {!! $page->contenus['main_content'] ?? '' !!}
            </div>
        </div>
    </section>
</x-layouts.app>