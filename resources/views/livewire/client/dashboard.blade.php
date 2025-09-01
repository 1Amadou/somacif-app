<div class="py-24">
    <div class="container mx-auto px-6">
        <div class="mb-12">
            <h1 class="text-5xl font-teko uppercase text-white">Tableau de Bord</h1>
            <p class="text-slate-400">Bienvenue, {{ $client->nom }}. Gérez votre partenariat avec SOMACIF.</p>
        </div>

        @if (session()->has('success'))
            <div class="bg-green-900/50 border border-green-700 text-green-300 p-4 rounded-lg text-center mb-8">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="grid sm:grid-cols-3 gap-6">
                    <div class="stat-card p-6 rounded-lg text-center"><span class="text-5xl font-teko brand-red">{{ $allOrders->count() }}</span><p class="text-white mt-1">Commandes Passées</p></div>
                    <div class="stat-card p-6 rounded-lg text-center"><span class="text-5xl font-teko brand-red">{{ $allOrders->where('remaining_balance', '>', 0)->count() }}</span><p class="text-white mt-1">Factures en Attente</p></div>
                    <div class="stat-card p-6 rounded-lg text-center"><span class="text-5xl font-teko brand-red">{{ number_format($allOrders->sum('remaining_balance'), 0, ',', ' ') }}</span><p class="text-white mt-1">Solde Total Dû (FCFA)</p></div>
                </div>

                <div class="bg-dark-card border border-border-dark rounded-lg">
                    <div class="p-6 border-b border-border-dark">
                        <h3 class="text-2xl font-teko text-white mb-4">Historique des commandes</h3>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-input" placeholder="Rechercher par N° de commande...">
                            <select wire:model.live="statusFilter" class="form-input">
                                <option value="">Tous les statuts</option>
                                <option value="en_attente">En attente</option>
                                <option value="validee">Validée</option>
                                <option value="en_preparation">En préparation</option>
                                <option value="en_cours_livraison">En cours de livraison</option>
                                <option value="livree">Livrée</option>
                                <option value="annulee">Annulée</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-sm uppercase text-slate-400 border-b border-border-dark">
                                <tr>
                                    <th class="px-6 py-4">N° Commande</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Montant</th>
                                    <th class="px-6 py-4">Solde Restant</th>
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
                                        <td class="px-6 py-4 font-semibold {{ $order->remaining_balance > 0 ? 'text-amber-400' : 'text-green-400' }}">{{ number_format($order->remaining_balance, 0, ',', ' ') }} FCFA</td>
                                        <td class="px-6 py-4"><span class="px-3 py-1 text-xs font-semibold rounded-full {{ match($order->statut) { 'livree' => 'bg-green-500/20 text-green-400', 'annulee' => 'bg-red-500/20 text-red-400', default => 'bg-amber-500/20 text-amber-400' } }}">{{ $order->statut }}</span></td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap text-sm font-medium">
                                            @if($order->statut === 'en_attente')
                                                <a href="{{ route('client.orders.edit', $order) }}" wire:navigate class="text-amber-400 hover:text-amber-300 mr-4">Modifier</a>
                                            @endif
                                            @if($order->statut === 'en_cours_livraison')
                                                <button wire:click="confirmReception({{ $order->id }})" wire:confirm="Êtes-vous sûr...?" class="text-green-400 hover:text-green-300 mr-4">Confirmer Réception</button>
                                            @endif
                                            @if($order->statut === 'livree')
                                                 <a href="{{ route('client.orders.invoice', $order) }}" target="_blank" class="text-blue-400 hover:text-blue-300 mr-4">Facture</a>
                                            @endif
                                            <a href="{{ route('client.orders.show', $order) }}" wire:navigate class="brand-red hover:text-red-400">Voir</a>
                                        </td>
                                        
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-12 text-slate-400">Aucune commande ne correspond à votre recherche.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($orders->hasPages())
                        <div class="p-6 border-t border-border-dark">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-1 space-y-8">
                <div class="bg-dark-card border border-border-dark rounded-lg p-6">
                    <h3 class="text-2xl font-teko text-white mb-4">Actions Rapides</h3>
                    <a href="{{ route('products.index') }}" class="block w-full text-center bg-brand-red hover:bg-red-700 text-white font-bold tracking-widest uppercase py-3 rounded-sm mb-3">Passer une nouvelle commande</a>
                </div>

                <div class="bg-dark-card border border-border-dark rounded-lg p-6">
                    <h3 class="text-2xl font-teko text-white mb-4">Vos Informations</h3>
                    <div class="space-y-3 text-sm">
                        <p class="text-slate-400"><strong>Identifiant :</strong> <span class="font-mono text-white">{{ $client->identifiant_unique_somacif }}</span></p>
                        <p class="text-slate-400"><strong>Téléphone :</strong> <span class="text-white">{{ $client->telephone }}</span></p>
                        <p class="text-slate-400"><strong>Type :</strong> <span class="text-white">{{ $client->type }}</span></p>
                        <div class="pt-3 border-t border-border-dark">
                            <h4 class="font-bold text-white mb-2">Vos points de vente :</h4>
                            <ul class="list-disc list-inside text-slate-300">
                                @forelse($client->pointsDeVente as $pointDeVente)
                                    <li>{{ $pointDeVente->nom }}</li>
                                @empty
                                    <li>Aucun point de vente enregistré.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>