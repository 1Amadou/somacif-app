<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Le formulaire de sélection de l'arrivage --}}
        <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
            {{ $this->form }}
        </div>

        {{-- Le conteneur pour le rapport, qui s'affiche seulement si un arrivage est sélectionné --}}
        @if ($this->selectedArrivageId && ($data = $this->getSelectedArrivageData()))
            <div class="space-y-6">
                {{-- Section Résumé de l'Arrivage --}}
                <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Rapport pour l'arrivage : {{ $data['arrivage']->numero_bon_livraison }}
                    </h2>
                    <dl class="mt-4 grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-3">
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fournisseur</dt>
                            <dd class="mt-1 text-lg text-gray-900 dark:text-white">{{ $data['arrivage']->fournisseur->nom_entreprise }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date de réception</dt>
                            <dd class="mt-1 text-lg text-gray-900 dark:text-white">{{ $data['arrivage']->date_arrivage->format('d/m/Y') }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Encaissé (pour ces produits)</dt>
                            <dd class="mt-1 text-2xl font-semibold text-primary-600 dark:text-primary-500">
                                {{ number_format($data['totalEncaisse'], 0, ',', ' ') }} FCFA
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Tableau détaillé des produits --}}
                <div class="overflow-x-auto bg-white rounded-lg shadow dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Produit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Reçu (cet arrivage)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Vendu (total)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Stock Entrepôt (actuel)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Stock Clients (actuel)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Stock Total (actuel)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Montant Ventes (total)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($data['reportData'] as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $item['nom_produit'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-blue-600 font-semibold dark:text-blue-400">{{ $item['quantite_recue_arrivage'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600 font-semibold dark:text-green-400">{{ $item['quantite_vendue_total'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-300">{{ $item['stock_entrepot_actuel'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-300">{{ $item['stock_chez_clients_total'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 font-bold dark:text-white">{{ $item['stock_total_actuel'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-primary-600 font-bold dark:text-primary-500">{{ number_format($item['montant_ventes_total'], 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Aucun produit trouvé pour cet arrivage.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            {{-- Message qui s'affiche si aucun arrivage n'est sélectionné --}}
            <div class="p-6 text-center bg-white rounded-lg shadow dark:bg-gray-800">
                <p class="text-gray-500 dark:text-gray-400">Veuillez sélectionner un arrivage pour afficher son rapport de suivi.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>