<?php

namespace App\Livewire\Product;

use App\Models\Client;
use App\Models\Product;
use App\Models\UniteDeVente;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AddToCart extends Component
{
    public Product $product;
    public ?Client $client;
    
    public $selectedVariantId;
    public int $quantity = 1;
    public float $currentPrice = 0;

    // S'exécute au chargement, initialise les valeurs par défaut
    public function mount(Product $product)
    {
        $this->product = $product;
        $this->client = Auth::guard('client')->user();
        
        // Sélectionne la première unité de vente par défaut
        $this->selectedVariantId = $this->product->uniteDeVentes->first()?->id;
        
        // Met à jour le prix initial
        $this->updatePrice();
    }

    // Se déclenche à chaque fois que l'utilisateur change le calibre (l'unité de vente)
    public function updatedSelectedVariantId()
    {
        $this->updatePrice();
    }

    // Met à jour le prix affiché en fonction du client et de la sélection
    public function updatePrice()
    {
        $selectedVariant = UniteDeVente::find($this->selectedVariantId);
        if ($selectedVariant) {
            $this->currentPrice = $this->getPriceForClient($selectedVariant);
        }
    }

    // Logique pour ajouter au panier
    public function addToCart()
    {
        if (!$this->client) {
            return redirect()->route('login');
        }

        $unite = UniteDeVente::find($this->selectedVariantId);

        if ($unite && $this->quantity > 0) {
            Cart::instance('default')->add(
                $unite->id,
                $this->product->nom . ' (' . $unite->nom_unite . ')',
                $this->quantity,
                $this->currentPrice,
                ['image' => $this->product->image_principale]
            );

            $this->dispatch('cart_updated');
            session()->flash('success', '"' . $this->product->nom . '" a été ajouté au panier.');
        }
    }
    
    // Logique pour déterminer le prix en fonction du type de client
    public function getPriceForClient(UniteDeVente $unite): float
    {
        return match ($this->client?->type) {
            'Grossiste' => $unite->prix_grossiste,
            'Hôtel/Restaurant' => $unite->prix_hotel_restaurant,
            'Particulier' => $unite->prix_particulier,
            default => $unite->prix_unitaire,
        };
    }

    public function render()
    {
        $selectedVariant = UniteDeVente::find($this->selectedVariantId);

        return view('livewire.product.add-to-cart', [
            'selectedVariant' => $selectedVariant
        ]);
    }
}