<?php

namespace App\Livewire\Client;

use App\Enums\OrderStatusEnum;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

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
            abort(401);
        }
        $this->client = $client;
        // Le load('pointsDeVente') est utile si vous l'affichez, sinon il peut être retiré.
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
        if ($order && $order->statut === OrderStatusEnum::EN_COURS_LIVRAISON) {
            // Cette logique est correcte et conforme à votre workflow
            $order->statut = OrderStatusEnum::LIVREE;
            // On pourrait ajouter une date de confirmation client si nécessaire
            // $order->client_confirmed_at = now();
            $order->save();
            session()->flash('success', 'Commande N°' . $order->numero_commande . ' marquée comme livrée !');
        }
    }

    public function render()
    {
        $allOrders = $this->client->orders()->get();

        $ordersQuery = $this->client->orders()
            ->with('livreur')
            ->when($this->search, fn($q) => $q->where('numero_commande', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter, fn($q) => $q->where('statut', $this->statusFilter))
            ->latest();

        // --- NOUVELLE LOGIQUE : RÉCUPÉRATION DU STOCK DÉTAILLÉ ---
        $pointDeVenteLieuIds = $this->client->pointsDeVente->pluck('lieuDeStockage.id')->filter();

        $stockDetails = Inventory::whereIn('lieu_de_stockage_id', $pointDeVenteLieuIds)
            ->with('uniteDeVente.product') // On charge les noms pour l'affichage
            ->select('unite_de_vente_id', DB::raw('SUM(quantite_stock) as total_stock'))
            ->groupBy('unite_de_vente_id')
            ->having('total_stock', '>', 0) // On affiche que les produits en stock
            ->get();
            
        return view('livewire.client.dashboard', [
            'allOrders' => $allOrders,
            'orders' => $ordersQuery->paginate(10),
            'statuses' => OrderStatusEnum::cases(),
            'stockDetails' => $stockDetails, // <-- On envoie les données à la vue
        ])->layout('components.layouts.app');
    }
}