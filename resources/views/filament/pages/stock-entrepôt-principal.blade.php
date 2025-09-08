<x-filament-panels::page>
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">{{ $this->titre }}</h1>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">Liste de toutes les unités de vente et leur stock actuel dans l'entrepôt principal.</p>
            </div>
        </div>
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">Unité de Vente (Nom Complet)</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 dark:text-white">Quantité en Stock</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                {{-- *** CORRECTION : Utilisation de la variable '$inventaire' *** --}}
                                @forelse ($inventaire as $ligne)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">{{ $ligne->uniteDeVente->nom_complet }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-mono {{ $ligne->quantite_stock > 0 ? 'text-gray-500 dark:text-gray-200' : 'text-red-500' }}">{{ number_format($ligne->quantite_stock, 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-12 text-gray-500 dark:text-gray-400">
                                            Aucun stock dans l'entrepôt principal.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>