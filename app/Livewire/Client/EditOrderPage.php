<?php

namespace App\Livewire\Client;

use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Component;

class EditOrderPage extends Component
{
    public Order $order;
    public array $items = [];

    public function mount(Order $order)
    {
        if ($order->client_id !== session('authenticated_client_id') || $order->statut !== 'Reçue') {
            session()->flash('error', 'Cette commande ne peut plus être modifiée.');
            return $this->redirect(route('client.dashboard'), navigate: true);
        }

        $this->order = $order;
        $this->loadItems();
    }

    protected function loadItems()
    {
        $this->items = [];
        foreach ($this->order->orderItems()->get() as $item) {
            $this->items[$item->id] = [
                'id' => $item->id,
                'nom_produit' => $item->nom_produit,
                'calibre' => $item->calibre,
                'quantite' => $item->quantite,
                'prix_unitaire' => $item->prix_unitaire,
            ];
        }
    }

    public function removeItem($itemId)
    {
        $item = OrderItem::find($itemId);
        if ($item && $item->order_id === $this->order->id) {
            $item->delete();
            $this->order->refresh();

            if ($this->order->orderItems->isEmpty()) {
                $this->order->delete();
                session()->flash('success', 'La commande a été annulée car elle est désormais vide.');
                return $this->redirect(route('client.dashboard'), navigate: true);
            }

            $this->recalculateTotalAndUpdate();
            $this->loadItems();
        }
    }

    public function saveOrder()
    {
        foreach ($this->items as $itemId => $itemData) {
            $item = OrderItem::find($itemId);
            if ($item) {
                $newQuantity = max(1, (int)$itemData['quantite']);
                $item->update(['quantite' => $newQuantity]);
            }
        }
        
        $this->recalculateTotalAndUpdate();
        
        session()->flash('success', 'Votre commande a été mise à jour avec succès.');
        return $this->redirect(route('client.dashboard'), navigate: true);
    }

    protected function recalculateTotalAndUpdate()
    {
        $newTotalAmount = 0;
        foreach ($this->order->orderItems()->get() as $item) {
            $newTotalAmount += $item->quantite * $item->prix_unitaire;
        }
        $this->order->update(['montant_total' => $newTotalAmount]);
    }

    public function render()
    {
        return view('livewire.client.edit-order-page')
            ->layout('components.layouts.app', ['metaTitle' => 'Modifier la commande ' . $this->order->numero_commande]);
    }
}