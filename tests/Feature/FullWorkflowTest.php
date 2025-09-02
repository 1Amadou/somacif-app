<?php

namespace Tests\Feature;

use App\Livewire\Auth\MagicLogin; // Ajout important
use App\Models\Arrivage;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PointDeVente;
use App\Models\Product;
use App\Models\Reglement;
use App\Models\UniteDeVente;
use App\Models\User;
use App\Notifications\MagicLoginCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire; // Ajout important
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FullWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Fournisseur $fournisseur;
    protected Client $client;
    protected PointDeVente $pointDeVente;
    protected Product $product;
    protected UniteDeVente $uniteDeVente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->fournisseur = Fournisseur::factory()->create();
        $this->client = Client::factory()->create(['type' => 'grossiste']);
        $this->pointDeVente = PointDeVente::factory()->create(['client_id' => $this->client->id]);
        $this->product = Product::factory()->create(['nom' => 'Tilapia']);
        $this->uniteDeVente = UniteDeVente::factory()->create([
            'product_id' => $this->product->id,
            'nom_unite' => 'Carton 10kg',
            'prix_grossiste' => 25000,
            'stock' => 0,
        ]);
    }

    #[Test]
    public function full_business_workflow_from_arrival_to_payment()
    {
        // Assertions initiales
        $this->assertEquals(0, $this->uniteDeVente->stock);
        $this->assertEquals(0, $this->pointDeVente->getInventoryStock($this->uniteDeVente->id));
        
        // PHASE 1 : APPROVISIONNEMENT
        $quantiteArrivage = 100;
        Arrivage::create(['fournisseur_id' => $this->fournisseur->id, 'numero_bon' => 'BON-2024-001', 'date_arrivage' => now(), 'details' => [['unite_de_vente_id' => $this->uniteDeVente->id, 'quantite' => $quantiteArrivage]], 'statut' => 'livre']);
        $this->assertEquals($quantiteArrivage, $this->uniteDeVente->fresh()->stock, "ERREUR PHASE 1");
        
        // PHASE 2 : COMMANDE ET DISTRIBUTION
        $quantiteCommandee = 15;
        $order = Order::factory()->create(['client_id' => $this->client->id, 'point_de_vente_id' => $this->pointDeVente->id, 'status' => 'en_attente', 'items' => [['unite_de_vente_id' => $this->uniteDeVente->id, 'quantite' => $quantiteCommandee, 'prix_unitaire' => $this->uniteDeVente->prix_grossiste]]]);
        $order->status = 'validee';
        $order->save();
        $this->assertEquals($quantiteArrivage - $quantiteCommandee, $this->uniteDeVente->fresh()->stock, "ERREUR PHASE 2B STOCK PRINCIPAL");
        $this->assertEquals($quantiteCommandee, $this->pointDeVente->getInventoryStock($this->uniteDeVente->id), "ERREUR PHASE 2B STOCK CLIENT");

        // PHASE 3 : VENTE ET RÈGLEMENT
        $quantiteVendue = 10;
        $montantPaye = $quantiteVendue * $this->uniteDeVente->prix_grossiste;
        $reglement = Reglement::create(['client_id' => $this->client->id, 'montant_verse' => $montantPaye, 'date_reglement' => now(), 'methode_paiement' => 'especes']);
        $reglement->details()->create(['unite_de_vente_id' => $this->uniteDeVente->id, 'quantite_vendue' => $quantiteVendue, 'prix_de_vente_unitaire' => $this->uniteDeVente->prix_grossiste]);
        $reglement->orders()->attach($order->id);
        (new \App\Observers\ReglementObserver())->process($reglement);
        $this->assertEquals($quantiteCommandee - $quantiteVendue, $this->pointDeVente->getInventoryStock($this->uniteDeVente->id), "ERREUR PHASE 3 STOCK CLIENT");
        $order->refresh();
        $this->assertEquals($montantPaye, $order->montant_paye, "ERREUR PHASE 3 MONTANT PAYE");
        $this->assertEquals('partiellement_paye', $order->statut_paiement, "ERREUR PHASE 3 STATUT PAIEMENT");
    }

    #[Test]
    public function it_prevents_stock_from_going_negative_on_order_validation()
    {
        $this->assertEquals(0, $this->uniteDeVente->stock);
        $order = Order::factory()->create(['client_id' => $this->client->id, 'point_de_vente_id' => $this->pointDeVente->id, 'status' => 'en_attente', 'items' => [['unite_de_vente_id' => $this->uniteDeVente->id, 'quantite' => 10]]]);
        $order->status = 'validee';
        $order->save();
        $this->assertEquals(0, $this->uniteDeVente->fresh()->stock, "ROBUSTESSE COMPROMISE STOCK PRINCIPAL");
        $this->assertEquals(0, $this->pointDeVente->getInventoryStock($this->uniteDeVente->id), "ROBUSTESSE COMPROMISE STOCK CLIENT");
    }
    
    #[Test]
    public function magic_link_authentication_and_notifications()
    {
        Notification::fake();

        // CORRECTION : On teste le composant Livewire directement
        Livewire::test(MagicLogin::class)
            ->set('identifier', $this->client->email)
            ->call('sendCode');

        Notification::assertSentTo($this->client, MagicLoginCodeNotification::class, "ERREUR AUTH");
    }
}