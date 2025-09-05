<?php

namespace App\Livewire\Client;

use App\Enums\OrderStatusEnum; // <-- AJOUT
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowOrderPage extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        if ($order->client_id !== Auth::guard('client')->id()) {
            abort(403, 'Accès non autorisé');
        }
        $this->order = $order->load(['items.uniteDeVente.product', 'pointDeVente', 'reglements', 'livreur']);
    }

    // --- NOUVELLE MÉTHODE ---
    public function confirmReception()
    {
        if ($this->order->statut === OrderStatusEnum::EN_COURS_LIVRAISON) {
            $this->order->statut = OrderStatusEnum::LIVREE;
            $this->order->client_confirmed_at = now();
            $this->order->save();
            session()->flash('message', 'Merci d\'avoir confirmé la réception !');
        }
    }

    // --- NOUVELLE MÉTHODE ---
    public function cancelOrder()
    {
        if ($this->order->statut === OrderStatusEnum::EN_ATTENTE) {
            $this->order->statut = OrderStatusEnum::ANNULEE;
            $this->order->save();
            session()->flash('message', 'Votre commande a bien été annulée.');
            return redirect()->route('client.dashboard');
        }
    }

    public function render()
    {
        return view('livewire.client.show-order-page')
            ->layout('components.layouts.app');
    }
}