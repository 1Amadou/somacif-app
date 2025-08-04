<x-layouts.app>
    @slot('metaTitle', $post->meta_titre ?? $post->titre . ' - SOMACIF')
    @slot('metaDescription', $post->meta_description ?? Str::limit(strip_tags($post->contenu), 160))

    <section class="page-header-bg py-24 md:py-32 bg-cover bg-center"
             style="background-image: linear-gradient(to top, rgba(10, 10, 10, 1) 5%, rgba(10, 10, 10, 0.7) 100%), url('{{ $post->image ? Storage::url($post->image) : 'https://placehold.co/1920x600/111827/FFFFFF?text=Actualités' }}')">
        <div class="container mx-auto px-6 text-center">
            <a href="{{ route('actualites.index') }}" class="text-sm uppercase tracking-widest brand-red font-semibold">{{ $post->category->nom }}</a>
            <h1 class="text-4xl md:text-6xl font-bold uppercase text-white mt-2">{{ $post->titre }}</h1>
            <div class="text-lg text-slate-300 mt-4">
                Publié le {{ $post->date_publication->format('d F Y') }}
            </div>
        </div>
    </section>

    <section class="py-24">
        <div class="container mx-auto px-6">
            <div class="grid lg:grid-cols-3 gap-12">
                
                <div class="lg:col-span-2">
                    <div class="prose prose-invert prose-lg max-w-none text-slate-300 prose-headings:font-teko prose-headings:text-white prose-a:text-primary hover:prose-a:text-red-400">
                        {!! $post->contenu !!}
                    </div>
                </div>

                <aside class="lg:col-span-1">
                    <div class="bg-dark-card p-8 rounded-lg border border-border-dark sticky top-32">
                        <h3 class="text-2xl font-teko uppercase text-white mb-6">Articles Récents</h3>
                        <div class="space-y-6">
                            @forelse($recentPosts as $recentPost)
                                <a href="{{ route('posts.show', $recentPost) }}" class="block group">
                                    <p class="text-lg font-bold text-white group-hover:text-primary transition-colors leading-tight">{{ $recentPost->titre }}</p>
                                    <p class="text-sm text-slate-500">{{ $recentPost->date_publication->format('d F Y') }}</p>
                                </a>
                            @empty
                                <p class="text-slate-400">Aucun autre article à afficher.</p>
                            @endforelse
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </section>
</x-layouts.app>