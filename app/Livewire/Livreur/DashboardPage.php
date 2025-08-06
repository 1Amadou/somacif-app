<?php

namespace App\Livewire\Livreur;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardPage extends Component
{
    public $livreur;
    public $activeOrders;
    public $completedOrders;

    public function mount()
    {
        $this->livreur = Auth::guard('livreur')->user();
        
        // On charge les commandes qui ne sont ni "Livrée" ni "Annulée"
        $this->activeOrders = $this->livreur->orders()
                            ->with('client')
                            ->whereNotIn('statut', ['Livrée', 'Annulée'])
                            ->orderBy('created_at', 'desc')
                            ->get();

        // On charge les 10 dernières commandes terminées
        $this->completedOrders = $this->livreur->orders()
                            ->with('client')
                            ->whereIn('statut', ['Livrée', 'Annulée'])
                            ->orderBy('created_at', 'desc')
                            ->take(10)
                            ->get();
    }

    public function render()
    {
        return view('livewire.livreur.dashboard-page')
            ->layout('components.layouts.livreur');
    }
}