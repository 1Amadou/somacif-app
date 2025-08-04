<x-layouts.app>
    @slot('metaTitle', $page->meta_titre ?? 'Actualités - SOMACIF')

    <section class="page-header-bg py-24 md:py-32 bg-cover bg-center"
             style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 5%, rgba(10, 10, 10, 0.7) 100%), url('{{ !empty($page->images['header_background']) ? Storage::url($page->images['header_background']) : 'https://placehold.co/1920x600/111827/FFFFFF?text=Journal+ou+Marché' }}')">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-6xl md:text-8xl font-bold uppercase text-white">{{ $page->titres['header_title'] ?? '' }}</h1>
            <p class="text-xl text-slate-300 mt-2">{{ $page->contenus['header_subtitle'] ?? '' }}</p>
        </div>
    </section>

    <section class="py-24">
        <div class="container mx-auto px-6">
            
            @if($categories->isNotEmpty())
            <div class="flex flex-wrap justify-center gap-3 md:gap-4 mb-16">
                <button class="category-filter-btn active">Tout voir</button>
                @foreach($categories as $category)
                    <button class="category-filter-btn">{{ $category->nom }}</button>
                @endforeach
            </div>
            @endif

            @if($posts->isNotEmpty())
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($posts as $post)
                        <a href="{{ route('posts.show', $post) }}" class="article-card rounded-lg overflow-hidden flex flex-col group">
                            <div class="overflow-hidden h-48">
                                <img src="{{ $post->image ? Storage::url($post->image) : 'https://placehold.co/400x300/171717/FFFFFF?text=Article' }}" alt="Image de l'article {{ $post->titre }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                            </div>
                            <div class="p-6 flex flex-col flex-grow">
                                <span class="text-xs uppercase tracking-widest brand-red font-semibold mb-2">{{ $post->category->nom }}</span>
                                <h3 class="text-2xl text-white mb-3 font-teko flex-grow group-hover:text-primary transition-colors">{{ $post->titre }}</h3>
                                <p class="text-sm text-slate-500">{{ $post->date_publication->format('d F Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-16">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-slate-400">Aucune actualité publiée pour le moment.</p>
                </div>
            @endif

        </div>
    </section>
</x-layouts.app>