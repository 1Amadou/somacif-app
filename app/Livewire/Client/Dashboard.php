<?php

namespace App\Livewire\Client;

use App\Models\Client;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public Client $client;
    public string $search = '';
    public string $statusFilter = '';

    public function mount()
    {
        $this->client = Client::find(session('authenticated_client_id'));
    }

    public function confirmReception(Order $order)
    {
        if ($order->client_id !== $this->client->id) {
            abort(403, 'Action non autorisée.');
        }

        $order->update([
            'statut' => 'Livrée',
            'client_confirmed_at' => now(),
        ]);

        session()->flash('success', 'Réception de la commande ' . $order->numero_commande . ' confirmée !');
        
        // On ne redirige plus. Livewire va rafraîchir le composant tout seul.
    }

    public function render()
    {
        $ordersQuery = $this->client->orders()
            ->when($this->search, function ($query) {
                $query->where('numero_commande', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('statut', $this->statusFilter);
            })
            ->latest();

        $orders = $ordersQuery->paginate(10);
        $allOrders = $this->client->orders()->get();

        return view('livewire.client.dashboard', [
            'orders' => $orders,
            'allOrders' => $allOrders,
        ])
        ->layout('components.layouts.app', ['metaTitle' => 'Mon Compte - SOMACIF']);
    }
}