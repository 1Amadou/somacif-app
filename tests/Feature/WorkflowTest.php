<?php

namespace Tests\Feature;

use App\Models\Arrivage;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PointDeVente;
use App\Models\Product;
use App\Models\UniteDeVente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_lifecycle_workflow(): void
    {
        // --- 1. PRÉPARATION ---
        $adminUser = User::factory()->create();
        $fournisseur = Fournisseur::factory()->create();
        $client = Client::factory()->create(); // On crée un client simple
        
        // On crée un point de vente ET on l'associe à notre client
        $pointDeVente = PointDeVente::factory()->create(['responsable_id' => $client->id]);

        $produit = Product::factory()
            ->has(UniteDeVente::factory()->state(['stock' => 0]), 'uniteDeVentes')
            ->create();
        $uniteDeVente = $produit->uniteDeVentes->first();

        // --- 2. ARRIVAGE ---
        Arrivage::factory()->create([
            'fournisseur_id' => $fournisseur->id,
            'user_id' => $adminUser->id,
            'details_produits' => [
                ['unite_de_vente_id' => $uniteDeVente->id, 'quantite_cartons' => 100]
            ]
        ]);

        // --- 3. VÉRIFICATION DU STOCK PRINCIPAL ---
        $uniteDeVente->refresh();
        $this->assertEquals(100, $uniteDeVente->stock);

        // --- 4. CRÉATION DE LA COMMANDE ---
        $commande = Order::factory()
            ->for($client)
            ->hasItems(1, [
                'unite_de_vente_id' => $uniteDeVente->id,
                'quantite' => 20,
            ])
            ->create(['statut' => 'Reçue']);

        // --- 5. VALIDATION DE LA COMMANDE ---
        $commande->statut = 'Validée';
        $commande->save();

        // --- 6. VÉRIFICATION FINALE ---
        $uniteDeVente->refresh();
        $inventaireClient = Inventory::where('point_de_vente_id', $pointDeVente->id)
                                    ->where('unite_de_vente_id', $uniteDeVente->id)
                                    ->first();

        $this->assertEquals(80, $uniteDeVente->stock);
        $this->assertNotNull($inventaireClient);
        $this->assertEquals(20, $inventaireClient->quantite_stock);
    }
}