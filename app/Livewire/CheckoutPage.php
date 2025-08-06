<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Order;
use App\Models\UniteDeVente;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Notification as Notifier;
use App\Notifications\NewOrderAdminNotification;
use Livewire\Component;

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
        foreach ($this->cartItems as $variantId => $item) {
            $variant = UniteDeVente::find($variantId);
            if ($variant) {
                $unitPrice = $this->getPriceForClient($variant);
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
        $this->validate(['selectedAddress' => 'required', 'notes' => 'nullable|string']);
        $totalCartons = array_sum(array_column($this->cartItems, 'quantity'));
        if ($this->client->type === 'Grossiste' && $totalCartons < 100) {
            $this->addError('cart', 'Les grossistes doivent commander un minimum de 100 cartons.');
            return;
        }
        $this->calculateTotal();

        $order = Order::create([
            'client_id' => $this->client->id,
            'numero_commande' => 'CMD-' . time(),
            'statut' => 'Reçue',
            'delivery_address' => $this->selectedAddress,
            'notes' => $this->notes,
            'montant_total' => $this->totalAmount,
        ]);

        foreach ($this->cartItems as $variantId => $item) {
            $variant = UniteDeVente::find($variantId);
            if ($variant) {
                $unitPrice = $this->getPriceForClient($variant);
                $order->orderItems()->create([
                    'product_id' => $variant->product_id,
                    'unite_de_vente_id' => $variant->id,
                    'nom_produit' => $item['name'],
                    'unite' => $variant->nom_unite,
                    'calibre' => $item['calibre'],
                    'quantite' => $item['quantity'],
                    'prix_unitaire' => $unitPrice,
                ]);
            }
        }

        // On notifie l'admin en utilisant le système dynamique
        $adminEmail = config('settings.admin_notification_email');
        $template = NotificationTemplate::where('key', 'admin.new_order')->first();
        if ($adminEmail && $template && $template->is_active && config('settings.mail_notifications_active')) {
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