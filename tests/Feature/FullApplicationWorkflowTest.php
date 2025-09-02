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

/**
 * Ce test audite le workflow complet de l'application, de l'entrée en stock à la vente finale.
 * Il sert de garant de la fiabilité de la logique métier.
 */
class FullApplicationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Client $client;
    private PointDeVente $pointDeVente;
    private UniteDeVente $uniteDeVente;

    /**
     * Prépare l'environnement de base pour chaque test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // On crée les acteurs de notre scénario
        $this->admin = User::factory()->create();
        $fournisseur = Fournisseur::factory()->create();
        $this->client = Client::factory()->create(['type' => 'Grossiste']);
        $this->pointDeVente = PointDeVente::factory()->create([
            'client_id' => $this->client->id,
            'responsable_id' => $this->client->id,
        ]);

        // On crée un produit avec une unité de vente et des prix spécifiques
        $product = Product::factory()->create();
        $this->uniteDeVente = UniteDeVente::factory()->create([
            'product_id' => $product->id,
            'stock' => 0, // Le stock principal commence à zéro
            'prix_unitaire' => 10000, // Prix de base
            'prix_grossiste' => 9000,   // Prix pour notre client test
            'internal_price' => 7000,  // Coût d'achat pour le calcul de profit
        ]);
    }

    /**
     * Teste le "chemin heureux" : un workflow complet sans erreur.
     */
    public function test_full_workflow_happy_path_with_all_details(): void
    {
        // --- 1. APPROVISIONNEMENT (Entrée de Stock) ---
        // Un camion arrive avec 100 cartons de notre produit.
        Arrivage::factory()->create([
            'details_produits' => [
                ['unite_de_vente_id' => $this->uniteDeVente->id, 'quantite' => 100]
            ]
        ]);

        // VÉRIFICATION 1 : Le stock principal doit être à 100.
        // L'ArrivageObserver doit avoir fait son travail.
        $this->assertEquals(100, $this->uniteDeVente->fresh()->stock, "Échec : Le stock principal n'a pas été incrémenté après l'arrivage.");

        // --- 2. DISTRIBUTION (Transfert de Stock) ---
        // Le client passe une commande pour 20 cartons. Le prix doit correspondre à son statut "Grossiste".
        $order = Order::factory()
            ->for($this->client)
            ->for($this->pointDeVente)
            ->hasItems(1, [
                'unite_de_vente_id' => $this->uniteDeVente->id,
                'quantite' => 20,
                'prix_unitaire' => $this->uniteDeVente->prix_grossiste, // 9000 FCFA
            ])
            ->create([
                'statut' => 'en_attente',
                'montant_total' => 20 * 9000, // 180,000 FCFA
            ]);

        // VÉRIFICATION 2 : Tant que la commande est "en attente", aucun stock ne doit bouger.
        $this->assertEquals(100, $this->uniteDeVente->fresh()->stock, "Échec : Le stock principal a changé alors que la commande est en attente.");
        $this->assertDatabaseMissing('inventories', ['point_de_vente_id' => $this->pointDeVente->id]);

        // L'admin valide la commande. C'est ici que la logique de transfert s'active.
        $order->statut = 'validee';
        $order->save();

        // VÉRIFICATION 3 : Le stock principal doit baisser, et l'inventaire du client doit augmenter.
        // L'OrderObserver doit avoir fait la double opération.
        $this->assertEquals(80, $this->uniteDeVente->fresh()->stock, "Échec : Le stock principal n'a pas diminué après la validation.");
        
        $clientInventory = Inventory::where('point_de_vente_id', $this->pointDeVente->id)
                                    ->where('unite_de_vente_id', $this->uniteDeVente->id)
                                    ->first();
        $this->assertNotNull($clientInventory, "Échec : L'inventaire du client n'a pas été créé.");
        $this->assertEquals(20, $clientInventory->quantite_stock, "Échec : L'inventaire du client n'a pas la bonne quantité.");

        // --- 3. VENTE & RÈGLEMENT (Flux Financier & Sortie de Stock) ---
        // Le client vient faire un premier versement partiel. Il déclare avoir vendu 5 cartons
        // à un prix légèrement supérieur (9500 FCFA).
        $reglement1 = null;
        DB::transaction(function () use ($order, &$reglement1) {
            $reglement1 = Reglement::create([
                'client_id' => $this->client->id,
                'user_id' => $this->admin->id,
                'montant_verse' => 5 * 9500, // 47,500 FCFA
            ]);
            $reglement1->details()->create([
                'unite_de_vente_id' => $this->uniteDeVente->id,
                'quantite_vendue' => 5,
                'prix_de_vente_unitaire' => 9500,
            ]);
            $reglement1->orders()->attach($order->id);
        });

        // On déclenche manuellement la logique de post-traitement du règlement.
        (new ReglementObserver(new StockManager()))->process($reglement1);

        // VÉRIFICATION 4 (Détail subtil) : Le stock du client doit avoir diminué, et les finances de la commande doivent être à jour.
        $this->assertEquals(15, $clientInventory->fresh()->quantite_stock, "Échec : Le stock du client n'a pas diminué après le premier règlement.");
        $order->refresh();
        $this->assertEquals('Partiellement réglé', $order->statut_paiement, "Échec : Le statut de paiement devrait être 'Partiellement réglé'.");
        $this->assertEquals(47500, $order->montant_paye, "Échec : Le montant payé sur la commande est incorrect.");

        // Le client revient et solde le reste de la commande. Il déclare avoir vendu les 15 cartons restants au prix initial.
        $reglement2 = null;
        DB::transaction(function () use ($order, &$reglement2) {
            $reglement2 = Reglement::create([
                'client_id' => $this->client->id,
                'user_id' => $this->admin->id,
                'montant_verse' => 15 * 9000, // 135,000 FCFA
            ]);
            $reglement2->details()->create([
                'unite_de_vente_id' => $this->uniteDeVente->id,
                'quantite_vendue' => 15,
                'prix_de_vente_unitaire' => 9000,
            ]);
            $reglement2->orders()->attach($order->id);
        });
        
        (new ReglementObserver(new StockManager()))->process($reglement2);

        // VÉRIFICATION 5 : Le stock du client doit être à zéro, et la commande complètement réglée.
        $this->assertEquals(0, $clientInventory->fresh()->quantite_stock, "Échec : Le stock du client devrait être vide.");
        $order->refresh();
        $this->assertEquals('Complètement réglé', $order->statut_paiement, "Échec : La commande devrait être complètement réglée.");
        // Vérification du total payé (47,500 + 135,000 = 182,500)
        $this->assertEquals(182500, $order->montant_paye, "Échec : Le montant total payé est incorrect.");
    }

    /**
     * Teste le cas où le stock est insuffisant pour valider une commande.
     */
    public function test_validation_fails_when_stock_is_insufficient(): void
    {
        // On s'attend à ce que le code lance une Exception
        $this->expectException(\Exception::class);

        // Le stock principal est à 0. On essaie de commander 10 cartons.
        $order = Order::factory()
            ->for($this->client)->for($this->pointDeVente)
            ->hasItems(1, ['unite_de_vente_id' => $this->uniteDeVente->id, 'quantite' => 10])
            ->create(['statut' => 'en_attente']);

        // Cette action doit déclencher l'OrderObserver, qui doit détecter le stock
        // insuffisant (0 < 10) et lancer une exception, stoppant toute l'opération.
        $order->statut = 'validee';
        $order->save();
    }
}