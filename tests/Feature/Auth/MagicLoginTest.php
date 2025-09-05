<?php

namespace Tests\Feature\Auth;

use App\Models\Client;
use App\Livewire\Auth\MagicLogin;
use App\Notifications\MagicLoginCodeNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class MagicLoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function la_page_de_connexion_est_accessible_aux_invites()
    {
        $this->get(route('login'))
            ->assertSuccessful()
            ->assertSeeLivewire('auth.magic-login');
    }

    #[Test]
    public function un_utilisateur_authentifie_est_redirige_vers_son_tableau_de_bord()
    {
        $client = Client::factory()->create();
        $this->actingAs($client, 'client');

        $this->get(route('login'))
            ->assertRedirect(route('client.dashboard'));
    }

    #[Test]
    public function un_client_peut_demander_un_code_de_connexion()
    {
        Notification::fake();
        $client = Client::factory()->create();

        // Utilise le bon nom de variable : email_address
        Livewire::test(MagicLogin::class)
            ->set('email_address', $client->email)
            ->call('sendCode');

        Notification::assertSentTo($client, MagicLoginCodeNotification::class);
    }

    #[Test]
    public function un_client_peut_se_connecter_avec_un_code_valide()
    {
        $client = Client::factory()->create();
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Utilise les bons noms de colonnes
        $client->forceFill([
            'verification_code' => bcrypt($code),
            'verification_code_generated_at' => now(),
        ])->save();

        Livewire::test(MagicLogin::class)
            ->set('email_address', $client->email)
            ->set('isCodeSent', true)
            ->set('code', $code)
            ->call('login')
            ->assertRedirect(route('client.dashboard'));

        $this->assertAuthenticatedAs($client, 'client');
    }

    #[Test]
    public function la_connexion_echoue_avec_un_code_invalide()
    {
        $client = Client::factory()->create();

        Livewire::test(MagicLogin::class)
            ->set('email_address', $client->email)
            ->set('isCodeSent', true)
            ->set('code', '000000') // Code invalide
            ->call('login')
            ->assertHasErrors(['code']);

        $this->assertGuest('client');
    }
}