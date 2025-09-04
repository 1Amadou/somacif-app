<x-filament-panels::page>
    {{ $this->form }}

    @if($this->getSelectedArrivageData())
        <div class="fi-section-header flex flex-row items-center gap-x-3 bg-gray-50 dark:bg-gray-800 p-4 rounded-md">
            <h2 class="fi-section-header-heading text-lg font-bold text-gray-950 dark:text-white">
                Rapport de Rentabilité pour l'Arrivage : {{ $this->getSelectedArrivageData()['arrivage']->numero_bon_livraison }}
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-6">
            <!-- Cartes de résumé -->
            @php $data = $this->getSelectedArrivageData(); @endphp

            <x-filament::card class="bg-primary-500 text-white dark:bg-primary-600">
                <div class="flex items-center gap-4">
                    <x-filament::icon-button icon="heroicon-o-currency-dollar" class="text-white" color="inherit" size="lg" />
                    <div>
                        <p class="text-sm">Coût Total d'Achat</p>
                        <p class="text-2xl font-bold">{{ number_format($data['totalCoutAchat'], 0, ',', ' ') }} FCFA</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card class="bg-success-500 text-white dark:bg-success-600">
                <div class="flex items-center gap-4">
                    <x-filament::icon-button icon="heroicon-o-banknotes" class="text-white" color="inherit" size="lg" />
                    <div>
                        <p class="text-sm">Montant Total des Ventes</p>
                        <p class="text-2xl font-bold">{{ number_format($data['totalMontantVentes'], 0, ',', ' ') }} FCFA</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card class="bg-warning-500 text-white dark:bg-warning-600">
                <div class="flex items-center gap-4">
                    <x-filament::icon-button icon="heroicon-o-chart-bar" class="text-white" color="inherit" size="lg" />
                    <div>
                        <p class="text-sm">Marge Brute</p>
                        <p class="text-2xl font-bold">{{ number_format($data['benefice'], 0, ',', ' ') }} FCFA</p>
                    </div>
                </div>
            </x-filament::card>
        </div>

        <!-- Tableau HTML classique -->
        <div class="overflow-x-auto rounded-md shadow-sm border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Produit</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Qté Reçue</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Prix Achat Unitaire</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Qté Vendue Totale</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Montant Ventes Total</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Stock Entrepôt</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Stock Clients</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($data['reportData'] as $item)
                        <tr>
                            <td class="px-4 py-2">{{ $item['nom_produit'] }}</td>
                            <td class="px-4 py-2">{{ $item['quantite_recue_arrivage'] }}</td>
                            <td class="px-4 py-2">{{ number_format($item['prix_achat_unitaire'], 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-2">{{ $item['quantite_vendue_total'] }}</td>
                            <td class="px-4 py-2">{{ number_format($item['montant_ventes_total'], 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-2">{{ $item['stock_entrepot_actuel'] }}</td>
                            <td class="px-4 py-2">{{ $item['stock_chez_clients_total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
