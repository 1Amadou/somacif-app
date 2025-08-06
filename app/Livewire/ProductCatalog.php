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
    public array $selectedVariants = [];

    public function mount()
    {
        if (session()->has('authenticated_client_id')) {
            $this->client = Client::find(session('authenticated_client_id'));
        }

        $products = Product::where('is_visible', true)->with('uniteDeVentes')->get();
        foreach ($products as $product) {
            // Par défaut, on sélectionne la première variante et la quantité est 1
            $this->selectedVariants[$product->id] = $product->uniteDeVentes->first()->id ?? null;
            $this->quantities[$product->id] = 1;
        }
    }

    public function addToCart($productId)
    {
        if (!$this->client || !isset($this->selectedVariants[$productId])) return;

        $variantId = $this->selectedVariants[$productId];
        $quantity = $this->quantities[$productId] ?? 1;

        $this->dispatch('productAdded', variantId: $variantId, quantity: $quantity);
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
            ->latest()->paginate(12);

        return view('livewire.product-catalog', ['products' => $products]);
    }
}