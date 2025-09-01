<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Product;
use App\Models\UniteDeVente;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Gloudemans\Shoppingcart\Facades\Cart;

class ProductCatalog extends Component
{
    use WithPagination;

    public ?Client $client;
    public array $quantities = [];
    public array $selectedVariants = [];

    // S'exécute au chargement du composant
    public function mount()
    {
        $this->client = Auth::guard('client')->user();

        // Initialise la variante sélectionnée pour chaque produit avec sa première unité de vente
        $products = Product::where('is_visible', true)->with('uniteDeVentes')->get();
        foreach ($products as $product) {
            $this->quantities[$product->id] = 1;
            $this->selectedVariants[$product->id] = $product->uniteDeVentes->first()?->id;
        }
    }

    // Fonction pour ajouter au panier
    public function addToCart($productId)
    {
        if (!$this->client) {
            return redirect()->route('login');
        }

        $uniteDeVenteId = $this->selectedVariants[$productId] ?? null;
        $quantity = $this->quantities[$productId] ?? 1;
        $unite = UniteDeVente::find($uniteDeVenteId);

        if ($unite && $quantity > 0) {
            Cart::instance('default')->add(
                $unite->id,
                $unite->product->nom . ' (' . $unite->nom_unite . ')',
                $quantity,
                $this->getPriceForClient($unite),
                ['image' => $unite->product->image_principale]
            );

            $this->dispatch('cart_updated');
            session()->flash('success', '"' . $unite->product->nom . '" a été ajouté au panier.');
        } else {
            session()->flash('error', 'Impossible d\'ajouter ce produit au panier.');
        }
    }
    
    // Logique pour déterminer le prix en fonction du type de client
    public function getPriceForClient(UniteDeVente $unite)
    {
        return match ($this->client?->type) {
            'Grossiste' => $unite->prix_grossiste,
            'Hôtel/Restaurant' => $unite->prix_hotel_restaurant,
            'Particulier' => $unite->prix_particulier,
            default => $unite->prix_unitaire, // Prix par défaut
        };
    }

    public function render()
    {
        $products = Product::where('is_visible', true)
            ->with(['uniteDeVentes'])
            ->latest()
            ->paginate(12);

        return view('livewire.product-catalog', [
            'products' => $products,
        ]);
    }
}