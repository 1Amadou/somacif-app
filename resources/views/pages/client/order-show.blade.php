<x-layouts.app>
    @slot('metaTitle', 'Détail Commande ' . $order->numero_commande)

    <div class="py-24">
        <div class="container mx-auto px-6">
            <div class="mb-8">
                <a href="{{ route('client.dashboard') }}" class="text-sm brand-red hover:text-red-400"><i class="fas fa-arrow-left mr-2"></i>Retour au tableau de bord</a>
                <h1 class="text-5xl font-teko uppercase text-white mt-4">Détail de la Commande</h1>
                <p class="text-slate-400">Commande <span class="font-mono text-white">{{ $order->numero_commande }}</span> passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-dark-card border border-border-dark rounded-lg p-8">
                    <h2 class="text-2xl font-teko text-white mb-4">Articles Commandés</h2>
                    <div class="space-y-4">
                        @forelse($order->orderItems as $item)
                            <div class="flex justify-between items-center border-b border-border-dark pb-4 last:border-b-0">
                                <div>
                                    <p class="font-bold text-white">{{ $item->nom_produit }}</p>
                                    <p class="text-sm text-slate-400">Calibre : {{ $item->calibre }}</p>
                                    <p class="text-sm text-slate-400">{{ $item->quantite }} carton(s) × {{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA</p>
                                </div>
                                <p class="text-lg font-semibold text-white">{{ number_format($item->quantite * $item->prix_unitaire, 0, ',', ' ') }} FCFA</p>
                            </div>
                        @empty
                             <p class="text-center text-slate-400 py-8">Cette commande ne contient aucun article.</p>
                        @endforelse
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-8">
                    <div class="bg-dark-card border border-border-dark rounded-lg p-8 sticky top-32">
                        <h2 class="text-2xl font-teko text-white mb-4">Récapitulatif</h2>
                        <div class="space-y-3 text-slate-300">
                            <div class="flex justify-between"><span>Statut</span> <span class="font-bold text-white">{{ $order->statut }}</span></div>
                            <div class="flex justify-between"><span>Adresse de livraison</span> <span class="font-bold text-white text-right">{{ $order->delivery_address }}</span></div>
                            <div class="flex justify-between pt-4 border-t border-border-dark text-lg">
                                <span class="font-bold text-white">Montant Total</span>
                                <span class="font-bold text-primary">{{ number_format($order->montant_total, 0, ',', ' ') }} FCFA</span>
                            </div>
                             <div class="flex justify-between text-base">
                                <span class="text-slate-400">Montant Payé</span>
                                <span class="font-semibold text-green-400">- {{ number_format($order->amount_paid, 0, ',', ' ') }} FCFA</span>
                            </div>
                             <div class="flex justify-between pt-2 border-t border-slate-700 text-lg">
                                <span class="font-bold text-white">Solde Restant</span>
                                <span class="font-bold text-amber-400">{{ number_format($order->remaining_balance, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                        @if($order->statut === 'Livrée')
                        <a href="{{ route('client.orders.invoice', $order) }}" class="block mt-6 w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold tracking-widest uppercase py-3 rounded-sm">
                            Télécharger la Facture
                        </a>
                        @endif
                    </div>

                    @if($order->livreur)
                        <div class="bg-dark-card border border-border-dark rounded-lg p-8">
                            <h2 class="text-2xl font-teko text-white mb-4">Informations de Livraison</h2>
                            <div class="space-y-3 text-sm">
                                <p class="text-slate-400">Votre commande est en cours de livraison par :</p>
                                <p class="font-bold text-white text-lg">{{ $order->livreur->name }}</p>
                                <p class="text-slate-400">Vous pouvez le contacter au : <a href="tel:{{ $order->livreur->phone }}" class="text-primary hover:underline">{{ $order->livreur->phone }}</a></p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>