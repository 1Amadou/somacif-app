<?php

namespace Tests\Feature;

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
use App\Observers\ReglementObserver;
use App\Services\StockManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FullWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Fournisseur $fournisseur;
    private Client $client;
    private PointDeVente $pointDeVente;
    private UniteDeVente $uniteDeVente;

    /**
     * Préparation de l'environnement de test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['name' => 'Admin Test']);
        $this->fournisseur = Fournisseur::factory()->create(['nom_entreprise' => 'Fournisseur Principal']);
        $this->client = Client::factory()->create(['nom' => 'Client Test "Hôtel Azalaï"']);
        $this->pointDeVente = PointDeVente::factory()->create([
            'nom' => 'PDV Principal Azalaï',
            'responsable_id' => $this->client->id,
        ]);
        $produit = Product::factory()
            ->has(UniteDeVente::factory()->state([
                'nom_unite' => 'Carton Tilapia 10kg',
                'stock' => 0,
                'prix_unitaire' => 50000,
            ]), 'uniteDeVentes')
            ->create(['nom' => 'Tilapia']);
        $this->uniteDeVente = $produit->uniteDeVentes->first();
    }

    /**
     * TEST PRINCIPAL : Simule le cycle de vie complet de l'approvisionnement au paiement.
     */
    public function test_the_entire_business_workflow_from_stock_entry_to_final_sale(): void
    {
        // --- MODULE 2 : APPROVISIONNEMENT ---
        Arrivage::factory()->create([
            'fournisseur_id' => $this->fournisseur->id,
            'user_id' => $this->adminUser->id,
            'details_produits' => [['unite_de_vente_id' => $this->uniteDeVente->id, 'quantite' => 100]]
        ]);
        $this->assertEquals(100, $this->uniteDeVente->fresh()->stock);

        // --- MODULE 3 : DISTRIBUTION ---
        $order = Order::factory()
            ->for($this->client)->for($this->pointDeVente)
            ->hasItems(1, ['unite_de_vente_id' => $this->uniteDeVente->id, 'quantite' => 20, 'prix_unitaire' => $this->uniteDeVente->prix_unitaire])
            ->create(['statut' => 'en_attente', 'montant_total' => 20 * $this->uniteDeVente->prix_unitaire, 'statut_paiement' => 'non_payee']);
        
        $order->statut = 'validee';
        $order->save();
        
        $this->assertEquals(80, $this->uniteDeVente->fresh()->stock);
        $clientInventory = Inventory::where('point_de_vente_id', $this->pointDeVente->id)->where('unite_de_vente_id', $this->uniteDeVente->id)->first();
        $this->assertNotNull($clientInventory);
        $this->assertEquals(20, $clientInventory->quantite_stock);

        // --- MODULE 4 : VENTE & FLUX FINANCIER (CORRIGÉ) ---
        $reglement = null;
        DB::transaction(function () use ($order, &$reglement) {
            $reglement = Reglement::create([
                'client_id' => $this->client->id, 'user_id' => $this->adminUser->id,
                'montant_verse' => 250000, 'montant_calcule' => 250000,
                'date_reglement' => now(), 'methode_paiement' => 'especes',
            ]);
            $reglement->details()->create([
                'unite_de_vente_id' => $this->uniteDeVente->id, 'quantite_vendue' => 5,
                'prix_de_vente_unitaire' => $this->uniteDeVente->prix_unitaire,
            ]);
            $reglement->orders()->attach($order->id);
        });

        // CORRECTION : On appelle notre logique manuellement APRES la transaction.
        (new ReglementObserver(new StockManager()))->process($reglement);

        // VÉRIFICATION 4 : Le stock du client doit diminuer, et le statut de la commande doit changer.
        $this->assertEquals(15, $clientInventory->fresh()->quantite_stock, "L'inventaire du client devrait être à 15 après le règlement.");
        
        $order->refresh();
        $this->assertEquals('Partiellement réglé', $order->statut_paiement, "Le statut de paiement devrait être 'Partiellement réglé'.");
        $this->assertEquals(250000, $order->montant_paye, "Le montant payé sur la commande devrait être mis à jour.");
    }
    
    /**
     * TEST DE SÉCURITÉ : Vérifie que la validation d'une commande échoue si le stock principal est insuffisant.
     */
    public function test_order_validation_fails_with_insufficient_main_stock(): void
    {
        $this->expectException(\Exception::class);
        $order = Order::factory()
            ->for($this->client)->for($this->pointDeVente)
            ->hasItems(1, ['unite_de_vente_id' => $this->uniteDeVente->id, 'quantite' => 10])
            ->create(['statut' => 'en_attente']);
        $order->statut = 'validee';
        $order->save();
    }
}