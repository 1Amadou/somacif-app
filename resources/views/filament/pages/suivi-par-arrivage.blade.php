<x-filament-panels::page>
    {{-- Le formulaire de sélection reste le même --}}
    {{ $this->form }}

    @if($data = $this->getSelectedArrivageData())
        <div id="report-content">
            {{-- En-tête du rapport --}}
            <div class="fi-section-header flex flex-col md:flex-row md:items-center justify-between gap-x-3 bg-gray-50 dark:bg-gray-800 p-4 rounded-xl mt-6">
                <div>
                    <h2 class="fi-section-header-heading text-lg font-bold text-gray-950 dark:text-white">
                        Rapport de Suivi pour l'Arrivage : {{ $data['arrivage']->numero_bon_livraison }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Du {{ $data['arrivage']->date_arrivage->format('d/m/Y') }} - Fournisseur: {{ $data['arrivage']->fournisseur->nom_entreprise }}
                    </p>
                </div>
                <div class="shrink-0">
                    <span class="inline-flex items-center rounded-md px-3 py-1 text-sm font-medium ring-1 ring-inset {{ $data['statut'] === 'Clôturé' ? 'bg-success-50 dark:bg-success-500/10 text-success-700 dark:text-success-400 ring-success-600/20 dark:ring-success-500/20' : 'bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-400 ring-primary-600/20 dark:ring-primary-500/20' }}">
                        Statut: {{ $data['statut'] }}
                    </span>
                </div>
            </div>

            {{-- Indicateurs financiers globaux --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-6">
                <x-filament::card>
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-full bg-danger-500/20 text-danger-600">
                            <x-heroicon-o-arrow-trending-down class="w-6 h-6"/>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Coût Total d'Achat</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($data['totalCoutAchat'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </x-filament::card>
                <x-filament::card>
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-full bg-success-500/20 text-success-600">
                            <x-heroicon-o-arrow-trending-up class="w-6 h-6"/>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Revenu Généré (Ventes)</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($data['totalRevenuGenere'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </x-filament::card>
                <x-filament::card>
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-full {{ $data['margeGlobale'] >= 0 ? 'bg-success-500/20 text-success-600' : 'bg-danger-500/20 text-danger-600' }}">
                            <x-heroicon-o-scale class="w-6 h-6"/>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Marge Brute Actuelle</p>
                            <p class="text-2xl font-bold {{ $data['margeGlobale'] >= 0 ? 'text-gray-900 dark:text-white' : 'text-danger-600' }}">{{ number_format($data['margeGlobale'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </x-filament::card>
            </div>

            {{-- Tableau résumé par produit --}}
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mt-6">Résumé par Produit</h3>
            <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Produit</th>
                            <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Qté Reçue</th>
                            <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Stock Total Actuel</th>
                            <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Qté Vendue</th>
                            <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Revenu Généré</th>
                            <th class="px-4 py-2 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Marge</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($data['reportData'] as $item)
                            <tr class="text-sm">
                                <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">{{ $item['nom_complet'] }}</td>
                                <td class="px-4 py-2 text-right font-mono text-gray-700 dark:text-gray-200">{{ number_format($item['quantite_recue']) }}</td>
                                <td class="px-4 py-2 text-right font-mono font-bold text-gray-900 dark:text-white">{{ number_format($item['stock_total_actuel']) }}</td>
                                <td class="px-4 py-2 text-right font-mono text-amber-600 dark:text-amber-400">{{ number_format($item['quantite_vendue']) }}</td>
                                <td class="px-4 py-2 text-right font-mono text-primary-600 dark:text-primary-400">{{ number_format($item['revenu_genere']) }}</td>
                                <td class="px-4 py-2 text-right font-mono font-bold {{ $item['marge_sur_ventes'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">{{ number_format($item['marge_sur_ventes']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Aucun article trouvé pour cet arrivage.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Section d'analyse détaillée --}}
            <div class="mt-8 space-y-8">
                @foreach ($data['reportData'] as $item)
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                        <h4 class="text-md font-bold text-primary-600 dark:text-primary-400">{{ $item['nom_complet'] }}</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            {{-- Colonne Détail des ventes --}}
                            <div>
                                <h5 class="font-semibold text-sm mb-2">Détail des Ventes ({{ $item['quantite_vendue'] }} unités)</h5>
                                @if($item['ventes_detaillees']->isNotEmpty())
                                    <ul class="text-xs space-y-1">
                                        @foreach($item['ventes_detaillees'] as $vente)
                                            <li class="flex justify-between">
                                                <span>{{ $vente->quantite_totale }} unités vendues à</span>
                                                <span class="font-mono">{{ number_format($vente->prix_de_vente_unitaire, 0, ',', ' ') }} FCFA</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-gray-500">Aucune vente pour ce produit.</p>
                                @endif
                            </div>

                            {{-- Colonne Répartition du stock --}}
                            <div>
                                <h5 class="font-semibold text-sm mb-2">Stock Restant ({{ $item['stock_total_actuel'] }} unités)</h5>
                                <ul class="text-xs space-y-1">
                                    <li class="flex justify-between">
                                        <span>Entrepôt Principal</span>
                                        <span class="font-mono font-bold">{{ number_format($item['stock_entrepot_actuel']) }}</span>
                                    </li>
                                    @foreach($item['repartition_stock'] as $stock)
                                        <li class="flex justify-between">
                                            <span>PDV: {{ $stock['nom_pdv'] }}</span>
                                            <span class="font-mono">{{ number_format($stock['quantite']) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-filament-panels::page>