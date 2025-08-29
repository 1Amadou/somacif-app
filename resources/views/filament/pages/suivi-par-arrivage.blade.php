<x-filament-panels::page>

    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    @php
        $data = $this->getSelectedArrivageData();
    @endphp

    @if ($data)
        <div class="mt-6 space-y-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">

            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold">
                        Rapport pour l'arrivage N° {{ $data['arrivage']->numero_bon_livraison }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        Date: {{ $data['arrivage']->date_arrivage->format('d/m/Y') }} | 
                        Fournisseur: {{ $data['arrivage']->fournisseur->nom_entreprise }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-semibold">Montant Total Encaissé</p>
                    <p class="text-2xl font-bold text-primary-600">
                        {{ number_format($data['totalEncaisse'], 0, ',', ' ') }} CFA
                    </p>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-white/10 pt-6">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Produit</th>
                        <th scope="col" class="px-6 py-3 text-center">Qté Reçue</th>
                        <th scope="col" class="px-6 py-3 text-center">Qté Vendue</th>
                        <th scope="col" class="px-6 py-3 text-center">Stock (Clients)</th>
                        <th scope="col" class="px-6 py-3 text-center">Stock (Entrepôt)</th>
                        <th scope="col" class="px-6 py-3 text-right">Montant Ventes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data['reportData'] as $row)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4 font-medium">{{ $row['nom_produit'] }}</td>
                            <td class="px-6 py-4 text-center font-bold">{{ $row['quantite_recue'] }}</td>
                            <td class="px-6 py-4 text-center text-green-600">{{ $row['quantite_vendue'] }}</td>
                            <td class="px-6 py-4 text-center text-blue-600">{{ $row['stock_chez_clients'] }}</td>
                            <td class="px-6 py-4 text-center text-orange-600">{{ $row['stock_entrepot'] }}</td>
                            <td class="px-6 py-4 text-right font-semibold">{{ number_format($row['montant_ventes'], 0, ',', ' ') }} CFA</td>
                        </tr>
                    @empty
                        @endforelse
                </tbody>
                </table>
            </div>

        </div>
    @else
        <div class="mt-6 text-center text-gray-500">
            <p>Veuillez sélectionner un arrivage pour afficher le rapport de suivi.</p>
        </div>
    @endif

</x-filament-panels::page>