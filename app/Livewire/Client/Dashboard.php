<?php

namespace App\Livewire\Client;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
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
        $client = Auth::guard('client')->user();
        if (!$client) {
            return redirect()->route('login');
        }
        $this->client = $client;
        $this->client->load('pointsDeVente');
    }
    
    public function updated($property)
    {
        if (in_array($property, ['search', 'statusFilter'])) {
            $this->resetPage();
        }
    }
    
    public function confirmReception($orderId)
    {
        $order = $this->client->orders()->find($orderId);
        if ($order && $order->statut === 'en_cours_livraison') {
            $order->confirmReception();
            session()->flash('success', 'Commande N°' . $order->numero_commande . ' marquée comme livrée !');
        }
    }

    private function calculateRemainingBalance($order)
    {
        return $order->montant_total - ($order->montant_paye ?? 0);
    }

    public function render()
    {
        $allOrders = $this->client->orders()->get()->each(function ($order) {
            $order->remaining_balance = $this->calculateRemainingBalance($order);
        });

        $ordersQuery = $this->client->orders()
            ->with('livreur') // On pré-charge le livreur
            ->when($this->search, fn($q) => $q->where('numero_commande', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter, fn($q) => $q->where('statut', $this->statusFilter))
            ->latest();
            
        $paginatedOrders = $ordersQuery->paginate(10);
        $paginatedOrders->each(function ($order) {
            $order->remaining_balance = $this->calculateRemainingBalance($order);
        });

        return view('livewire.client.dashboard', [
            'allOrders' => $allOrders,
            'orders' => $paginatedOrders,
        ])->layout('components.layouts.app');
    }
}