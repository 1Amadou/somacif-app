<div>
    @if($selectedVariant)
        <div class="mb-6">
            <p class="text-4xl font-teko brand-red">
                {{ number_format($currentPrice, 0, ',', ' ') }} FCFA
                <span class="text-lg text-slate-400">/ {{ $selectedVariant->nom_unite }}</span>
            </p>
        </div>

        <div class="space-y-4">
            <div>
                <label for="calibre" class="text-sm font-medium text-slate-300 mb-2 block">Choisir un calibre</label>
                <select id="calibre" wire:model.live="selectedVariantId" class="w-full bg-slate-800 border border-slate-700 rounded-md py-3 px-4 text-white focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    @foreach($product->uniteDeVentes as $variant)
                        <option value="{{ $variant->id }}">{{ $variant->calibre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-4">
                <div>
                    <label for="quantity" class="text-sm text-slate-400">Quantité</label>
                    <input type="number" id="quantity" wire:model="quantity" min="1" class="w-24 bg-slate-800 border border-slate-700 rounded-md py-2 px-2 text-white text-center">
                </div>
                <button wire:click="addToCart" class="w-full bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 py-3 px-8 rounded-sm">
                    Ajouter au panier
                </button>
            </div>
        </div>
        
        @if($message)
            <p class="text-green-400 text-sm mt-3">{{ $message }}</p>
        @endif
    @else
        <p class="text-amber-400">Ce produit n'a pas de variante de prix disponible pour le moment.</p>
    @endif
</div>