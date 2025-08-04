<div class="py-24">
    <div class="container mx-auto px-6">

        @if($latestOrder)
            <div class="bg-green-900/50 border border-green-700 text-green-300 p-8 rounded-lg text-center max-w-3xl mx-auto">
                <i class="fas fa-check-circle text-6xl mb-4"></i>
                <h1 class="text-4xl font-teko uppercase text-white mb-2">Commande Passée avec Succès !</h1>
                <p class="mb-6">Votre commande <span class="font-bold font-mono">{{ $latestOrder->numero_commande }}</span> a bien été enregistrée. Notre équipe commerciale vous contactera très prochainement pour la confirmation.</p>
                <a href="{{ route('client.dashboard') }}" class="bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase py-3 px-8 rounded-sm">
                    Voir mes commandes
                </a>
            </div>
        @else
            <h1 class="text-5xl font-teko uppercase text-white">Finaliser ma commande</h1>
            <div class="mt-8 grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-dark-card border border-border-dark rounded-lg p-6">
                        <h2 class="text-2xl font-teko text-white mb-4">1. Point de Livraison</h2>
                        @if(!empty($deliveryAddresses))
                            <select wire:model="selectedAddress" class="form-input">
                                @foreach($deliveryAddresses as $address)
                                    <option value="{{ $address }}">{{ $address }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="bg-amber-900/50 border border-amber-700 text-amber-300 p-4 rounded-lg text-sm">
                                <p class="font-bold">Aucun point de livraison configuré.</p>
                                <p>Veuillez contacter le service commercial de SOMACIF pour faire ajouter vos entrepôts ou points de livraison à votre compte.</p>
                            </div>
                        @endif
                        @error('selectedAddress') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="bg-dark-card border border-border-dark rounded-lg p-6">
                        <h2 class="text-2xl font-teko text-white mb-4">2. Ajouter une note</h2>
                        <textarea wire:model.defer="notes" class="form-textarea" rows="4" placeholder="Instructions spéciales, informations complémentaires..."></textarea>
                    </div>
                </div>
                <div class="lg:col-span-1">
                    <div class="bg-dark-card border border-border-dark rounded-lg p-6 sticky top-32">
                        <h2 class="text-2xl font-teko text-white mb-4">Récapitulatif</h2>
                        <div class="space-y-3 mb-4 border-b border-border-dark pb-4">
                            @foreach($cartItems as $item)
                                <div class="flex justify-between text-sm">
                                    <p class="text-slate-300">{{ $item['name'] }} <span class="text-slate-500">x{{ $item['quantity'] }}</span></p>
                                    {{-- Le calcul du prix par item sera ajouté si nécessaire --}}
                                </div>
                            @endforeach
                        </div>
                        <div class="space-y-2 text-slate-300">
                            <div class="flex justify-between pt-4 border-t border-border-dark text-lg">
                                <span class="font-bold text-white">Montant Total</span>
                                <span class="font-bold text-primary">{{ number_format($totalAmount, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                        <button wire:click="placeOrder" wire:loading.attr="disabled" class="w-full mt-6 text-center bg-green-600 hover:bg-green-700 text-white font-bold tracking-widest uppercase transition duration-300 py-4 rounded-sm disabled:opacity-50">
                            <span wire:loading.remove>Confirmer et Envoyer</span>
                            <span wire:loading>Envoi en cours...</span>
                        </button>
                        @error('cart') <p class="text-red-500 text-xs mt-2 text-center">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>