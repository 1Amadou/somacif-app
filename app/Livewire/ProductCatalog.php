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

    public function mount()
    {
        $this->client = Auth::guard('client')->user();
        $products = Product::where('is_visible', true)->with('uniteDeVentes')->get();
        foreach ($products as $product) {
            $firstVariant = $product->uniteDeVentes->first();
            if ($firstVariant) {
                $this->quantities[$firstVariant->id] = 1; // On lie la quantité à l'ID de l'unité
                $this->selectedVariants[$product->id] = $firstVariant->id;
            }
        }
    }

    // --- CORRECTION DE LA LOGIQUE D'AJOUT AU PANIER ---
    public function addToCart($uniteDeVenteId)
    {
        if (!$this->client) {
            return redirect()->route('login');
        }

        $unite = UniteDeVente::find($uniteDeVenteId);
        $quantity = $this->quantities[$uniteDeVenteId] ?? 1;

        if (!$unite || $quantity <= 0) {
            session()->flash('error', 'Produit invalide ou quantité incorrecte.');
            return;
        }

        // --- VALIDATION DU STOCK ---
        if ($unite->stock_entrepôt_principal < $quantity) {
            session()->flash('error', "Stock insuffisant pour \"{$unite->nom_complet}\". Il ne reste que {$unite->stock_entrepôt_principal} carton(s).");
            return;
        }

        Cart::instance('default')->add(
            $unite->id,
            $unite->nom_complet, // Utilisation du nom complet pour plus de clarté
            $quantity,
            $this->getPriceForClient($unite),
            ['image' => $unite->product->image_principale]
        );

        $this->dispatch('cart_updated');
        session()->flash('success', '"' . $unite->nom_complet . '" a été ajouté au panier.');
    }
    
    public function getPriceForClient(UniteDeVente $unite)
    {
        // Cette logique est déjà correcte
        return match ($this->client?->type) {
            'Grossiste' => $unite->prix_grossiste,
            'Hôtel/Restaurant' => $unite->prix_hotel_restaurant,
            'Particulier' => $unite->prix_particulier,
            default => $unite->prix_unitaire,
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