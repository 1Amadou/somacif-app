<div>
    {{-- Message flash en cas de succès --}}
    @if(session()->has('success'))
        <div class="bg-green-800 border border-green-700 text-white p-4 rounded-lg text-center mb-8">
            {{ session('success') }}
        </div>
    @endif

    {{-- Message pour les visiteurs non connectés --}}
    @if(!$client)
        <div class="text-center py-20 bg-dark-card rounded-lg border border-border-dark">
            <h3 class="text-3xl font-teko uppercase text-white">Connectez-vous pour commander</h3>
            <p class="text-slate-400 mt-2 mb-6">Le catalogue de prix et la commande sont réservés à nos partenaires.</p>
            <a href="{{ route('login') }}" class="bg-brand-red hover:bg-red-700 text-white font-bold tracking-widest uppercase py-3 px-8 rounded-sm transition-colors">S'identifier / Devenir Partenaire</a>
        </div>
    @endif

    {{-- Grille de produits pour les clients connectés --}}
    @if($client)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            @forelse($products as $product)
                <div class="product-card-info rounded-lg overflow-hidden group flex flex-col bg-dark-card border border-border-dark">
                    <a href="{{ route('products.show', $product) }}" class="block overflow-hidden h-56">
                        <img src="{{ $product->image_principale ? Storage::url($product->image_principale) : 'https://placehold.co/400x300/171717/FFFFFF?text=Image+Produit' }}"
                             alt="Image de {{ $product->nom }}"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    </a>
                    <div class="p-6 flex flex-col flex-grow">
                        <h3 class="text-2xl text-white mb-2 font-teko truncate">{{ $product->nom }}</h3>

                        @if($product->uniteDeVentes->count() > 0)
                            {{-- Sélection de l'unité de vente (calibre/poids) --}}
                            <select wire:model.live="selectedVariants.{{ $product->id }}" class="form-input mb-4 text-sm bg-slate-800 border border-slate-700 text-white rounded-md">
                                @foreach($product->uniteDeVentes as $unite)
                                    <option value="{{ $unite->id }}">{{ $unite->nom_unite }} - {{ $unite->calibre }}</option>
                                @endforeach
                            </select>
                            
                            @php
                                $selectedUnite = $product->uniteDeVentes->firstWhere('id', $selectedVariants[$product->id] ?? null) ?? $product->uniteDeVentes->first();
                            @endphp

                            {{-- Prix dynamique --}}
                            <p class="text-3xl font-teko brand-red mb-4">
                                {{ number_format($this->getPriceForClient($selectedUnite), 0, ',', ' ') }} FCFA
                            </p>
                            
                            <div class="mt-auto pt-4 border-t border-slate-800">
                                <div class="flex items-center gap-2">
                                    <input type="number" min="1" wire:model="quantities.{{ $selectedUnite->id }}" class="w-20 form-input text-center bg-slate-800 border border-slate-700 rounded-md py-2 px-2 text-white">
                                    <button wire:click="addToCart({{ $selectedUnite->id }})" wire:loading.attr="disabled" class="w-full bg-brand-red hover:bg-red-700 text-white font-bold text-sm uppercase tracking-wider py-3 px-3 rounded-md transition-colors">
                                        <span wire:loading.remove wire:target="addToCart({{ $selectedUnite->id }})">Ajouter</span>
                                        <span wire:loading wire:target="addToCart({{ $selectedUnite->id }})">Ajout...</span>
                                    </button>
                                </div>
                            </div>
                        @else
                            <p class="text-slate-400">Aucune unité de vente disponible.</p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center py-12 text-slate-400">Aucun produit à afficher.</p>
            @endforelse
        </div>
        <div class="mt-16">{{ $products->links() }}</div>
    @endif
</div>