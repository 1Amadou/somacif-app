<div>
    {{-- Le bloc d'achat ne s'affiche que pour les clients connectés --}}
    @if($client)
        @if ($selectedVariant)
            <div class="mb-6">
                <p class="text-4xl font-teko brand-red">
                    {{ number_format($currentPrice, 0, ',', ' ') }} FCFA
                    <span class="text-lg text-slate-400">/ {{ $selectedVariant->nom_unite }}</span>
                </p>
            </div>

            <div class="space-y-4">
                {{-- Sélecteur d'unité de vente (calibre) --}}
                <div>
                    <label for="calibre" class="text-sm font-medium text-slate-300 mb-2 block">Choisir un calibre</label>
                    <select id="calibre" wire:model.live="selectedVariantId" class="w-full bg-slate-800 border border-slate-700 rounded-md py-3 px-4 text-white focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                        @foreach($product->uniteDeVentes as $variant)
                            <option value="{{ $variant->id }}">{{ $variant->calibre }} - {{ $variant->nom_unite }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Champ quantité et bouton d'ajout --}}
                <div class="flex items-center gap-4">
                    <input type="number" min="1" wire:model="quantity" class="w-24 form-input text-center bg-slate-800 border border-slate-700 rounded-md py-3 px-2 text-white">
                    <button wire:click="addToCart" wire:loading.attr="disabled" class="w-full bg-brand-red hover:bg-red-700 text-white font-bold text-sm uppercase tracking-wider py-4 px-3 rounded-md transition-colors">
                        <span wire:loading.remove wire:target="addToCart">
                            <i class="fas fa-shopping-cart mr-2"></i> Ajouter au panier
                        </span>
                        <span wire:loading wire:target="addToCart">Ajout en cours...</span>
                    </button>
                </div>
            </div>
        @else
            <p class="text-slate-400">Ce produit n'a pas d'options de vente disponibles.</p>
        @endif
    @else
        {{-- Message pour les visiteurs non connectés --}}
        <div class="text-center py-10 bg-dark-card rounded-lg border border-border-dark">
            <h3 class="text-2xl font-teko uppercase text-white">Connectez-vous pour commander</h3>
            <p class="text-slate-400 mt-2 mb-6">Consultez nos offres et connectez-vous pour voir les prix.</p>
            <a href="{{ route('login') }}" class="btn btn-primary">S'identifier</a>
        </div>
    @endif
</div>