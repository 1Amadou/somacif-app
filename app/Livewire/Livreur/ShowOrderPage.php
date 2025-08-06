<?php

namespace App\Livewire\Livreur;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowOrderPage extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        // Sécurité : on vérifie que la commande est bien assignée au livreur connecté
        if ($order->livreur_id !== Auth::guard('livreur')->id()) {
            abort(403, 'Action non autorisée.');
        }
        $this->order = $order;
    }

    public function confirmDeliveryByLivreur()
    {
        $this->order->update([
            'livreur_confirmed_at' => now(),
        ]);

        session()->flash('success', 'Livraison confirmée de votre côté.');
    }

    public function render()
    {
        return view('livewire.livreur.show-order-page')
            ->layout('components.layouts.livreur', ['metaTitle' => 'Détail Livraison ' . $this->order->numero_commande]);
    }
}