<?php

namespace App\Filament\Resources\ReglementResource\Pages;

use App\Filament\Resources\ReglementResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditReglement extends EditRecord
{
    protected static string $resource = ReglementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $order = Order::with('items.uniteDeVente')->find($data['order_id']);
        $details = collect($data['details'] ?? []);
        $errors = [];
        $ventesParArticle = $details->groupBy('unite_de_vente_id');

        foreach ($ventesParArticle as $uniteId => $ventes) {
            $totalVendu = $ventes->sum('quantite_vendue');
            $itemCommande = $order->items->firstWhere('unite_de_vente_id', $uniteId);
            if (!$itemCommande) continue;
            
            $quantiteCommandee = $itemCommande->quantite;

            if ($totalVendu > $quantiteCommandee) {
                $errors[] = "Pour l'article '{$itemCommande->uniteDeVente->nom_complet}', la quantité totale vendue ({$totalVendu}) dépasse la quantité reçue ({$quantiteCommandee}).";
            }
        }

        if (!empty($errors)) {
            Notification::make()->title('Erreur de Quantité')->danger()->body(implode("\n", $errors))->send();
            throw new Halt();
        }

        return $data;
    }
}