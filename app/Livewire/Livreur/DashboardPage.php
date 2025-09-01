<?php

namespace App\Livewire\Livreur;

use App\Models\Livreur;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardPage extends Component
{
    public Livreur $livreur;
    public $missions;

    public function mount()
    {
        $this->livreur = Auth::guard('livreur')->user();
        if (!$this->livreur) {
            return redirect()->route('login');
        }

        // On récupère les missions : commandes assignées qui ne sont pas encore livrées ou annulées.
        $this->missions = $this->livreur->orders()
            ->whereNotIn('statut', ['livree', 'annulee'])
            ->with(['client', 'pointDeVente']) // On pré-charge les infos utiles
            ->orderByRaw("FIELD(statut, 'en_preparation', 'en_cours_livraison')") // Priorise les nouvelles missions
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.livreur.dashboard-page')
            ->layout('components.layouts.livreur'); // Un layout dédié pour les livreurs
    }
}