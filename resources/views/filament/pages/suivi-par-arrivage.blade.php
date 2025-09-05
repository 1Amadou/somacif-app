<x-filament-panels::page>
    {{ $this->form }}

    @if($data = $this->getSelectedArrivageData())
        <div class="fi-section-header flex flex-row items-center gap-x-3 bg-gray-50 dark:bg-gray-800 p-4 rounded-md mt-6">
            <h2 class="fi-section-header-heading text-lg font-bold text-gray-950 dark:text-white">
                Rapport de Suivi pour l'Arrivage : {{ $data['arrivage']->numero_bon_livraison }}
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-6">
            <x-filament::card>
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-full bg-primary-500/20 text-primary-600">
                        <x-heroicon-o-arrow-down-tray class="w-6 h-6"/>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Quantité Totale Reçue</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($data['totalQuantiteRecue'], 0, ',', ' ') }} unités</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center gap-4">
                     <div class="p-3 rounded-full bg-success-500/20 text-success-600">
                        <x-heroicon-o-archive-box class="w-6 h-6"/>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Stock Total Restant</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($data['totalStockRestant'], 0, ',', ' ') }} unités</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                 <div class="flex items-center gap-4">
                     <div class="p-3 rounded-full bg-warning-500/20 text-warning-600">
                        <x-heroicon-o-arrow-up-tray class="w-6 h-6"/>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Quantité Totale Sortie</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($data['totalQuantiteSortie'], 0, ',', ' ') }} unités</p>
                    </div>
                </div>
            </x-filament::card>
        </div>

        <div class="overflow-x-auto rounded-md shadow-sm border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Produit</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Qté Reçue</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Stock Entrepôt</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Stock Clients</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Stock Total Actuel</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Qté Sortie</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($data['reportData'] as $item)
                        <tr class="text-sm">
                            <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">{{ $item['nom_complet'] }}</td>
                            <td class="px-4 py-2 text-right font-mono text-gray-700 dark:text-gray-200">{{ $item['quantite_recue'] }}</td>
                            <td class="px-4 py-2 text-right font-mono text-blue-600 dark:text-blue-400">{{ $item['stock_entrepot_actuel'] }}</td>
                            <td class="px-4 py-2 text-right font-mono text-violet-600 dark:text-violet-400">{{ $item['stock_clients_actuel'] }}</td>
                            <td class="px-4 py-2 text-right font-mono font-bold text-gray-900 dark:text-white">{{ $item['stock_total_actuel'] }}</td>
                            <td class="px-4 py-2 text-right font-mono text-amber-600 dark:text-amber-400">{{ $item['quantite_sortie'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>