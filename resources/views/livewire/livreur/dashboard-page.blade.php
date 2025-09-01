<div class="py-12">
    <div class="container mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Bonjour, {{ $livreur->prenom }}</h1>
            <p class="text-slate-400">Voici vos missions de livraison pour aujourd'hui.</p>
        </div>

        <div class="space-y-4">
            @forelse ($missions as $mission)
                <a href="{{ route('livreur.orders.show', $mission) }}" wire:navigate class="block bg-dark-card border border-border-dark rounded-lg p-4 transition-transform hover:scale-[1.02]">
                    <div class="flex justify-between items-center">
                        <span class="font-mono text-white">{{ $mission->numero_commande }}</span>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $mission->statut === 'en_preparation' ? 'bg-amber-500/20 text-amber-400' : 'bg-blue-500/20 text-blue-400' }}">
                            {{ $mission->statut === 'en_preparation' ? 'À Récupérer' : 'En Route' }}
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
    </div>
</div>