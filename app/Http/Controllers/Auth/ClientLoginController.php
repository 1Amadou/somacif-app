<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.client-login');
    }

    public function sendVerificationCode(Request $request)
    {
        $request->validate(['identifiant_unique' => 'required|string']);
        $client = Client::where('identifiant_unique_somacif', $request->identifiant_unique)->first();

        if ($client) {
            $code = random_int(100000, 999999);
            session(['verification_code' => $code, 'client_id_to_verify' => $client->id]);
            session()->flash('test_code', $code); // Pour les tests en local
            // Ici sera la logique d'envoi SMS
            return redirect()->route('client.login.verify.form');
        }
        return back()->withErrors(['identifiant_unique' => 'Cet identifiant est inconnu.']);
    }

    public function showVerificationForm()
    {
        if (!session()->has('client_id_to_verify')) {
            return redirect()->route('login');
        }
        return view('auth.client-verify');
    }

    public function verify(Request $request)
    {
        $request->validate(['verification_code' => 'required|numeric|digits:6']);
        if ($request->verification_code == session('verification_code')) {
            $clientId = session('client_id_to_verify');
            $client = Client::find($clientId);
            session(['authenticated_client_id' => $clientId]);
            session()->forget(['verification_code', 'client_id_to_verify']);

            $client->loginLogs()->create([
                'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'login_at' => now(),
            ]);
            return redirect()->intended(route('client.dashboard'));
        }
        return back()->withErrors(['verification_code' => 'Le code est incorrect.']);
    }

    public function logout(Request $request)
    {
        session()->forget('authenticated_client_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}