<div class="py-24">
    <div class="container mx-auto px-6">
        <div class="mb-12">
            <h1 class="text-5xl font-teko uppercase text-white">Tableau de Bord</h1>
            <p class="text-slate-400">Bienvenue, {{ $client->nom }}. Gérez votre partenariat avec SOMACIF.</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                
                <div class="grid sm:grid-cols-3 gap-6">
                    <div class="stat-card p-6 rounded-lg text-center">
                        <span class="text-5xl font-teko brand-red">{{ $orders->count() }}</span>
                        <p class="text-white mt-1">Commandes Passées</p>
                    </div>
                    <div class="stat-card p-6 rounded-lg text-center">
                        <span class="text-5xl font-teko brand-red">{{ $orders->whereNotIn('statut', ['Livrée', 'Annulée'])->count() }}</span>
                        <p class="text-white mt-1">Commandes en Cours</p>
                    </div>
                    <div class="stat-card p-6 rounded-lg text-center">
                        <span class="text-5xl font-teko brand-red">{{ number_format($orders->sum('montant_total'), 0, ',', ' ') }}</span>
                        <p class="text-white mt-1">Total Dépensé (FCFA)</p>
                    </div>
                </div>

                <div class="bg-dark-card border border-border-dark rounded-lg">
                    <h3 class="text-2xl font-teko text-white p-6 border-b border-border-dark">Historique des commandes</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-sm uppercase text-slate-400 border-b border-border-dark">
                                <tr>
                                    <th class="px-6 py-4">N° Commande</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Montant</th>
                                    <th class="px-6 py-4">Statut</th>
                                    <th class="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr class="border-b border-border-dark last:border-b-0">
                                        <td class="px-6 py-4 font-mono text-white">{{ $order->numero_commande }}</td>
                                        <td class="px-6 py-4">{{ $order->created_at->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4">{{ number_format($order->montant_total, 0, ',', ' ') }} FCFA</td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                                @if($order->statut === 'Livrée') bg-green-500/20 text-green-400
                                                @elseif($order->statut === 'Annulée') bg-red-500/20 text-red-400
                                                @else bg-amber-500/20 text-amber-400 @endif">
                                                {{ $order->statut }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('client.orders.show', $order) }}" class="font-bold text-sm brand-red hover:text-red-400">Voir les détails</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-12 text-slate-400">Vous n'avez encore passé aucune commande.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-dark-card border border-border-dark rounded-lg p-6 opacity-60">
                     <h3 class="text-2xl font-teko text-white mb-2">Commandes Planifiées</h3>
                     <p class="text-slate-400">Bientôt disponible : planifiez vos livraisons récurrentes et gérez votre calendrier d'approvisionnement directement ici.</p>
                </div>

            </div>

            <div class="lg:col-span-1 space-y-8">
                <div class="bg-dark-card border border-border-dark rounded-lg p-6">
                    <h3 class="text-2xl font-teko text-white mb-4">Actions Rapides</h3>
                    <a href="{{ route('products.index') }}" class="block w-full text-center bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 py-3 rounded-sm mb-3">
                        Passer une nouvelle commande
                    </a>
                    <a href="#" class="block w-full text-center border-2 border-slate-700 text-slate-500 font-bold tracking-widest uppercase py-3 rounded-sm cursor-not-allowed">
                        Voir mes factures (Bientôt)
                    </a>
                </div>
                <div class="bg-dark-card border border-border-dark rounded-lg p-6">
                    <h3 class="text-2xl font-teko text-white mb-4">Vos Informations</h3>
                    <div class="space-y-3 text-sm">
                        <p class="text-slate-400"><strong>Identifiant Unique :</strong> <span class="font-mono text-white">{{ $client->identifiant_unique_somacif }}</span></p>
                        <p class="text-slate-400"><strong>Téléphone :</strong> <span class="text-white">{{ $client->telephone }}</span></p>
                        <p class="text-slate-400"><strong>Type de compte :</strong> <span class="text-white">{{ $client->type }}</span></p>
                        <div class="pt-3 border-t border-border-dark">
                            <h4 class="font-bold text-white mb-2">Vos points de livraison :</h4>
                            <ul class="list-disc list-inside text-slate-300">
                                {{-- BLOC CORRIGÉ ET ROBUSTE --}}
                                @php
                                    $adresses = is_array($client->entrepots_de_livraison) ? $client->entrepots_de_livraison : [];
                                @endphp
                                @forelse($adresses as $adresse)
                                    <li>{{ $adresse }}</li>
                                @empty
                                    <li>Aucun point de livraison enregistré.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                 <div class="bg-dark-card border border-border-dark rounded-lg p-6 opacity-60">
                     <h3 class="text-2xl font-teko text-white mb-2">Support Direct</h3>
                     <p class="text-slate-400">Bientôt disponible : un espace pour communiquer directement avec votre agent commercial attitré.</p>
                </div>
            </div>
        </div>
    </div>
</div>