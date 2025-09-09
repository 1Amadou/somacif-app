<div class="py-24">
    <div class="container mx-auto px-6">
        <h1 class="text-5xl font-teko uppercase text-white mb-8">Finaliser ma commande</h1>
        @if (session()->has('checkout_error'))
            <div class="bg-red-900/50 border border-red-700 text-red-300 p-4 rounded-lg text-center mb-8">
                {{ session('checkout_error') }}
            </div>
        @endif
        @if ($cartItems->count() > 0)
            <form wire:submit="placeOrder" class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-dark-card border border-border-dark rounded-lg p-6 space-y-6">
                    <div>
                        <h3 class="text-2xl font-teko text-white mb-4">Adresse de Livraison</h3>
                        <label for="point_de_vente_id" class="text-slate-300 mb-2 block">Sélectionnez votre point de vente pour la livraison *</label>
                        <select wire:model="point_de_vente_id" id="point_de_vente_id" class="form-input w-full">
                            <option value="">-- Choisissez un point de vente --</option>
                            @foreach($pointsDeVente as $id => $nom)
                                <option value="{{ $id }}">{{ $nom }}</option>
                            @endforeach
                        </select>
                        @error('point_de_vente_id') <span class="text-red-500 text-sm mt-2">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="notes" class="text-slate-300 mb-2 block">Notes de commande (optionnel)</label>
                        <textarea wire:model="notes" id="notes" rows="4" class="form-input w-full" placeholder="Instructions spéciales..."></textarea>
                    </div>
                </div>

                <div class="lg:col-span-1 bg-dark-card border border-border-dark rounded-lg p-6 h-fit">
                    <h3 class="text-2xl font-teko text-white mb-4 border-b border-border-dark pb-4">Récapitulatif</h3>
                    <div class="space-y-4">
                        @foreach ($cartItems as $item)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-white">{{ $item->name }} <span class="text-slate-400">x {{ $item->qty }}</span></span>
                                <span class="text-slate-300">{{ number_format($item->price * $item->qty, 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 pt-6 border-t border-border-dark">
                        <div class="flex justify-between font-bold text-lg text-white">
                            <span>Total</span>
                            <span>{{ $cartTotal }} FCFA</span>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="btn btn-primary w-full text-center">Valider la commande</button>
                    </div>
                </div>
            </form>
        @else
            <div class="text-center py-20 bg-dark-card rounded-lg border border-border-dark">
                <h3 class="text-3xl font-teko uppercase text-white">Votre panier est vide</h3>
                <a href="{{ route('products.index') }}" class="btn btn-primary mt-6">Retourner au catalogue</a>
            </div>
        @endif
    </div>
</div>