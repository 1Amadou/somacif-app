<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LivreurLoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        if (Auth::guard('livreur')->attempt($credentials)) {
            $request->session()->regenerate();
            // CORRECTION DÉFINITIVE : On redirige explicitement vers le tableau de bord livreur.
            return redirect()->route('livreur.dashboard');
        }
        return back()->withErrors(['phone' => 'Les informations d\'identification ne correspondent pas.']);
    }

    public function logout(Request $request)
    {
        Auth::guard('livreur')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // CORRECTION DÉFINITIVE : On redirige vers la passerelle de connexion unifiée.
        return redirect()->route('login');
    }
}