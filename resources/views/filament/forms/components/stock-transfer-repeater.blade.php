<div
    x-data="{
        inventory: [],
        sourceId: @entangle($getStatePath()).source_point_de_vente_id,
        init() {
            this.$watch('sourceId', (newSourceId) => {
                if (newSourceId) {
                    // On appelle une méthode dans notre composant PHP pour charger le stock
                    this.$wire.call('loadSourceInventory', newSourceId);
                } else {
                    this.inventory = [];
                }
            });
        },
        updateDetails(uniteId, quantite) {
            // Met à jour l'état du formulaire Filament
            let state = this.$wire.get('data.details') || {};
            if (quantite > 0) {
                state[uniteId] = { quantite: quantite };
            } else {
                delete state[uniteId];
            }
            this.$wire.set('data.details', state);
        }
    }"
    x-on:inventory-loaded.window="inventory = $event.detail.inventory"
>
    <div class="rounded-lg border border-gray-300 dark:border-gray-700">
        <table class="w-full text-left">
            <thead class="bg-gray-50 dark:bg-gray-800 text-sm">
                <tr>
                    <th class="px-4 py-2">Article</th>
                    <th class="px-4 py-2">Stock Source</th>
                    <th class="px-4 py-2">Quantité à Transférer</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                <template x-if="inventory.length === 0">
                    <tr>
                        <td colspan="3" class="text-center py-4 text-gray-500">
                            Sélectionnez un point de vente source pour voir son stock.
                        </td>
                    </tr>
                </template>
                <template x-for="item in inventory" :key="item.unite_de_vente_id">
                    <tr class="text-sm">
                        <td class="px-4 py-2 font-medium text-gray-900 dark:text-white" x-text="item.nom_complet"></td>
                        <td class="px-4 py-2 text-gray-500" x-text="item.quantite_stock"></td>
                        <td class="px-4 py-2">
                            <input 
                                type="number" 
                                min="0"
                                :max="item.quantite_stock"
                                x-on:input.debounce.500ms="updateDetails(item.unite_de_vente_id, $event.target.value)"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>