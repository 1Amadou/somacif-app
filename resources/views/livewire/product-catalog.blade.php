<div>
    @if(!$client)
        <div class="text-center py-20 bg-dark-card rounded-lg border border-border-dark">
            <h3 class="text-3xl font-teko uppercase text-white">Connectez-vous pour commander</h3>
            <p class="text-slate-400 mt-2 mb-6">Le catalogue de prix et la commande sont réservés à nos partenaires.</p>
            <a href="{{ route('nos-offres') }}" class="bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase py-3 px-8 rounded-sm">S'identifier</a>
        </div>
    @endif

    @if($client)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            @forelse($products as $product)
                <div class="product-card-info rounded-lg overflow-hidden group flex flex-col">
                    <a href="{{ route('products.show', $product) }}" class="block overflow-hidden h-56">
                        <img src="{{ $product->image_principale ? Storage::url($product->image_principale) : 'https://placehold.co/400x300/171717/FFFFFF?text=Image+Produit' }}" 
                             alt="Image de {{ $product->nom }}" 
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    </a>
                    <div class="p-6 flex flex-col flex-grow">
                        <h3 class="text-2xl text-white mb-2 font-teko truncate">{{ $product->nom }}</h3>
                        
                        @if($client && $product->uniteDeVentes->first())
                            <p class="text-3xl font-teko brand-red mb-4">
                                {{ number_format($this->getPriceForClient($product->uniteDeVentes->first()), 0, ',', ' ') }} FCFA
                                <span class="text-base text-slate-400">/ carton</span>
                            </p>
                        @endif

                        @if(!empty($product->calibres))
                        <div class="mb-4">
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Calibres :</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($product->calibres as $calibre)
                                    <span class="text-xs font-semibold bg-slate-700 text-slate-200 px-2 py-1 rounded">{{ $calibre }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <div class="mt-auto pt-4 border-t border-slate-800">
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
                                <input type="number" min="1" wire:model="quantities.{{ $product->id }}" class="w-20 bg-slate-800 border border-slate-700 rounded-md py-2 px-2 text-white text-center">
                                <button wire:click="addToCart({{ $product->id }})" class="w-full bg-brand-red hover-bg-brand-red text-white font-bold text-sm uppercase tracking-wider py-2 px-3 rounded-md transition-colors">Ajouter</button>
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