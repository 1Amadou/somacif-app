<x-layouts.app>
    @slot('metaTitle', $product->meta_titre ?? $product->nom . ' - SOMACIF')
    @slot('metaDescription', $product->meta_description ?? $product->description_courte)

    <main>
        <section class="py-16 md:py-24">
            <div class="container mx-auto px-6">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16">
                    <div>
                        <div class="main-image mb-4 rounded-lg overflow-hidden bg-neutral-800">
                            <img id="mainProductImage" src="{{ $product->image_principale ? Storage::url($product->image_principale) : 'https://placehold.co/600x500/171717/FFFFFF?text=Image+Produit' }}" alt="Image principale de {{ $product->nom }}" class="w-full h-full object-cover">
                        </div>
                        <div class="thumbnails grid grid-cols-5 gap-4">
                            @if($product->image_principale)
                                <img src="{{ Storage::url($product->image_principale) }}" alt="Thumbnail 1" class="product-gallery-thumbnail active rounded" onclick="changeImage(this)">
                            @endif
                            @if($product->images_galerie)
                                @foreach($product->images_galerie as $image)
                                    <img src="{{ Storage::url($image) }}" alt="Thumbnail supplémentaire" class="product-gallery-thumbnail rounded" onclick="changeImage(this)">
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div>
                        <h1 class="text-5xl md:text-7xl font-bold uppercase text-white leading-none">{{ $product->nom }}</h1>
                        <p class="text-lg text-slate-400 mt-4 mb-6">{{ $product->description_courte }}</p>
                        
                        <div class="border-y border-slate-800 py-6 space-y-4">
                            @if($product->origine)
                            <div class="flex items-center"><i class="fas fa-globe-africa brand-red w-6 text-center mr-4"></i><span class="text-slate-300"><strong class="text-white">Origine :</strong> {{ $product->origine }}</span></div>
                            @endif
                            @if($product->poids_moyen)
                            <div class="flex items-center"><i class="fas fa-weight-hanging brand-red w-6 text-center mr-4"></i><span class="text-slate-300"><strong class="text-white">Poids moyen :</strong> {{ $product->poids_moyen }}</span></div>
                            @endif
                            @if($product->conservation)
                            <div class="flex items-center"><i class="fas fa-snowflake brand-red w-6 text-center mr-4"></i><span class="text-slate-300"><strong class="text-white">Conservation :</strong> {{ $product->conservation }}</span></div>
                            @endif
                        </div>

                        <div class="mt-10">
                            @if($authenticatedClient)
                                <div class="bg-dark-card border border-border-dark p-6 rounded-lg">
                                    <h3 class="text-2xl font-teko text-white mb-4">Ajouter au panier</h3>
                                    <livewire:product.add-to-cart :product="$product" />
                                </div>
                            @else
                                <a href="{{ route('nos-offres') }}" class="w-full text-center bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 py-4 px-10 rounded-sm inline-block">
                                    <i class="fas fa-paper-plane mr-3"></i> Passer une commande
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 bg-black">
            <div class="container mx-auto px-6">
                <div class="border-b border-slate-800 flex flex-wrap">
                    <div class="info-tab active" onclick="switchTab(event, 'description')">Description</div>
                    @if($product->infos_nutritionnelles)
                    <div class="info-tab" onclick="switchTab(event, 'nutrition')">Infos Nutritionnelles</div>
                    @endif
                    @if($product->idee_recette)
                    <div class="info-tab" onclick="switchTab(event, 'recette')">Idée Recette</div>
                    @endif
                </div>
                <div class="pt-8 text-slate-400 prose prose-invert max-w-none prose-p:my-2 prose-headings:my-4">
                    <div id="description" class="tab-content">{!! $product->description_longue !!}</div>
                    @if($product->infos_nutritionnelles)
                    <div id="nutrition" class="tab-content hidden">{!! $product->infos_nutritionnelles !!}</div>
                    @endif
                    @if($product->idee_recette)
                    <div id="recette" class="tab-content hidden">{!! $product->idee_recette !!}</div>
                    @endif
                </div>
            </div>
        </section>

        @if($relatedProducts->isNotEmpty())
        <section class="py-24">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                     <h2 class="text-5xl uppercase text-white">Vous pourriez aussi aimer</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($relatedProducts as $relatedProduct)
                        <x-product-card :product="$relatedProduct" />
                    @endforeach
                </div>
            </div>
        </section>
        @endif
    </main>

    @push('scripts')
    <script>
        function changeImage(element) {
            document.getElementById('mainProductImage').src = element.src;
            document.querySelectorAll('.product-gallery-thumbnail').forEach(thumb => thumb.classList.remove('active'));
            element.classList.add('active');
        }

        function switchTab(event, tabName) {
            document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
            document.getElementById(tabName).classList.remove('hidden');
            document.querySelectorAll('.info-tab').forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
        }
    </script>
    @endpush
</x-layouts.app>