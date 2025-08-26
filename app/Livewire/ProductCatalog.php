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

        // Seuls les produits visibles sont pris en compte
        $products = Product::where('is_visible', true)->with('uniteDeVentes')->get();

        foreach ($products as $product) {
            // S'assure que le produit a au moins une unité de vente avant d'initialiser les valeurs par défaut.
            if ($product->uniteDeVentes->isNotEmpty()) {
                $firstVariantId = $product->uniteDeVentes->first()->id;
                // Si aucune variante n'est sélectionnée, on prend la première par défaut.
                // L'opérateur ?? est plus sûr que le simple 'first()'.
                $this->selectedVariants[$product->id] = $this->selectedVariants[$product->id] ?? $firstVariantId;
                // Initialise la quantité à 1 si elle n'existe pas déjà.
                $this->quantities[$product->id] = $this->quantities[$product->id] ?? 1;
            } else {
                // Si un produit n'a pas de variante, on n'initialise rien pour éviter les erreurs.
                $this->selectedVariants[$product->id] = null;
                $this->quantities[$product->id] = 0;
            }
        }
    }

    public function addToCart($productId)
    {
        // 1. Vérification de l'authentification
        if (!$this->client) {
            session()->flash('error', 'Vous devez être connecté pour ajouter des produits au panier.');
            return;
        }

        // 2. Récupération et validation de la variante et de la quantité
        $variantId = $this->selectedVariants[$productId] ?? null;
        $quantity = (int) ($this->quantities[$productId] ?? 1);

        if (!$variantId) {
            session()->flash('error', 'Veuillez sélectionner une variante de produit.');
            return;
        }

        if ($quantity <= 0) {
            session()->flash('error', 'La quantité doit être supérieure à zéro.');
            return;
        }

        // 3. Dispatch de l'événement avec des données validées
        $this->dispatch('productAdded', variantId: $variantId, quantity: $quantity);
        
        // 4. Réinitialisation de la quantité
        $this->quantities[$productId] = 1;
    }

    public function getPriceForClient($uniteDeVente)
    {
        // Validation que le client et l'unité de vente existent avant de calculer le prix.
        if (!$this->client || !$uniteDeVente) {
            return 0;
        }
        
        // Utilise un match pour une logique de prix plus claire.
        return match ($this->client->type) {
            'Grossiste' => $uniteDeVente->prix_grossiste,
            'Hôtel/Restaurant' => $uniteDeVente->prix_hotel_restaurant,
            'Particulier' => $uniteDeVente->prix_particulier,
            default => 0,
        };
    }

    public function render()
    {
        // Utilise une requête plus précise pour ne récupérer que les données nécessaires.
        $products = Product::where('is_visible', true)
            ->with(['uniteDeVentes', 'pointsDeVenteStock'])
            ->latest()
            ->paginate(12);

        return view('livewire.product-catalog', ['products' => $products]);
    }
}