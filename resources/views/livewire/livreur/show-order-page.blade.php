<div class="py-12">
    <div class="container mx-auto px-4">
        <div class="mb-6">
            <a href="{{ route('livreur.dashboard') }}" wire:navigate class="text-slate-400 hover:text-white text-sm"><i class="fas fa-arrow-left mr-2"></i>Retour aux missions</a>
            <h1 class="text-3xl font-bold text-white mt-2">Mission : {{ $order->numero_commande }}</h1>
        </div>

        <div class="space-y-6">
            <div class="bg-dark-card border border-border-dark rounded-lg p-4">
                <h3 class="text-lg font-bold text-white mb-2">Informations Client</h3>
                <p class="text-slate-300">{{ $order->client->nom }}</p>
                <p class="text-slate-400 text-sm">{{ $order->pointDeVente->adresse }}</p>
                <a href="tel:{{ $order->client->telephone }}" class="block mt-2 text-blue-400 hover:text-blue-300"><i class="fas fa-phone mr-2"></i>Appeler le client</a>
            </div>

            <div class="bg-dark-card border border-border-dark rounded-lg p-4">
                <h3 class="text-lg font-bold text-white mb-2">Contenu à livrer</h3>
                <ul class="text-sm text-slate-300 list-disc list-inside">
                    @foreach ($order->items as $item)
                        <li>{{ $item->quantite }} x {{ $item->uniteDeVente->product->nom }} ({{ $item->uniteDeVente->nom_unite }})</li>
                    @endforeach
                </ul>
            </div>

            <div class="mt-6">
                @if($order->statut === 'en_preparation')
                    <button wire:click="startDelivery" wire:confirm="Confirmer la récupération du colis ?" class="w-full btn-primary text-lg">
                        Colis Récupéré, Démarrer la Course
                    </button>
                @elseif($order->statut === 'en_cours_livraison')
                    <button wire:click="markAsDelivered" wire:confirm="Confirmer que le client a bien réceptionné la commande ?" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold tracking-widest uppercase py-4 rounded-md">
                        Le Client a Confirmé la Réception
                    </button>
                @else
                    <p class="text-center text-green-400 p-4 bg-green-900/50 rounded-lg">Cette mission est terminée.</p>
                @endif
            </div>
        </div>
    </div>
</div>