<div class="py-12">
    <div class="container mx-auto px-6">
        <h1 class="text-4xl font-teko uppercase text-white">Vos Livraisons</h1>
        <p class="text-slate-400">Bonjour {{ $livreur->name }}, voici le suivi de vos tournées.</p>

        <div class="mt-8">
            <h2 class="text-3xl font-teko uppercase text-white mb-4">À Livrer Aujourd'hui</h2>
            <div class="space-y-6">
                @forelse($activeOrders as $order)
                    <div class="bg-dark-card border border-border-dark rounded-lg p-6 grid md:grid-cols-4 gap-6 items-center">
                        <div>
                            <p class="text-sm text-slate-400">Commande</p>
                            <p class="font-mono text-white">{{ $order->numero_commande }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-400">Client</p>
                            <p class="font-bold text-white">{{ $order->client->nom }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-400">Adresse de Livraison</p>
                            <p class="font-semibold text-white">{{ $order->delivery_address }}</p>
                        </div>
                        <div class="text-right">
                            <a href="{{ route('livreur.orders.show', $order) }}" class="bg-brand-red hover-bg-brand-red text-white font-bold tracking-wider uppercase transition duration-300 py-3 px-6 rounded-sm text-sm">
                                Gérer la Livraison
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="bg-dark-card border border-border-dark rounded-lg p-12 text-center">
                        <p class="text-slate-400">Vous n'avez aucune livraison active pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-16">
            <h2 class="text-3xl font-teko uppercase text-white mb-4">Historique des 10 Dernières Livraisons</h2>
            <div class="space-y-4">
                @forelse($completedOrders as $order)
                    <div class="bg-slate-900/50 border border-slate-800 rounded-lg p-4 grid md:grid-cols-4 gap-6 items-center opacity-70">
                        <div>
                            <p class="text-xs text-slate-500">Commande</p>
                            <p class="font-mono text-slate-300">{{ $order->numero_commande }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Client</p>
                            <p class="font-semibold text-slate-300">{{ $order->client->nom }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Statut Final</p>
                            <p class="font-bold {{ $order->statut === 'Livrée' ? 'text-green-400' : 'text-red-400' }}">{{ $order->statut }}</p>
                        </div>
                        <div class="text-right">
                            <a href="{{ route('livreur.orders.show', $order) }}" class="text-slate-400 hover:text-white text-sm">
                                Revoir
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="bg-dark-card border border-border-dark rounded-lg p-12 text-center">
                        <p class="text-slate-400">Aucune livraison terminée dans votre historique.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>