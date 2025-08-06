<div class="py-24">
    <div class="container mx-auto px-6">
        <a href="{{ route('client.dashboard') }}" class="text-sm brand-red hover:text-red-400"><i class="fas fa-arrow-left mr-2"></i>Retour au tableau de bord</a>
        <h1 class="text-5xl font-teko uppercase text-white mt-4">Modifier la Commande</h1>
        <p class="text-slate-400">Commande <span class="font-mono text-white">{{ $order->numero_commande }}</span></p>

        <div class="mt-8 bg-dark-card border border-border-dark rounded-lg p-8">
            <h2 class="text-2xl font-teko text-white mb-4">Articles de votre commande</h2>
            <div class="space-y-4">
                @forelse($items as $itemId => $item)
                    <div class="flex justify-between items-center border-b border-border-dark pb-4">
                        <div>
                            <p class="font-bold text-white">{{ $item['nom_produit'] }}</p>
                            <p class="text-sm text-slate-400">Calibre : {{ $item['calibre'] }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <input type="number" min="1" wire:model="items.{{ $itemId }}.quantite" class="w-20 form-input text-center">
                            <button wire:click="removeItem({{ $itemId }})" wire:confirm="Êtes-vous sûr de vouloir supprimer cet article ?" class="text-slate-500 hover:text-red-500">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-slate-400 py-8">Cette commande est vide.</p>
                @endforelse
            </div>

            @if(!empty($items))
                <div class="text-right mt-6">
                    <button wire:click="saveOrder" wire:loading.attr="disabled" class="bg-green-600 hover:bg-green-700 text-white font-bold tracking-widest uppercase py-3 px-8 rounded-sm disabled:opacity-50">
                        <span wire:loading.remove>Sauvegarder les modifications</span>
                        <span wire:loading>Sauvegarde...</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>