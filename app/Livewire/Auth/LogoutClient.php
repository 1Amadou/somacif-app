<?php
namespace App\Livewire\Auth;

use Livewire\Component;

class LogoutClient extends Component
{
    public function logout()
    {
        session()->forget('authenticated_client_id');
        return $this->redirect(route('home'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.logout-client');
    }
}