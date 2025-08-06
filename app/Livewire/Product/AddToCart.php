<?php

namespace App\Livewire\Product;

use App\Models\Client;
use App\Models\Product;
use Livewire\Component;

class AddToCart extends Component
{
    public Product $product;
    public int $quantity = 1;
    public ?int $selectedVariantId = null;
    public string $message = '';
    public ?Client $client = null;

    public function mount()
    {
        if (session()->has('authenticated_client_id')) {
            $this->client = Client::find(session('authenticated_client_id'));
        }
        
        // On présélectionne la première variante disponible
        if ($this->product->uniteDeVentes->isNotEmpty()) {
            $this->selectedVariantId = $this->product->uniteDeVentes->first()->id;
        }
    }

    public function addToCart()
    {
        if (!$this->selectedVariantId) {
            $this->message = "Veuillez sélectionner un calibre.";
            return;
        }

        $this->dispatch('productAdded', variantId: $this->selectedVariantId, quantity: $this->quantity);
        $this->message = "Produit ajouté au panier !";
        $this->quantity = 1;
    }

    public function getPriceForClient($uniteDeVente)
    {
        if (!$this->client || !$uniteDeVente) return 0;
        return match ($this->client->type) {
            'Grossiste' => $uniteDeVente->prix_grossiste,
            'Hôtel/Restaurant' => $uniteDeVente->prix_hotel_restaurant,
            'Particulier' => $uniteDeVente->prix_particulier,
            default => 0,
        };
    }

    public function render()
    {
        $selectedVariant = $this->product->uniteDeVentes->firstWhere('id', $this->selectedVariantId);
        $currentPrice = $this->getPriceForClient($selectedVariant);

        return view('livewire.product.add-to-cart', [
            'selectedVariant' => $selectedVariant,
            'currentPrice' => $currentPrice,
        ]);
    }
}