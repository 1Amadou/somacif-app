<?php

namespace App\Livewire\Client;

use App\Enums\OrderStatusEnum; // <-- AJOUT : On importe notre dictionnaire
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditOrderPage extends Component
{
    public Order $order;
    public array $items = [];
    public float $newTotal = 0.0;

    public function mount(Order $order)
    {
        // CORRECTION : On utilise notre Enum pour une vérification robuste
        if ($order->client_id !== Auth::guard('client')->id() || $order->statut !== OrderStatusEnum::EN_ATTENTE) {
            session()->flash('error', 'Cette commande ne peut plus être modifiée.');
            return redirect()->route('client.dashboard');
        }

        $this->order = $order->load('items.uniteDeVente.product');
        
        foreach ($this->order->items as $item) {
            $this->items[$item->id] = [
                'id' => $item->id,
                'name' => $item->uniteDeVente->product->nom . ' (' . $item->uniteDeVente->nom_unite . ')',
                'quantity' => $item->quantite,
                'price' => $item->prix_unitaire,
                'subtotal' => $item->quantite * $item->prix_unitaire
            ];
        }
        $this->calculateTotal();
    }

    // Se déclenche quand une quantité est modifiée
    public function updatedItems($value, $key)
    {
        $itemId = explode('.', $key)[0];
        $quantity = (int) $value;

        if ($quantity < 1) {
            $quantity = 1;
            $this->items[$itemId]['quantity'] = 1;
        }

        $this->items[$itemId]['subtotal'] = $quantity * $this->items[$itemId]['price'];
        $this->calculateTotal();
    }

    public function removeItem($itemId)
    {
        unset($this->items[$itemId]);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->newTotal = array_sum(array_column($this->items, 'subtotal'));
    }

    public function saveChanges()
    {
        foreach ($this->items as $itemId => $itemData) {
            OrderItem::find($itemId)->update(['quantite' => $itemData['quantity']]);
        }
        
        // Supprime les articles qui ont été retirés
        $currentItemIds = array_keys($this->items);
        $this->order->items()->whereNotIn('id', $currentItemIds)->delete();

        // Met à jour le total de la commande
        $this->order->update(['montant_total' => $this->newTotal]);
        
        // Notifier l'admin serait une bonne idée ici

        session()->flash('success', 'Votre commande a été mise à jour avec succès.');
        return redirect()->route('client.orders.show', $this->order);
    }

    public function render()
    {
        return view('livewire.client.edit-order-page')
            ->layout('components.layouts.app');
    }
}