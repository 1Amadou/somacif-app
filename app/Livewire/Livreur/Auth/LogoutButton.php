<?php
namespace App\Livewire\Livreur\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LogoutButton extends Component
{
    public function logout()
    {
        Auth::guard('livreur')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return $this->redirect(route('livreur.login'));
    }

    public function render()
    {
        return view('livewire.livreur.auth.logout-button');
    }
}