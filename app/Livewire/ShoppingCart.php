<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;

class ShoppingCart extends Component
{
    public ?Client $client = null;
    public array $cartItems = [];

    protected $listeners = ['productAdded' => 'handleProductAdded', 'orderPlaced' => 'emptyCart'];

    public function emptyCart()
    {
        $this->cartItems = [];
        session()->forget('cart');
    }

    public function mount()
    {
        if (session()->has('authenticated_client_id')) {
            $this->client = Client::find(session('authenticated_client_id'));
            $this->cartItems = session('cart', []);
        }
    }

    public function handleProductAdded($productId, $quantity)
    {
        $product = \App\Models\Product::find($productId);
        if (!$product) return;

        if (isset($this->cartItems[$productId])) {
            $this->cartItems[$productId]['quantity'] += $quantity;
        } else {
            $this->cartItems[$productId] = [
                'product_id' => $product->id,
                'name' => $product->nom,
                'quantity' => $quantity,
            ];
        }
        $this->updateSession();
    }

    public function updateQuantity($productId, $quantity)
    {
        if (isset($this->cartItems[$productId])) {
            $this->cartItems[$productId]['quantity'] = max(1, (int)$quantity);
            $this->updateSession();
        }
    }

    public function removeItem($productId)
    {
        unset($this->cartItems[$productId]);
        $this->updateSession();
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