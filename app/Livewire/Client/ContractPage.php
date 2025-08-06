<?php

namespace App\Livewire\Client;

use App\Models\Client;
use App\Models\Page; // Assurez-vous que cette ligne est présente
use Livewire\Component;

class ContractPage extends Component
{
    public Client $client;
    public bool $terms_accepted = false;
    public ?string $cguContent = ''; // Propriété pour stocker le contenu des CGU

    public function mount()
    {
        $this->client = Client::find(session('authenticated_client_id'));
        
        // On charge le contenu de la page des Conditions Générales
        $cguPage = Page::where('slug', 'conditions-generales')->first();
        if ($cguPage) {
            $this->cguContent = $cguPage->contenus['main_content'] ?? '';
        }
    }

    public function acceptTerms()
    {
        $this->validate(['terms_accepted' => 'accepted']);

        $this->client->update([
            'terms_accepted_at' => now(),
        ]);
        
        $this->client->refresh(); 

        session()->flash('success', 'Merci ! Les conditions ont bien été acceptées.');
    }

    public function render()
    {
        return view('livewire.client.contract-page')
            ->layout('components.layouts.app', ['metaTitle' => 'Contrat & Conditions']);
    }
}