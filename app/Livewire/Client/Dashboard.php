<?php

namespace App\Livewire\Client;

use App\Models\Client;
use Livewire\Component;

class Dashboard extends Component
{
    public Client $client;
    public $orders;

    public function mount()
    {
        $this->client = Client::find(session('authenticated_client_id'));
        $this->orders = $this->client->orders()->latest()->get();
    }

    public function render()
    {
        return view('livewire.client.dashboard')
            ->layout('components.layouts.app', ['metaTitle' => 'Mon Compte - SOMACIF']);
    }
}