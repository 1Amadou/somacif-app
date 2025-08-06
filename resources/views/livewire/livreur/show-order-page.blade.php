<div class="py-12">
    <div class="container mx-auto px-6">
        <a href="{{ route('livreur.dashboard') }}" class="text-sm brand-red hover:text-red-400"><i class="fas fa-arrow-left mr-2"></i>Retour au tableau de bord</a>
        <h1 class="text-4xl font-teko uppercase text-white mt-4">Détail de la Livraison</h1>
        <p class="text-slate-400">Commande <span class="font-mono text-white">{{ $order->numero_commande }}</span></p>

        @if (session('success'))
            <div class="mt-4 bg-green-900/50 border border-green-700 text-green-300 p-4 rounded-lg text-center">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-8 grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-dark-card border border-border-dark rounded-lg p-8">
                <h2 class="text-2xl font-teko text-white mb-4">Informations Client</h2>
                <div class="space-y-3 text-slate-300">
                    <p><strong>Client :</strong> <span class="text-white">{{ $order->client->nom }}</span></p>
                    <p><strong>Téléphone :</strong> <a href="tel:{{ $order->client->telephone }}" class="text-primary hover:underline">{{ $order->client->telephone }}</a></p>
                    <p><strong>Adresse de Livraison :</strong> <span class="text-white">{{ $order->delivery_address }}</span></p>
                    @if($order->notes)
                        <p class="pt-3 border-t border-border-dark"><strong>Note du client :</strong> <span class="text-white">{{ $order->notes }}</span></p>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-dark-card border border-border-dark rounded-lg p-8 sticky top-32">
                    <h2 class="text-2xl font-teko text-white mb-4">Statut de la Confirmation</h2>
                    <div class="space-y-4">
                        <div>
                            @if($order->client_confirmed_at)
                                <p class="font-bold text-green-400 flex items-center"><i class="fas fa-check-circle mr-3"></i> Confirmé par le Client</p>
                                <p class="text-xs text-slate-400 pl-8">{{ $order->client_confirmed_at->format('d/m/Y à H:i') }}</p>
                            @else
                                <p class="font-bold text-amber-400 flex items-center"><i class="fas fa-clock mr-3"></i> En attente du Client</p>
                            @endif
                        </div>
                        <div class="pt-4 border-t border-border-dark">
                            @if($order->livreur_confirmed_at)
                                <p class="font-bold text-green-400 flex items-center"><i class="fas fa-check-circle mr-3"></i> Confirmé par Vous</p>
                                <p class="text-xs text-slate-400 pl-8">{{ $order->livreur_confirmed_at->format('d/m/Y à H:i') }}</p>
                            @else
                                <button wire:click="confirmDeliveryByLivreur" class="w-full bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase py-3 rounded-sm">
                                    Confirmer la Remise
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>