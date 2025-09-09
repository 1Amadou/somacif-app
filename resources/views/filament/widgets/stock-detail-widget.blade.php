<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Section pour les totaux globaux --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-filament::card class="!bg-primary-50 dark:!bg-primary-500/10">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-full bg-primary-500/20 text-primary-600">
                        <x-heroicon-o-archive-box class="w-6 h-6"/>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Unités Totales en Stock</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalItems) }}</p>
                    </div>
                </div>
            </x-filament::card>
            <x-filament::card class="!bg-warning-50 dark:!bg-warning-500/10">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-full bg-warning-500/20 text-warning-600">
                        <x-heroicon-o-banknotes class="w-6 h-6"/>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Valeur du Stock (Coût)</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->valeurStock, 0, ',', ' ') }} FCFA</p>
                    </div>
                </div>
            </x-filament::card>
            <x-filament::card class="!bg-success-50 dark:!bg-success-500/10">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-full bg-success-500/20 text-success-600">
                        <x-heroicon-o-arrow-trending-up class="w-6 h-6"/>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Revenu Potentiel (Vente)</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->revenuPotentiel, 0, ',', ' ') }} FCFA</p>
                    </div>
                </div>
            </x-filament::card>
        </div>

        <hr class="my-6 border-gray-200 dark:border-gray-700">

        {{-- Section pour les cartes de chaque produit --}}
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Détail du Stock par Produit</h3>
            @if($inventaire->isNotEmpty())
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach($inventaire as $item)
                        <div class="p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex flex-col items-center justify-center text-center">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200 leading-tight">{{ $item->uniteDeVente->nom_complet }}</p>
                            <p class="text-3xl font-bold text-primary-600 dark:text-primary-400 mt-2">{{ number_format($item->quantite_stock) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">en stock</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 dark:text-gray-400 py-4">L'entrepôt principal est vide.</p>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
