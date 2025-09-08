<div class="py-24">
    <div class="container mx-auto px-6">
        <div class="mb-8">
            <a href="{{ route('client.dashboard') }}" wire:navigate class="text-slate-400 hover:text-white text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Retour au tableau de bord
            </a>
            <h1 class="text-5xl font-teko uppercase text-white mt-2">Détail de la Commande</h1>
            <p class="font-mono text-slate-300">{{ $order->numero_commande }}</p>
            
            <div class="mt-6 space-y-4">
                @if (session()->has('message'))
                    <div class="bg-green-900/50 border border-green-700 text-green-300 p-4 rounded-lg text-center">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="flex items-center gap-4">
                    @if ($order->statut === \App\Enums\OrderStatusEnum::EN_COURS_LIVRAISON)
                        <button wire:click="confirmReception" wire:confirm="Confirmez-vous avoir bien reçu cette commande ?" class="btn btn-success">
                            <i class="fas fa-check-circle mr-2"></i>Confirmer la réception
                        </button>
                    @endif

                    @if ($order->statut === \App\Enums\OrderStatusEnum::EN_ATTENTE)
                        <a href="{{ route('client.orders.edit', $order) }}" wire:navigate class="btn btn-warning">
                           <i class="fas fa-pencil-alt mr-2"></i>Modifier la commande
                        </a>
                        <button wire:click="cancelOrder" wire:confirm="Êtes-vous sûr de vouloir annuler cette commande ?" class="btn btn-danger">
                           <i class="fas fa-times-circle mr-2"></i>Annuler la commande
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Colonne principale : Articles et Infos --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- Section des Articles Commandés --}}
                <div class="bg-dark-card border border-border-dark rounded-lg">
                    <div class="p-6 border-b border-border-dark">
                        <h3 class="text-2xl font-teko text-white">Articles Commandés</h3>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-4">
                            @foreach ($order->items as $item)
                            <li class="flex justify-between items-center border-b border-border-dark pb-4 last:border-b-0 last:pb-0">
                                <div>
                                    <p class="font-bold text-white">{{ $item->uniteDeVente->product->nom }}</p>
                                    <p class="text-sm text-slate-400">{{ $item->uniteDeVente->nom_unite }} - {{ $item->uniteDeVente->calibre }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-mono text-white">{{ number_format($item->prix_unitaire * $item->quantite, 0, ',', ' ') }} FCFA</p>
                                    <p class="text-sm text-slate-400">{{ $item->quantite }} x {{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA</p>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Section : Historique des Versements --}}
                <div class="bg-dark-card border border-border-dark rounded-lg">
                    <div class="p-6 border-b border-border-dark">
                        <h3 class="text-2xl font-teko text-white">Historique des Versements sur cette Commande</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-sm uppercase text-slate-400">
                                <tr>
                                    <th class="px-6 py-4">Date du Versement</th>
                                    <th class="px-6 py-4">Méthode</th>
                                    <th class="px-6 py-4 text-right">Montant Versé</th>
                                    <th class="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->reglements as $reglement)
                                    <tr class="border-t border-border-dark">
                                        <td class="px-6 py-4 text-white">{{ $reglement->date_reglement ? \Carbon\Carbon::parse($reglement->date_reglement)->format('d/m/Y') : 'N/A' }}</td>
                                        <td class="px-6 py-4 text-white capitalize">{{ $reglement->methode_paiement }}</td>
                                        <td class="px-6 py-4 text-right font-mono text-green-400">{{ number_format($reglement->montant_verse, 0, ',', ' ') }} FCFA</td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('client.reglements.bordereau', $reglement) }}" target="_blank" class="font-bold text-sm text-blue-400 hover:text-blue-300">Voir le Bordereau</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-8 text-slate-400">Aucun versement n'a encore été enregistré pour cette commande.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Colonne latérale : Résumé Financier et d'Information --}}
            <div class="lg:col-span-1 space-y-8">
                <div class="bg-dark-card border border-border-dark rounded-lg p-6 h-fit">
                    <h3 class="text-2xl font-teko text-white mb-4 border-b border-border-dark pb-4">Résumé</h3>
                    <div class="space-y-3 text-sm">
                        <p class="flex justify-between text-slate-300"><span>Date de Commande :</span><span class="font-bold text-white">{{ $order->created_at->format('d/m/Y') }}</span></p>
                        
                        <p class="flex justify-between text-slate-300"><span>Statut Commande :</span><span class="font-bold text-white">{{ $order->statut->getLabel() }}</span></p>
                        
                        <p class="flex justify-between text-slate-300"><span>Statut Paiement :</span><span class="font-bold text-white capitalize">{{ $order->statut_paiement }}</span></p>
                        <p class="flex justify-between text-slate-300"><span>Point de Vente :</span><span class="font-bold text-white text-right">{{ $order->pointDeVente->nom }}</span></p>
                        @if($order->livreur)
                        <p class="flex justify-between text-slate-300"><span>Livreur :</span><span class="font-bold text-white">{{ $order->livreur->full_name }}</span></p>
                        @endif
                    </div>
                    <div class="mt-6 pt-6 border-t border-border-dark space-y-3 text-sm">
                        <p class="flex justify-between text-slate-300"><span>Total de la Commande :</span><span class="font-mono text-white">{{ number_format($order->montant_total, 0, ',', ' ') }} FCFA</span></p>
                        <p class="flex justify-between text-slate-300"><span>Montant Total Payé :</span><span class="font-mono text-green-400">{{ number_format($order->montant_paye, 0, ',', ' ') }} FCFA</span></p>
                        <p class="flex justify-between text-slate-300 text-lg"><span>Solde Dû :</span><span class="font-mono font-bold text-amber-400">{{ number_format($order->montant_total - $order->montant_paye, 0, ',', ' ') }} FCFA</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>