<?php

namespace App\Livewire\Livreur;

use App\Models\Order;
use App\Notifications\ClientOrderInTransitNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowOrderPage extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        // Sécurité : le livreur ne peut voir que les commandes qui lui sont assignées.
        if ($order->livreur_id !== Auth::guard('livreur')->id()) {
            abort(403, 'Accès non autorisé');
        }
        $this->order = $order->load(['items.uniteDeVente.product', 'client', 'pointDeVente']);
    }

    // ACTION : Le livreur a récupéré le colis
    public function startDelivery()
    {
        if ($this->order->statut === 'en_preparation') {
            $this->order->update(['statut' => 'en_cours_livraison']);
            
            // Notifier le client que sa commande est en route
            $this->order->client->notify(new ClientOrderInTransitNotification($this->order));

            session()->flash('success', 'Livraison démarrée ! Le client a été notifié.');
            return redirect()->route('livreur.dashboard');
        }
    }

    // ACTION : Le livreur a livré le colis
    public function markAsDelivered()
    {
        if ($this->order->statut === 'en_cours_livraison') {
            $this->order->update(['statut' => 'livree', 'livreur_confirmed_at' => now()]);
            
            // Notifier l'admin que la livraison est terminée
            // ...

            session()->flash('success', 'Commande marquée comme livrée !');
            return redirect()->route('livreur.dashboard');
        }
    }

    public function render()
    {
        return view('livewire.livreur.show-order-page')
            ->layout('components.layouts.livreur');
    }
}