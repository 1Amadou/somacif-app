<?php

namespace App\Livewire\Client;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UniteDeVente; // <-- Ajout de l'import
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Validation\Rule; // <-- Ajout de l'import

class EditOrderPage extends Component
{
    public Order $order;
    public array $items = [];
    public float $newTotal = 0.0;

    public function mount(Order $order)
    {
        if ($order->client_id !== Auth::guard('client')->id() || $order->statut !== OrderStatusEnum::EN_ATTENTE) {
            session()->flash('error', 'Cette commande ne peut plus être modifiée.');
            return redirect()->route('client.dashboard');
        }

        $this->order = $order->load('items.uniteDeVente.product');
        
        foreach ($this->order->items as $item) {
            $this->items[$item->id] = [
                'id' => $item->id,
                'unite_de_vente_id' => $item->unite_de_vente_id, // <-- On garde l'ID pour la validation
                'name' => $item->uniteDeVente->nom_complet,
                'quantity' => $item->quantite,
                'price' => $item->prix_unitaire,
                'subtotal' => $item->quantite * $item->prix_unitaire,
                'stock_dispo' => $item->uniteDeVente->stock_entrepôt_principal // <-- On stocke l'info
            ];
        }
        $this->calculateTotal();
    }

    public function updatedItems($value, $key)
    {
        $this->calculateTotal();
    }

    public function removeItem($itemId)
    {
        unset($this->items[$itemId]);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        foreach($this->items as $id => $item) {
            $this->items[$id]['subtotal'] = $item['quantity'] * $item['price'];
        }
        $this->newTotal = array_sum(array_column($this->items, 'subtotal'));
    }

    public function saveChanges()
    {
        // --- AMÉLIORATION : Validation du stock avant de sauvegarder ---
        $rules = [];
        foreach ($this->items as $itemId => $itemData) {
            $unite = UniteDeVente::find($itemData['unite_de_vente_id']);
            $stockDisponible = $unite ? $unite->stock_entrepôt_principal : 0;
            
            $rules["items.{$itemId}.quantity"] = "required|numeric|min:1|max:{$stockDisponible}";
        }
        
        $this->validate($rules, [
            'max' => 'Le stock pour :attribute est insuffisant (:max disponible).'
        ]);

        foreach ($this->items as $itemId => $itemData) {
            OrderItem::find($itemId)->update(['quantite' => $itemData['quantity']]);
        }
        
        $currentItemIds = array_keys($this->items);
        $this->order->items()->whereNotIn('id', $currentItemIds)->delete();

        $this->order->update(['montant_total' => $this->newTotal]);
        
        session()->flash('success', 'Votre commande a été mise à jour avec succès.');
        return redirect()->route('client.orders.show', $this->order);
    }

    public function render()
    {
        return view('livewire.client.edit-order-page')
            ->layout('components.layouts.app');
    }
}