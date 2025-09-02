<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Livreur;
use App\Models\Order;
use App\Models\PointDeVente;
use App\Models\Product;
use App\Models\Reglement;
use App\Models\UniteDeVente;
use App\Services\StockManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class SomacifFullWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    protected $admin;

    /**
     * @var Fournisseur
     */
    protected $fournisseur;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var PointDeVente
     */
    protected $pointDeVente;

    /**
     * @var Livreur
     */
    protected $livreur;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var UniteDeVente
     */
    protected $uniteDeVente;

    /**
     * Prépare l'environnement de base pour tous les tests de ce fichier.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // On exécute les seeders pour avoir les données de base (comme les utilisateurs)
        $this->seed();

        // Création des acteurs principaux
        $this->admin = User::factory()->create(['email' => 'admin@somacif.com']);
        $this->fournisseur = Fournisseur::factory()->create();
        $this->client = Client::factory()->create();
        $this->pointDeVente = PointDeVente::factory()->create(['client_id' => $this->client->id]);
        $this->livreur = Livreur::factory()->create();

        // Désactivation des notifications pour ne pas ralentir les tests
        Notification::fake();
    }

    /**
     * @test
     * Teste le flux complet de l'application, de la création du produit au règlement final.
     */
    public function test_full_application_workflow()
    {
        // =========================================================================
        // === ETAPE 1: GESTION DU CATALOGUE (ADMIN) ===============================
        // =========================================================================
        $this->step_1_catalog_setup();

        // =========================================================================
        // === ETAPE 2: APPROVISIONNEMENT (ADMIN) ==================================
        // =========================================================================
        $this->step_2_procurement_and_stock_increase();

        // =========================================================================
        // === ETAPE 3: COMMANDE DU CLIENT (FRONTEND) ==============================
        // =========================================================================
        $order = $this->step_3_client_order_process();

        // =========================================================================
        // === ETAPE 4: VALIDATION ET DISTRIBUTION (ADMIN) =========================
        // =========================================================================
        $this->step_4_order_validation_and_stock_transfer($order);

        // =========================================================================
        // === ETAPE 5: LIVRAISON (ADMIN & LIVREUR) ================================
        // =========================================================================
        $this->step_5_delivery_process($order);

        // =========================================================================
        // === ETAPE 6: RÈGLEMENT ET SORTIE DE STOCK FINALE (ADMIN) ================
        // =========================================================================
        $this->step_6_payment_and_final_stock_decrease($order);
    }

    private function step_1_catalog_setup()
    {
        // L'admin se connecte et crée un produit de base
        $this->product = Product::factory()->create([
            'name' => 'Tilapia',
            'category_id' => \App\Models\Category::factory()->create()->id,
        ]);

        // L'admin crée la déclinaison vendable (Unité de Vente) avec ses prix
        // Le stock initial est à 0.
        $this->uniteDeVente = UniteDeVente::factory()->create([
            'product_id' => $this->product->id,
            'name' => 'Carton 10kg',
            'prix_unitaire' => 15000,
            'prix_grossiste' => 14000,
            'prix_hotel_restaurant' => 14500,
            'stock' => 0,
        ]);

        // VÉRIFICATION: On s'assure que le produit et son unité de vente existent bien en BDD.
        $this->assertDatabaseHas('products', ['name' => 'Tilapia']);
        $this->assertDatabaseHas('unite_de_ventes', ['name' => 'Carton 10kg', 'stock' => 0]);
        
        fwrite(STDOUT, "✅ Étape 1/6: Catalogue configuré avec succès.\n");
    }

    private function step_2_procurement_and_stock_increase()
    {
        // L'admin crée un arrivage pour le fournisseur de 100 cartons de Tilapia
        $arrivage = \App\Models\Arrivage::factory()->create([
            'fournisseur_id' => $this->fournisseur->id,
            'numero_bon' => 'BON12345',
        ]);
        $arrivage->uniteDeVentes()->attach($this->uniteDeVente->id, ['quantity' => 100]);
        
        // La magie opère ici : l'ArrivageObserver doit avoir été déclenché pour augmenter le stock.

        // VÉRIFICATION: Le stock de l'entrepôt principal pour l'unité de vente doit être de 100.
        $this->uniteDeVente->refresh(); // On rafraîchit le modèle pour avoir la dernière valeur
        $this->assertEquals(100, $this->uniteDeVente->stock);

        fwrite(STDOUT, "✅ Étape 2/6: Approvisionnement réussi, stock principal mis à jour à 100.\n");
    }

    private function step_3_client_order_process()
    {
        // Le client se connecte
        $this->actingAs($this->client, 'client');

        // Le client ajoute 10 cartons au panier via le composant Livewire
        Livewire::test('product.add-to-cart', ['uniteDeVenteId' => $this->uniteDeVente->id])
            ->call('addToCart', 10);
        
        // Le client va sur la page de checkout, sélectionne son point de vente et valide
        Livewire::test('checkout-page')
            ->set('pointDeVenteId', $this->pointDeVente->id)
            ->call('placeOrder')
            ->assertRedirect(route('client.dashboard'));

        // VÉRIFICATION: Une commande doit avoir été créée avec le statut "en_attente".
        $this->assertDatabaseHas('orders', [
            'client_id' => $this->client->id,
            'point_de_vente_id' => $this->pointDeVente->id,
            'status' => 'en_attente',
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(10, $order->items->first()->quantity); // Vérifie la quantité commandée

        fwrite(STDOUT, "✅ Étape 3/6: Commande client passée avec succès.\n");
        
        return $order;
    }

    private function step_4_order_validation_and_stock_transfer(Order $order)
    {
        // L'admin se connecte
        $this->actingAs($this->admin, 'web');

        // L'admin valide la commande
        $order->status = 'validee';
        $order->save();

        // Ici, l'OrderObserver doit avoir transféré le stock.

        // VÉRIFICATION 1: Le stock de l'entrepôt principal doit avoir diminué de 10.
        $this->uniteDeVente->refresh();
        $this->assertEquals(90, $this->uniteDeVente->stock);

        // VÉRIFICATION 2: Le stock du point de vente du client (inventaire) doit avoir augmenté de 10.
        $this->assertDatabaseHas('inventories', [
            'point_de_vente_id' => $this->pointDeVente->id,
            'unite_de_vente_id' => $this->uniteDeVente->id,
            'quantity' => 10,
        ]);

        fwrite(STDOUT, "✅ Étape 4/6: Commande validée, stock transféré vers le point de vente.\n");
    }

    private function step_5_delivery_process(Order $order)
    {
        // L'admin assigne le livreur et met la commande en transit
        $this->actingAs($this->admin, 'web');
        $order->livreur_id = $this->livreur->id;
        $order->status = 'en_cours_livraison';
        $order->save();
        $order->refresh();

        // VÉRIFICATION (ADMIN): La commande est bien assignée.
        $this->assertEquals($this->livreur->id, $order->livreur_id);
        $this->assertEquals('en_cours_livraison', $order->status);

        // Le livreur se connecte et confirme la livraison
        $this->actingAs($this->livreur, 'livreur');
        
        // Simule la confirmation par le livreur
        $order->status = 'livree';
        $order->save();
        
        // VÉRIFICATION (LIVREUR): La commande est bien marquée comme livrée.
        $order->refresh();
        $this->assertEquals('livree', $order->status);

        fwrite(STDOUT, "✅ Étape 5/6: Processus de livraison terminé avec succès.\n");
    }
    
    private function step_6_payment_and_final_stock_decrease(Order $order)
    {
        // L'admin (comptable) se connecte pour enregistrer un règlement
        $this->actingAs($this->admin, 'web');

        // Le client a vendu 7 cartons sur les 10 reçus et vient payer
        $montantVerse = 7 * $this->uniteDeVente->prix_unitaire;

        // Le comptable crée le règlement
        $reglement = Reglement::factory()->create([
            'client_id' => $this->client->id,
            'montant' => $montantVerse,
            'date_reglement' => now(),
        ]);
        
        // Il associe ce règlement à la commande
        $reglement->orders()->attach($order->id);

        // Il détaille ce qui a été vendu (7 cartons)
        $reglement->detailsReglement()->create([
            'unite_de_vente_id' => $this->uniteDeVente->id,
            'quantity' => 7,
            'prix_unitaire' => $this->uniteDeVente->prix_unitaire,
        ]);

        // Manuellement, on appelle la logique de l'observer qui est normalement appelée
        // depuis la page de création Livewire.
        (new \App\Observers\ReglementObserver(new StockManager()))->created($reglement);


        // VÉRIFICATION 1 (Logistique): Le stock de l'inventaire du client doit avoir diminué de 7.
        // Il doit donc lui en rester 3. (10 reçus - 7 vendus)
        $this->assertDatabaseHas('inventories', [
            'point_de_vente_id' => $this->pointDeVente->id,
            'unite_de_vente_id' => $this->uniteDeVente->id,
            'quantity' => 3, // 10 - 7 = 3
        ]);

        // VÉRIFICATION 2 (Financière): Le statut de paiement de la commande doit être "partiel".
        $order->refresh();
        $this->assertEquals('partiel', $order->statut_paiement);
        $this->assertEquals($montantVerse, $order->montant_paye);

        fwrite(STDOUT, "✅ Étape 6/6: Règlement enregistré, stock final du client et statut financier mis à jour.\n");
        fwrite(STDOUT, "\n🎉 MISSION ACCOMPLIE: Le test d'audit du flux complet a réussi !\n");
    }
}