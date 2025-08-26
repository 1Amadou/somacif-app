<div>
    {{-- Message flash en cas d'erreur ou de succès --}}
    @if(session()->has('error'))
        <div class="bg-red-500 text-white p-4 rounded mb-6">{{ session('error') }}</div>
    @endif
    @if(session()->has('success'))
        <div class="bg-green-500 text-white p-4 rounded mb-6">{{ session('success') }}</div>
    @endif

    @if(!$client)
        <div class="text-center py-20 bg-dark-card rounded-lg border border-border-dark">
            <h3 class="text-3xl font-teko uppercase text-white">Connectez-vous pour commander</h3>
            <p class="text-slate-400 mt-2 mb-6">Le catalogue de prix et la commande sont réservés à nos partenaires.</p>
            <a href="{{ route('nos-offres') }}" class="bg-brand-red hover:bg-red-700 text-white font-bold tracking-widest uppercase py-3 px-8 rounded-sm transition-colors">S'identifier</a>
        </div>
    @endif

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

                        {{-- SÉLECTION DU CALIBRE --}}
                        @if($product->uniteDeVentes->count() > 1)
                            <select wire:model.live="selectedVariants.{{ $product->id }}" class="form-input mb-4 text-sm bg-slate-800 border border-slate-700 text-white rounded-md">
                                @foreach($product->uniteDeVentes as $variant)
                                    <option value="{{ $variant->id }}">{{ $variant->calibre }}</option>
                                @endforeach
                            </select>
                        @elseif($product->uniteDeVentes->first())
                            <p class="text-sm text-slate-400 mb-4">Calibre: {{ $product->uniteDeVentes->first()->calibre }}</p>
                        @endif

                        {{-- PRIX DYNAMIQUE BASÉ SUR LE CALIBRE SÉLECTIONNÉ --}}
                        @php
                            // S'assure que la variante existe avant de la récupérer
                            $selectedVariant = $product->uniteDeVentes->firstWhere('id', $selectedVariants[$product->id] ?? null);
                        @endphp
                        <p class="text-3xl font-teko brand-red mb-4">
                            @if($selectedVariant)
                                {{ number_format($this->getPriceForClient($selectedVariant), 0, ',', ' ') }} FCFA
                                <span class="text-base text-slate-400">/ {{ $selectedVariant->nom_unite }}</span>
                            @else
                                <span class="text-base text-slate-400">Prix non disponible</span>
                            @endif
                        </p>

                        <div class="mt-auto pt-4 border-t border-slate-800">
                            {{-- Affichage du stock --}}
                            <h4 class="text-sm font-bold text-slate-300 mb-2">Disponibilité :</h4>
                            @if($product->pointsDeVenteStock->count() > 0)
                                <ul class="text-xs space-y-1">
                                    @foreach($product->pointsDeVenteStock as $stock)
                                        <li class="flex justify-between items-center text-slate-400">
                                            <span><i class="fas fa-map-marker-alt mr-2"></i>{{ $stock->nom }}</span>
                                            <span class="font-bold text-white bg-slate-700 px-2 py-1 rounded">{{ $stock->pivot->quantite_stock }} cartons</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-xs text-slate-500">Stock non renseigné.</p>
                            @endif
                        </div>

                        <div class="mt-4">
                            <div class="flex items-center gap-2">
                                <input type="number" min="1" wire:model.live="quantities.{{ $product->id }}" class="w-20 form-input text-center bg-slate-800 border border-slate-700 rounded-md py-2 px-2 text-white">
                                <button wire:click="addToCart({{ $product->id }})" class="w-full bg-brand-red hover:bg-red-700 text-white font-bold text-sm uppercase tracking-wider py-3 px-3 rounded-md transition-colors">Ajouter</button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center py-12 text-slate-400">Aucun produit à afficher.</p>
            @endforelse
        </div>
        <div class="mt-16">{{ $products->links() }}</div>
    @endif
</div>