<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\UniteDeVente;
use Livewire\Component;

class ShoppingCart extends Component
{
    public ?Client $client = null;
    public array $cartItems = [];
    public bool $isCartOpen = false;
    protected $listeners = ['productAdded' => 'handleProductAdded', 'orderPlaced' => 'emptyCart'];

    public function mount()
    {
        if (session()->has('authenticated_client_id')) {
            $this->client = Client::find(session('authenticated_client_id'));
            
            // Récupère le panier de la session, ou un tableau vide s'il n'existe pas.
            $cartInSession = session('cart', []);

            $validCartItems = [];
            $cartNeedsReset = false;

            foreach ($cartInSession as $variantId => $item) {
                // Vérifie si toutes les clés nécessaires existent pour éviter les erreurs.
                if (isset($item['variant_id'], $item['name'], $item['calibre'], $item['quantity'])) {
                    // Vérifie également que la variante existe toujours en base de données.
                    if (UniteDeVente::find($variantId)) {
                        $validCartItems[$variantId] = $item;
                    } else {
                        // Si une variante n'existe plus, le panier est invalide.
                        $cartNeedsReset = true;
                        break;
                    }
                } else {
                    // Si une clé est manquante, le panier est invalide.
                    $cartNeedsReset = true;
                    break;
                }
            }

            if ($cartNeedsReset) {
                // Vide le panier si des articles sont mal formés ou n'existent plus.
                $this->emptyCart();
            } else {
                // Charge les articles valides.
                $this->cartItems = $validCartItems;
            }
        }
    }

    public function handleProductAdded($variantId, $quantity)
    {
        // Vérifie si la variante existe avant de l'ajouter.
        $variant = UniteDeVente::with('product')->find($variantId);
        if (!$variant) {
            session()->flash('error', 'Le produit n\'existe pas.');
            return;
        }

        if (isset($this->cartItems[$variantId])) {
            $this->cartItems[$variantId]['quantity'] += (int) $quantity;
        } else {
            $this->cartItems[$variantId] = [
                'variant_id' => $variant->id,
                'name' => $variant->product->nom,
                'calibre' => $variant->calibre,
                'quantity' => (int) $quantity,
            ];
        }
        $this->updateSession();
        $this->isCartOpen = true;
        session()->flash('success', 'Produit ajouté au panier avec succès!');
    }

    public function updateQuantity($variantId, $quantity)
    {
        // S'assure que la quantité est un entier et est supérieure ou égale à 1.
        $safeQuantity = max(1, (int)$quantity);
        if (isset($this->cartItems[$variantId])) {
            $this->cartItems[$variantId]['quantity'] = $safeQuantity;
            $this->updateSession();
        }
    }

    public function removeItem($variantId)
    {
        unset($this->cartItems[$variantId]);
        $this->updateSession();
        // Si le panier est vide après suppression, on le ferme automatiquement.
        if (empty($this->cartItems)) {
            $this->isCartOpen = false;
        }
        session()->flash('success', 'Produit retiré du panier.');
    }

    public function emptyCart()
    {
        $this->cartItems = [];
        session()->forget('cart');
        $this->isCartOpen = false;
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