<div class="py-12">
    <div class="container mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Bonjour, {{ $livreur->prenom }}</h1>
            <p class="text-slate-400">Voici vos missions de livraison.</p>
        </div>

        <h2 class="text-2xl font-teko text-white mb-4 uppercase">Missions Actives</h2>
        <div class="space-y-4 mb-12">
            @forelse ($missionsActives as $mission)
                <a href="{{ route('livreur.orders.show', $mission) }}" wire:navigate class="block bg-dark-card border border-border-dark rounded-lg p-4 transition-transform hover:scale-[1.02]">
                    <div class="flex justify-between items-center">
                        <span class="font-mono text-white">{{ $mission->numero_commande }}</span>
                        
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            @switch($mission->statut->getColor())
                                @case('warning') bg-amber-500/20 text-amber-400 @break
                                @case('info') bg-blue-500/20 text-blue-400 @break
                                @default bg-gray-500/20 text-gray-400
                            @endswitch
                        ">
                            {{ $mission->statut->getLabel() }}
                        </span>

                    </div>
                    <div class="mt-2 text-slate-300">
                        <p class="font-bold text-white">{{ $mission->client->nom }}</p>
                        <p class="text-sm"><i class="fas fa-map-marker-alt mr-2"></i>{{ $mission->pointDeVente->nom }}</p>
                    </div>
                </a>
            @empty
                <div class="bg-dark-card border border-border-dark rounded-lg p-12 text-center">
                    <p class="text-slate-400">Vous n'avez aucune mission de livraison active pour le moment.</p>
                </div>
            @endforelse
        </div>

        <h2 class="text-2xl font-teko text-white mb-4 uppercase">Historique des Livraisons</h2>
        <div class="bg-dark-card border border-border-dark rounded-lg">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                     <thead class="text-sm uppercase text-slate-400 border-b border-border-dark">
                        <tr>
                            <th class="px-6 py-4">N° Commande</th>
                            <th class="px-6 py-4">Client</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Statut Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historiqueMissions as $mission)
                            <tr class="border-b border-border-dark last:border-b-0">
                                <td class="px-6 py-4 font-mono text-white">{{ $mission->numero_commande }}</td>
                                <td class="px-6 py-4 text-white">{{ $mission->client->nom }}</td>
                                <td class="px-6 py-4 text-slate-300">{{ $mission->updated_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">
                                     <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        @switch($mission->statut->getColor())
                                            @case('primary') bg-green-500/20 text-green-400 @break
                                            @case('danger') bg-red-500/20 text-red-400 @break
                                            @default bg-gray-500/20 text-gray-400
                                        @endswitch
                                    ">
                                        {{ $mission->statut->getLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-12 text-slate-400">Aucun historique de livraison.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if($historiqueMissions->hasPages())
                <div class="p-6 border-t border-border-dark">
                    {{ $historiqueMissions->links() }}
                </div>
            @endif
        </div>

    </div>
</div>