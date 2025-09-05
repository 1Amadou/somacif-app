<?php

namespace App\Filament\Resources\ReglementResource\Pages;

use App\Filament\Resources\ReglementResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Exceptions\Halt;

class CreateReglement extends CreateRecord
{
    protected static string $resource = ReglementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

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