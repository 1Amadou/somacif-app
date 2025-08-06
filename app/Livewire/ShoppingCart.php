<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\UniteDeVente;
use Livewire\Component;

class ShoppingCart extends Component
{
    public ?Client $client = null;
    public array $cartItems = [];
    protected $listeners = ['productAdded' => 'handleProductAdded', 'orderPlaced' => 'emptyCart'];

    public function mount()
    {
        if (session()->has('authenticated_client_id')) {
            $this->client = Client::find(session('authenticated_client_id'));
            $cartInSession = session('cart', []);

            $validCartItems = [];
            $cartNeedsReset = false;

            foreach ($cartInSession as $variantId => $item) {
                // Vérifie si toutes les clés nécessaires existent
                if (isset($item['variant_id'], $item['name'], $item['calibre'], $item['quantity'])) {
                    $validCartItems[$variantId] = $item;
                } else {
                    // Si une clé est manquante, le panier est invalide et doit être réinitialisé
                    $cartNeedsReset = true;
                    break; 
                }
            }

            if ($cartNeedsReset) {
                $this->emptyCart(); // Vide le panier si des articles sont mal formés
            } else {
                $this->cartItems = $validCartItems; // Charge les articles valides
            }
        }
    }

    public function handleProductAdded($variantId, $quantity)
    {
        $variant = UniteDeVente::with('product')->find($variantId);
        if (!$variant) {
            return;
        }

        if (isset($this->cartItems[$variantId])) {
            $this->cartItems[$variantId]['quantity'] += $quantity;
        } else {
            $this->cartItems[$variantId] = [
                'variant_id' => $variant->id,
                'name' => $variant->product->nom,
                'calibre' => $variant->calibre,
                'quantity' => $quantity,
            ];
        }
        $this->updateSession();
    }
    
    public function updateQuantity($variantId, $quantity)
    {
        if (isset($this->cartItems[$variantId])) {
            $this->cartItems[$variantId]['quantity'] = max(1, (int)$quantity);
            $this->updateSession();
        }
    }

    public function removeItem($variantId)
    {
        unset($this->cartItems[$variantId]);
        $this->updateSession();
    }

    public function emptyCart()
    {
        $this->cartItems = [];
        session()->forget('cart');
    }

    private function updateSession()
    {
        session(['cart' => $this->cartItems]);
    }

    public function render()
    {
        return view('livewire.shopping-cart');
    }
}