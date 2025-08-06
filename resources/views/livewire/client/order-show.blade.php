<x-layouts.app>
    @slot('metaTitle', 'Détail Commande ' . $order->numero_commande)

    <div class="py-24">
        <div class="container mx-auto px-6">
            <a href="{{ route('client.dashboard') }}" class="text-sm brand-red hover:text-red-400">
                <i class="fas fa-arrow-left mr-2"></i>Retour à mes commandes
            </a>
            <h1 class="text-5xl font-teko uppercase text-white mt-4">Détail de la Commande</h1>
            <p class="text-slate-400">Commande <span class="font-mono text-white">{{ $order->numero_commande }}</span> passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>

            <div class="mt-8 grid lg:grid-cols-3 gap-8">
                <!-- Colonne Principale -->
                <div class="lg:col-span-2 bg-dark-card border border-border-dark rounded-lg p-8">
                    <h2 class="text-2xl font-teko text-white mb-4">Articles Commandés</h2>
                    <div class="space-y-4">
                        @foreach($order->orderItems as $item)
                            <div class="flex justify-between items-center border-b border-border-dark pb-4 last:border-b-0">
                                <div>
                                    <p class="font-bold text-white">{{ $item->nom_produit }}</p>
                                    {{-- AFFICHAGE DU CALIBRE --}}
                                    <p class="text-sm text-slate-400">Calibre : {{ $item->calibre }}</p>
                                    <p class="text-sm text-slate-400">{{ $item->quantite }} carton(s) × {{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA</p>
                                </div>
                                <p class="text-lg font-semibold text-white">{{ number_format($item->quantite * $item->prix_unitaire, 0, ',', ' ') }} FCFA</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Colonne Latérale -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Récapitulatif de la Commande -->
                    <div class="bg-dark-card border border-border-dark rounded-lg p-8 sticky top-32">
                        <h2 class="text-2xl font-teko text-white mb-4">Récapitulatif</h2>
                        <div class="space-y-2 text-slate-300">
                            <div class="flex justify-between">
                                <span>Statut</span> 
                                <span class="font-bold text-white">{{ $order->statut }}</span>
                            </div>
                            <div class="flex justify-between pt-4 border-t border-border-dark text-lg">
                                <span class="font-bold text-white">Montant Total</span>
                                <span class="font-bold text-primary">{{ number_format($order->montant_total, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                    </div>

                    <!-- Informations de Livraison (Si applicable) -->
                    <div class="bg-dark-card border border-border-dark rounded-lg p-8">
                        <h2 class="text-2xl font-teko text-white mb-4">Informations de Livraison</h2>
                        <div class="space-y-2 text-slate-300">
                            <div class="flex justify-between">
                                <span>Adresse de Livraison</span>
                                <span class="font-bold text-white">{{ $order->adresse_livraison }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Date de Livraison Prévue</span>
                                <span class="font-bold text-white">{{ $order->date_livraison->format('d/m/Y') }}</span>
                            </div>
                            @if($order->livreur)
                                <div class="flex justify-between">
                                    <span>Nom du Livreur</span>
                                    <span class="font-bold text-white">{{ $order->livreur->nom }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Contact du Livreur</span>
                                    <span class="font-bold text-white">{{ $order->livreur->telephone }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
