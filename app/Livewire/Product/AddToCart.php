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
    public ?UniteDeVente $selectedVariant;
    public $selectedVariantId;
    public float $currentPrice = 0.0;
    public int $quantity = 1;

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->client = Auth::guard('client')->user();
        
        // On sélectionne la première variante par défaut
        $this->selectedVariant = $this->product->uniteDeVentes->first();
        if ($this->selectedVariant) {
            $this->selectedVariantId = $this->selectedVariant->id;
            $this->updatePrice();
        }
    }

    // Se déclenche quand l'utilisateur change de calibre
    public function updatedSelectedVariantId($variantId)
    {
        $this->selectedVariant = $this->product->uniteDeVentes->find($variantId);
        $this->updatePrice();
    }

    public function updatePrice()
    {
        if ($this->selectedVariant && $this->client) {
            $this->currentPrice = match ($this->client->type) {
                'Grossiste' => $this->selectedVariant->prix_grossiste,
                'Hôtel/Restaurant' => $this->selectedVariant->prix_hotel_restaurant,
                default => $this->selectedVariant->prix_particulier,
            };
        }
    }

    public function addToCart()
    {
        if (!$this->client) {
            return redirect()->route('login');
        }

        if (!$this->selectedVariant) {
            session()->flash('error', 'Veuillez sélectionner une option de produit.');
            return;
        }

        // --- VALIDATION DU STOCK ---
        if ($this->selectedVariant->stock_entrepôt_principal < $this->quantity) {
            session()->flash('error', "Stock insuffisant. Il ne reste que {$this->selectedVariant->stock_entrepôt_principal} carton(s) disponible(s).");
            return;
        }

        Cart::instance('default')->add(
            $this->selectedVariant->id,
            $this->selectedVariant->nom_complet,
            $this->quantity,
            $this->currentPrice,
            ['image' => $this->product->image_principale]
        );

        $this->dispatch('cart_updated');
        session()->flash('success', '"' . $this->selectedVariant->nom_complet . '" a été ajouté au panier.');
    }

    public function render()
    {
        return view('livewire.product.add-to-cart');
    }
}