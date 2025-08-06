<div class="py-24">
    <div class="container mx-auto px-6">
        <div class="mb-12">
            <h1 class="text-5xl font-teko uppercase text-white">Tableau de Bord</h1>
            <p class="text-slate-400">Bienvenue, {{ $client->nom }}. Gérez votre partenariat avec SOMACIF.</p>
        </div>

        @if (session('success'))
            <div class="bg-green-900/50 border border-green-700 text-green-300 p-4 rounded-lg text-center mb-8">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-900/50 border border-red-700 text-red-300 p-4 rounded-lg text-center mb-8">
                {{ session('error') }}
            </div>
        @endif
        @if(!$client->terms_accepted_at)
            <div class="bg-amber-900/50 border border-amber-700 text-amber-300 p-4 rounded-lg text-center mb-8">
                <p><i class="fas fa-exclamation-triangle mr-2"></i><strong>Action requise :</strong> Pour finaliser votre compte, veuillez consulter et accepter nos conditions de partenariat.</p>
                <a href="{{ route('client.contract') }}" class="font-bold underline hover:text-white mt-2 inline-block">Consulter mon contrat et accepter les conditions</a>
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
                                <option value="Reçue">Reçue</option>
                                <option value="Validée">Validée</option>
                                <option value="En préparation">En préparation</option>
                                <option value="En cours de livraison">En cours de livraison</option>
                                <option value="Livrée">Livrée</option>
                                <option value="Annulée">Annulée</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto hidden md:block">
                        <table class="w-full text-left">
                            <thead class="text-sm uppercase text-slate-400 border-b border-border-dark">
                                <tr>
                                    <th class="px-6 py-4">N° Commande</th>
                                    <th class="px-6 py-4">Date</th>
                                    <th class="px-6 py-4">Montant Total</th>
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
                                        <td class="px-6 py-4"><span class="px-3 py-1 text-xs font-semibold rounded-full @if($order->statut === 'Livrée') bg-green-500/20 text-green-400 @elseif($order->statut === 'Annulée') bg-red-500/20 text-red-400 @else bg-amber-500/20 text-amber-400 @endif">{{ $order->statut }}</span></td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            @if($order->statut === 'Livrée') <a href="{{ route('client.orders.invoice', $order) }}" class="font-bold text-sm text-blue-400 hover:text-blue-300 mr-4">Facture</a> @endif
                                            @if($order->statut === 'Reçue') <a href="{{ route('client.orders.edit', $order) }}" class="font-bold text-sm text-amber-400 hover:text-amber-300 mr-4">Modifier</a> @endif
                                            @if($order->statut === 'En cours de livraison')<button wire:click="confirmReception({{ $order->id }})" wire:confirm="Êtes-vous sûr de vouloir confirmer la réception ?" class="font-bold text-sm text-green-400 hover:text-green-300 mr-4">Confirmer Réception</button> @endif
                                            <a href="{{ route('client.orders.show', $order) }}" class="font-bold text-sm brand-red hover:text-red-400">Voir</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-12 text-slate-400">Aucune commande ne correspond à votre recherche.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="md:hidden space-y-4 p-4">
                        @forelse($orders as $order)
                            <div class="border border-border-dark rounded-lg p-4 space-y-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-mono text-white">{{ $order->numero_commande }}</p>
                                        <p class="text-sm text-slate-400">{{ $order->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full @if($order->statut === 'Livrée') bg-green-500/20 text-green-400 @elseif($order->statut === 'Annulée') bg-red-500/20 text-red-400 @else bg-amber-500/20 text-amber-400 @endif">{{ $order->statut }}</span>
                                </div>
                                <div class="pt-3 border-t border-border-dark space-y-2">
                                    <p class="flex justify-between text-slate-300"><span>Montant:</span> <span class="font-semibold text-white">{{ number_format($order->montant_total, 0, ',', ' ') }} FCFA</span></p>
                                    <p class="flex justify-between text-slate-300"><span>Solde:</span> <span class="font-semibold {{ $order->remaining_balance > 0 ? 'text-amber-400' : 'text-green-400' }}">{{ number_format($order->remaining_balance, 0, ',', ' ') }} FCFA</span></p>
                                </div>
                                <div class="pt-3 border-t border-border-dark flex justify-end items-center gap-4">
                                    @if($order->statut === 'Livrée') <a href="{{ route('client.orders.invoice', $order) }}" class="font-bold text-sm text-blue-400 hover:text-blue-300">Facture</a> @endif
                                    @if($order->statut === 'Reçue') <a href="{{ route('client.orders.edit', $order) }}" class="font-bold text-sm text-amber-400 hover:text-amber-300">Modifier</a> @endif
                                    @if($order->statut === 'En cours de livraison')<button wire:click="confirmReception({{ $order->id }})" wire:confirm="Êtes-vous sûr ?" class="font-bold text-sm text-green-400 hover:text-green-300">Confirmer Réception</button> @endif
                                    <a href="{{ route('client.orders.show', $order) }}" class="btn btn-primary py-2 px-4 text-xs">Voir</a>
                                </div>
                            </div>
                        @empty
                            <p class="text-center py-8 text-slate-400">Aucune commande ne correspond à votre recherche.</p>
                        @endforelse
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
                    <a href="{{ route('products.index') }}" class="block w-full text-center bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase py-3 rounded-sm mb-3">Passer une nouvelle commande</a>
                    <a href="#" class="block w-full text-center border-2 border-slate-700 text-slate-400 font-bold tracking-widest uppercase py-3 rounded-sm">Voir toutes mes factures</a>
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

