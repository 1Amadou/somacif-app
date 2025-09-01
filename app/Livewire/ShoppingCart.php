<?php

namespace App\Livewire;

use App\Models\Client;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShoppingCart extends Component
{
    protected $listeners = ['cart_updated' => '$refresh'];

    public ?Client $client;
    public bool $isCartOpen = false; // <-- CORRECTION : La variable manquante est ici

    public function mount()
    {
        $this->client = Auth::guard('client')->user();
    }

    public function removeFromCart($rowId)
    {
        Cart::instance('default')->remove($rowId);
        $this->dispatch('cart_updated');
    }

    public function render()
    {
        $cartItems = Cart::instance('default')->content()->toArray();
        $cartCount = Cart::instance('default')->count();
        $cartTotal = Cart::instance('default')->total(0, ',', ' ');

        return view('livewire.shopping-cart', [
            'cartItems' => $cartItems,
            'cartCount' => $cartCount,
            'cartTotal' => $cartTotal,
        ]);
    }
}