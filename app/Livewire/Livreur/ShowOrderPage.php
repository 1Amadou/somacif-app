<?php

namespace App\Livewire\Livreur;

use App\Enums\OrderStatusEnum; // <-- AJOUT
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowOrderPage extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        if ($order->livreur_id !== Auth::guard('livreur')->id()) {
            abort(403, 'Accès non autorisé');
        }
        $this->order = $order->load(['items.uniteDeVente.product', 'client', 'pointDeVente']);
    }

    // ACTION : Le livreur accepte la mission
    public function startDelivery()
    {
        // CORRECTION : On utilise l'Enum
        if ($this->order->statut === OrderStatusEnum::EN_PREPARATION) {
            $this->order->update(['statut' => OrderStatusEnum::EN_COURS_LIVRAISON]);
            
            // La notification est maintenant gérée par OrderObserver, on n'a plus besoin du code ici.
            
            session()->flash('success', 'Livraison démarrée ! Le client sera notifié.');
            return redirect()->route('livreur.dashboard');
        }
    }

    // ACTION : Le livreur confirme la livraison (après le client)
    public function confirmDelivery()
    {
        // CORRECTION : La condition a été améliorée.
        // Le livreur ne peut confirmer que si le client a déjà confirmé (statut "Livrée")
        if ($this->order->statut === OrderStatusEnum::LIVREE) {
            $this->order->update(['livreur_confirmed_at' => now()]);
            
            // La notification à l'admin est gérée par OrderObserver.
            
            session()->flash('success', 'Commande marquée comme livrée ! Mission terminée.');
            return redirect()->route('livreur.dashboard');
        }
    }

    public function render()
    {
        return view('livewire.livreur.show-order-page')
            ->layout('components.layouts.livreur');
    }
}