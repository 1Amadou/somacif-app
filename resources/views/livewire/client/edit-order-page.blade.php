<div class="py-24">
    <div class="container mx-auto px-6">
        <div class="mb-8">
            <a href="{{ route('client.orders.show', $order) }}" class="text-slate-400 hover:text-white text-sm"><i class="fas fa-arrow-left mr-2"></i>Annuler et retourner au détail</a>
            <h1 class="text-5xl font-teko uppercase text-white mt-2">Modifier la Commande</h1>
            <p class="font-mono text-slate-300">{{ $order->numero_commande }}</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-dark-card border border-border-dark rounded-lg">
                <div class="p-6">
                    <ul class="space-y-4">
                        @forelse ($items as $itemId => $item)
                        <li class="flex items-center border-b border-border-dark pb-4 last:border-b-0 last:pb-0">
                            <div class="flex-grow">
                                <p class="font-bold text-white">{{ $item['name'] }}</p>
                                <p class="text-sm text-slate-400">{{ number_format($item['price'], 0, ',', ' ') }} FCFA / unité</p>
                                {{-- AMÉLIORATION : On affiche le stock --}}
                                <p class="text-xs text-blue-400">Stock disponible: {{ $item['stock_dispo'] }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <input type="number" min="1" max="{{ $item['stock_dispo'] }}" wire:model.live="items.{{ $itemId }}.quantity" class="w-20 form-input text-center">
                                <button wire:click="removeItem({{ $itemId }})" class="text-red-500 hover:text-red-400"><i class="fas fa-trash"></i></button>
                            </div>
                        </li>
                        @empty
                        <p class="text-slate-400 text-center py-8">Cette commande est vide.</p>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="lg:col-span-1 bg-dark-card border border-border-dark rounded-lg p-6 h-fit">
                <h3 class="text-2xl font-teko text-white mb-4 border-b border-border-dark pb-4">Nouveau Récapitulatif</h3>
                <div class="mt-6 pt-6 border-t border-border-dark">
                    <div class="flex justify-between font-bold text-lg text-white">
                        <span>Nouveau Total</span>
                        <span>{{ number_format($newTotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
                <div class="mt-6">
                    <button wire:click="saveChanges" wire:loading.attr="disabled" class="btn btn-primary w-full text-center">
                        Enregistrer les modifications
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>