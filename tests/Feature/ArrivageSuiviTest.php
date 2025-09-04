<?php

namespace Tests\Feature;

use App\Models\Arrivage;
use App\Models\Fournisseur;
use App\Models\UniteDeVente;
use App\Models\User;
use App\Models\VenteDirecte;
use App\Models\Reglement;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Client;
use App\Filament\Resources\ArrivageResource\Pages\CreateArrivage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArrivageSuiviTest extends TestCase
{
    use RefreshDatabase;

    public function test_arrivage_and_suivi_page_logic_is_correct()
    {
        // ÉTAPE 1: PRÉPARATION DES DONNÉES
        $user = User::factory()->create();
        $fournisseur = Fournisseur::factory()->create();
        $client = Client::factory()->create();
        
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $unite1 = UniteDeVente::factory()->create([
            'stock' => 0,
            'product_id' => $product1->id,
        ]);
        
        $unite2 = UniteDeVente::factory()->create([
            'stock' => 0,
            'product_id' => $product2->id,
        ]);
        
        $this->actingAs($user);

        // ÉTAPE 2: CRÉATION D'UN ARRIVAGE EN UTILISANT LIVEWIRE
        $component = Livewire::test(CreateArrivage::class);

        // Définir les données du formulaire directement sur la propriété `data` du composant Livewire
        $component->set('data', [
            'fournisseur_id' => $fournisseur->id,
            'numero_bon_livraison' => 'BL-TEST-1234',
            'date_arrivage' => now()->toDateString(),
            'notes' => 'Test de l\'arrivage',
            'details_produits' => [
                [
                    'unite_de_vente_id' => $unite1->id,
                    'quantite' => 100,
                    'prix_achat_unitaire' => 10000,
                ],
                [
                    'unite_de_vente_id' => $unite2->id,
                    'quantite' => 50,
                    'prix_achat_unitaire' => 20000,
                ],
            ],
        ]);

        // Simuler la soumission du formulaire
        $component->call('create');
        $component->assertHasNoFormErrors();

        // ÉTAPE 3: VÉRIFICATION DE L'ARRIVAGE ET DU STOCK
        $arrivage = Arrivage::first();
        $this->assertNotNull($arrivage);
        $this->assertEquals(100, $unite1->fresh()->stock);
        $this->assertEquals(50, $unite2->fresh()->stock);

        // Ventes prévisionnelles
        $vente1Quantite = 20;
        $vente1PrixVente = 15000;
        $vente2Quantite = 30;
        $vente2PrixVente = 25000;
        
        // ÉTAPE 4: SIMULATION DES VENTES ET RÈGLEMENTS
        $venteDirecte = VenteDirecte::create([
            'date_vente' => now(),
            'client_id' => $client->id,
            'items' => [
                [
                    'unite_de_vente_id' => $unite1->id,
                    'quantite' => $vente1Quantite,
                    'prix_unitaire' => $vente1PrixVente,
                ],
            ],
        ]);
        
        $reglement = Reglement::create([
            'date_reglement' => now(),
            'montant' => $vente2Quantite * $vente2PrixVente,
            'client_id' => $client->id,
            'details' => [
                [
                    'unite_de_vente_id' => $unite2->id,
                    'quantite_vendue' => $vente2Quantite,
                    'prix_de_vente_unitaire' => $vente2PrixVente,
                ],
            ],
        ]);
        
        $this->assertEquals(100 - $vente1Quantite, $unite1->fresh()->stock);
        
        $inventory2 = Inventory::where('unite_de_vente_id', $unite2->id)->where('client_id', $client->id)->first();
        $this->assertEquals(50, $unite2->fresh()->stock);
        $this->assertEquals(50 - $vente2Quantite, $inventory2->fresh()->quantite_stock);

        // ÉTAPE 5: VÉRIFICATION DE LA LOGIQUE DE LA PAGE DE SUIVI
        $suiviPage = new \App\Filament\Pages\SuiviParArrivage();
        $suiviPage->selectedArrivageId = $arrivage->id;
        $reportData = $suiviPage->getSelectedArrivageData();

        $this->assertNotNull($reportData);

        // Calculs attendus
        $expectedTotalCoutAchat = (100 * 10000) + (50 * 20000);
        $expectedTotalVentes = ($vente1Quantite * $vente1PrixVente) + ($vente2Quantite * $vente2PrixVente);
        $expectedBenefice = $expectedTotalVentes - $expectedTotalCoutAchat;

        // Vérification des totaux globaux
        $this->assertEquals($expectedTotalCoutAchat, $reportData['totalCoutAchat']);
        $this->assertEquals($expectedTotalVentes, $reportData['totalMontantVentes']);
        $this->assertEquals($expectedBenefice, $reportData['benefice']);

        // Vérification des données pour chaque produit
        $product1Data = collect($reportData['reportData'])->firstWhere('nom_produit', $unite1->nom_unite . ' (' . $unite1->calibre . ')');
        $this->assertEquals(100, $product1Data['quantite_recue_arrivage']);
        $this->assertEquals($vente1Quantite, $product1Data['quantite_vendue_total']);
        $this->assertEquals($vente1Quantite * $vente1PrixVente, $product1Data['montant_ventes_total']);
        $this->assertEquals(100 - $vente1Quantite, $product1Data['stock_entrepot_actuel']);
        $this->assertEquals(0, $product1Data['stock_chez_clients_total']);
        
        $product2Data = collect($reportData['reportData'])->firstWhere('nom_produit', $unite2->nom_unite . ' (' . $unite2->calibre . ')');
        $this->assertEquals(50, $product2Data['quantite_recue_arrivage']);
        $this->assertEquals($vente2Quantite, $product2Data['quantite_vendue_total']);
        $this->assertEquals($vente2Quantite * $vente2PrixVente, $product2Data['montant_ventes_total']);
        $this->assertEquals(50, $product2Data['stock_entrepot_actuel']);
        $this->assertEquals($vente2Quantite, $product2Data['stock_chez_clients_total']);
    }
}