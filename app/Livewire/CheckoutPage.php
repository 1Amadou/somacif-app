<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Facades\Notification as Notifier;
use App\Notifications\NewOrderAdminNotification;

class CheckoutPage extends Component
{
    public Client $client;
    public array $cartItems = [];
    public array $deliveryAddresses = [];
    public string $selectedAddress = '';
    public string $notes = '';
    public ?Order $latestOrder = null;
    public float $totalAmount = 0;

    public function mount()
    {
        $this->client = Client::find(session('authenticated_client_id'));
        $this->cartItems = session('cart', []);

        if (empty($this->cartItems)) {
            return $this->redirect(route('products.index'), navigate: true);
        }

        $addresses = $this->client->entrepots_de_livraison;
        $this->deliveryAddresses = is_array($addresses) ? $addresses : [];

        if (!empty($this->deliveryAddresses)) {
            $this->selectedAddress = $this->deliveryAddresses[0];
        }

        $this->calculateTotal();
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

    public function calculateTotal()
    {
        $this->totalAmount = 0;
        foreach ($this->cartItems as $productId => $item) {
            $product = Product::with('uniteDeVentes')->find($productId);
            if ($product && $product->uniteDeVentes->first()) {
                $unitPrice = $this->getPriceForClient($product->uniteDeVentes->first());
                $this->totalAmount += $item['quantity'] * $unitPrice;
            }
        }
    }

    public function placeOrder()
    {
        if (empty($this->deliveryAddresses)) {
            $this->addError('selectedAddress', 'Aucun point de livraison n\'est configuré.');
            return;
        }

        $this->validate([
            'selectedAddress' => 'required',
            'notes' => 'nullable|string',
        ]);

        $totalCartons = array_sum(array_column($this->cartItems, 'quantity'));
        if ($this->client->type === 'Grossiste' && $totalCartons < 100) {
            $this->addError('cart', 'Les grossistes doivent commander un minimum de 100 cartons.');
            return;
        }

        // On recalcule le total juste avant de commander pour être sûr
        $this->calculateTotal();

        $order = Order::create([
            'client_id' => $this->client->id,
            'numero_commande' => 'CMD-' . time(),
            'statut' => 'Reçue',
            'delivery_address' => $this->selectedAddress,
            'notes' => $this->notes,
            'montant_total' => $this->totalAmount, // On utilise le montant calculé
        ]);

        foreach ($this->cartItems as $productId => $item) {
            $product = Product::with('uniteDeVentes')->find($productId);
            $unitPrice = $this->getPriceForClient($product->uniteDeVentes->first());
            $order->orderItems()->create([
                'product_id' => $productId,
                'nom_produit' => $item['name'],
                'unite' => 'Carton',
                'quantite' => $item['quantity'],
                'prix_unitaire' => $unitPrice,
            ]);
        }

        // On notifie l'admin
        $adminEmail = config('settings.admin_notification_email');
        if ($adminEmail) {
            Notifier::route('mail', $adminEmail)->notify(new NewOrderAdminNotification($order));
        }

        session()->forget('cart');
        $this->latestOrder = $order;
        $this->dispatch('orderPlaced');
    }

    public function render()
    {
        return view('livewire.checkout-page')
            ->layout('components.layouts.app', ['metaTitle' => 'Finaliser ma commande']);
    }
}