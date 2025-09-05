<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatusEnum;
// CORRECTION : On importe la bonne ressource
use App\Filament\Resources\OrderResource; 
use App\Models\UniteDeVente;
use App\Services\StockManager;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    // CORRECTION : On lie cette page à la ressource "OrderResource", et non "ArrivageResource"
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // On utilise une action personnalisée pour inclure la validation de stock
    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()->action('saveOrder');
    }

    public function saveOrder(): void
    {
        $data = $this->form->getState();
        // On récupère le statut de la commande AVANT la modification
        $originalStatus = $this->getRecord()->statut;

        // On vérifie le stock uniquement si on essaie de passer la commande à "Validée"
        if ($data['statut'] === OrderStatusEnum::VALIDEE->value && $originalStatus !== OrderStatusEnum::VALIDEE) {
            $stockManager = app(StockManager::class);
            $items = $data['items'] ?? [];
            $errors = [];

            foreach ($items as $item) {
                $unite = UniteDeVente::find($item['unite_de_vente_id']);
                $quantiteDemandee = $item['quantite'];
                $stockDisponible = $stockManager->getInventoryStock($unite, null);

                if ($stockDisponible < $quantiteDemandee) {
                    $errors[] = "Stock insuffisant pour '{$unite->nom_complet}' (Demandé: {$quantiteDemandee}, Disponible: {$stockDisponible})";
                }
            }

            if (!empty($errors)) {
                Notification::make()
                    ->title('Validation échouée : Stock Insuffisant')
                    ->danger()
                    ->body(implode("\n", $errors))
                    ->persistent()
                    ->send();
                return;
            }
        }
        
        $this->save();
    }
}