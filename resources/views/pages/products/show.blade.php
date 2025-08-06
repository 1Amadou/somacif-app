<x-layouts.app>
    @slot('metaTitle', $product->meta_titre ?? $product->nom . ' - SOMACIF')
    @slot('metaDescription', $product->meta_description ?? $product->description_courte)

    <main>
        <section class="py-16 md:py-24">
            <div class="container mx-auto px-6">
                {{-- La grille passe en une seule colonne sur mobile --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
                    
                    <div>
                        {{-- La galerie Swiper est déjà responsive par nature, aucun changement n'est nécessaire ici --}}
                        <div style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff" class="swiper product-gallery-main mb-4 rounded-lg">
                            <div class="swiper-wrapper">
                                @if($product->image_principale)
                                    <div class="swiper-slide bg-neutral-800"><img src="{{ Storage::url($product->image_principale) }}" class="w-full h-96 md:h-[500px] object-cover" /></div>
                                @endif
                                @if($product->images_galerie)
                                    @foreach($product->images_galerie as $image)
                                        <div class="swiper-slide bg-neutral-800"><img src="{{ Storage::url($image) }}" class="w-full h-96 md:h-[500px] object-cover" /></div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                        <div thumbsSlider="" class="swiper product-gallery-thumbs">
                            <div class="swiper-wrapper">
                                @if($product->image_principale)
                                    <div class="swiper-slide !w-1/4 md:!w-1/5"><img src="{{ Storage::url($product->image_principale) }}" class="w-full h-24 object-cover rounded cursor-pointer" /></div>
                                @endif
                                @if($product->images_galerie)
                                    @foreach($product->images_galerie as $image)
                                        <div class="swiper-slide !w-1/4 md:!w-1/5"><img src="{{ Storage::url($image) }}" class="w-full h-24 object-cover rounded cursor-pointer" /></div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <div>
                        {{-- Typographie responsive : text-4xl sur mobile, md:text-7xl sur desktop --}}
                        <h1 class="text-4xl md:text-7xl font-bold uppercase text-white leading-none">{{ $product->nom }}</h1>
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
                                    <livewire:product.add-to-cart :product="$product" />
                                </div>
                            @else
                                <div class="bg-dark-card border border-brand-red p-6 rounded-lg text-center fade-in-section">
                                    <h3 class="text-2xl font-teko text-white">Voir les prix et commander ?</h3>
                                    <p class="text-slate-400 mt-2 mb-6">L'accès aux tarifs et au passage de commande est réservé à nos partenaires.</p>
                                    <a href="{{ route('login') }}" class="btn btn-primary w-full">Se connecter ou Devenir Partenaire</a>
                                </div>
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

</x-layouts.app>