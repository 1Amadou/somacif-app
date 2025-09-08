<?php

namespace App\Livewire\Livreur;

use App\Enums\OrderStatusEnum;
use App\Models\Livreur;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DashboardPage extends Component
{
    use WithPagination;

    public Livreur $livreur;
    public $missionsActives;

    public function mount()
    {
        $this->livreur = Auth::guard('livreur')->user();
        if (!$this->livreur) {
            return redirect()->route('login');
        }

        // CORRECTION N°1 : Correction d'une faute de frappe (EN_COURS_LIVRAISON)
        $this->missionsActives = $this->livreur->orders()
            ->whereIn('statut', [OrderStatusEnum::EN_PREPARATION, OrderStatusEnum::EN_COURS_LIVRAISON])
            ->with(['client', 'pointDeVente'])
            ->orderByRaw("FIELD(statut, '" . OrderStatusEnum::EN_PREPARATION->value . "', '" . OrderStatusEnum::EN_COURS_LIVRAISON->value . "')")
            ->latest()
            ->get();
    }

    public function render()
    {
        $historiqueMissions = $this->livreur->orders()
            ->whereIn('statut', [OrderStatusEnum::LIVREE, OrderStatusEnum::ANNULEE])
            ->with(['client', 'pointDeVente'])
            ->latest()
            // CORRECTION N°2 : Syntaxe correcte pour la pagination nommée
            ->paginate(10, ['*'], 'historique');

        return view('livewire.livreur.dashboard-page', [
            'historiqueMissions' => $historiqueMissions,
        ])->layout('components.layouts.livreur');
    }
}