<?php

namespace App\Livewire\Client;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowOrderPage extends Component
{
    public Order $order;

    // S'exécute au chargement, récupère la commande et vérifie les droits d'accès
    public function mount(Order $order)
    {
        // Sécurité : S'assure que le client connecté est bien le propriétaire de la commande
        if ($order->client_id !== Auth::guard('client')->id()) {
            abort(403, 'Accès non autorisé');
        }

        // On pré-charge toutes les relations nécessaires pour optimiser les requêtes
        $this->order = $order->load(['items.uniteDeVente.product', 'pointDeVente', 'reglements', 'livreur']);
    }

    public function render()
    {
        return view('livewire.client.show-order-page')
            ->layout('components.layouts.app');
    }
}