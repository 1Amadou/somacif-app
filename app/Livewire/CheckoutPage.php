<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Setting;
use App\Models\UniteDeVente;
use App\Models\User;
use App\Notifications\NewOrderAdminNotification;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CheckoutPage extends Component
{
    public $point_de_vente_id;
    public $notes = '';

    public function placeOrder()
    {
        $this->validate([
            'point_de_vente_id' => 'required|exists:point_de_ventes,id',
        ]);

        $client = Auth::guard('client')->user();
        $cartItems = Cart::instance('default')->content();

        if ($cartItems->isEmpty()) {
            return redirect()->route('products.index');
        }

        // Validation finale du stock (déjà en place et correcte)
        foreach ($cartItems as $item) {
            $unite = UniteDeVente::find($item->id);
            if (!$unite || $unite->stock_entrepôt_principal < $item->qty) {
                session()->flash('checkout_error', "Le stock pour \"{$item->name}\" est devenu insuffisant. Veuillez ajuster votre panier.");
                return redirect()->route('client.checkout');
            }
        }

        // --- CORRECTION : Utilisation de subtotal pour le montant total de la commande ---
        $montantTotal = Cart::instance('default')->subtotal(2, '.', '');

        $order = Order::create([
            'client_id' => $client->id,
            'point_de_vente_id' => $this->point_de_vente_id,
            'numero_commande' => 'CMD-' . strtoupper(uniqid()),
            'statut' => 'en_attente',
            'montant_total' => $montantTotal, // On utilise le montant fiable
            'notes' => $this->notes,
            'statut_paiement' => 'non_payee',
        ]);

        foreach ($cartItems as $item) {
            $order->items()->create([
                'unite_de_vente_id' => $item->id,
                'quantite' => $item->qty,
                'prix_unitaire' => $item->price,
            ]);
        }

        Cart::instance('default')->destroy();

        $adminEmail = Setting::where('key', 'admin_notification_email')->value('value');
        if ($adminEmail) {
            (new User(['email' => $adminEmail]))->notify(new NewOrderAdminNotification($order));
        }

        session()->flash('success', 'Votre commande a bien été passée ! Elle est en attente de validation.');
        return redirect()->route('client.dashboard');
    }

    public function render()
    {
        $client = Auth::guard('client')->user();
        $pointsDeVente = $client->pointsDeVente()->pluck('nom', 'id');
        $cartItems = Cart::instance('default')->content();
        // --- CORRECTION : Utilisation de subtotal pour l'affichage ---
        $cartTotal = Cart::instance('default')->subtotal(0, ',', ' ');

        return view('livewire.checkout-page', compact('pointsDeVente', 'cartItems', 'cartTotal'))
            ->layout('components.layouts.app');
    }
}