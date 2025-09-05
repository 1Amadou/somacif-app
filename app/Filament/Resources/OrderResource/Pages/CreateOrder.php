<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatusEnum;
// CORRECTION : On importe la bonne ressource
use App\Filament\Resources\OrderResource; 
use App\Models\UniteDeVente;
use App\Services\StockManager;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateOrder extends CreateRecord
{
    // CORRECTION : On lie cette page à la ressource "OrderResource", et non "ArrivageResource"
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    // On utilise une action personnalisée pour inclure la validation de stock
    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()->action('createOrder');
    }

    public function createOrder(): void
    {
        $data = $this->form->getState();

        // Si on tente de valider directement, on vérifie le stock
        if ($data['statut'] === OrderStatusEnum::VALIDEE->value) {
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
        
        $this->create();
    }
}