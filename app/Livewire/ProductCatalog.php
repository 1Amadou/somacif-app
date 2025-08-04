<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    public ?Client $client = null;
    public array $quantities = [];

    public function mount()
    {
        if (session()->has('authenticated_client_id')) {
            $this->client = Client::find(session('authenticated_client_id'));
        }

        $products = Product::where('is_visible', true)->pluck('id');
        foreach ($products as $productId) {
            $this->quantities[$productId] = 1;
        }
    }

    public function addToCart($productId)
    {
        if (!$this->client) return; // Sécurité

        $quantity = $this->quantities[$productId] ?? 1;
        
        // On envoie un événement au panier flottant
        $this->dispatch('productAdded', productId: $productId, quantity: $quantity);
        
        $this->quantities[$productId] = 1;
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
        $products = Product::where('is_visible', true)
            ->with('uniteDeVentes', 'pointsDeVenteStock')
            ->latest()
            ->paginate(12);
            
        return view('livewire.product-catalog', [
            'products' => $products,
        ]);
    }
}