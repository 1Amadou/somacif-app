<?php
namespace App\Livewire\Product;

use App\Models\Product;
use Livewire\Component;

class AddToCart extends Component
{
    public Product $product;
    public int $quantity = 1;
    public string $message = '';

    public function addToCart()
    {
        $this->dispatch('productAdded', productId: $this->product->id, quantity: $this->quantity);
        $this->message = "{$this->quantity} carton(s) ajouté(s) !";
        $this->quantity = 1;
    }

    public function render()
    {
        return view('livewire.product.add-to-cart');
    }
}