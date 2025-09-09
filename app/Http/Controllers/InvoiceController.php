<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Models\Reglement;
use App\Filament\Pages\SuiviParArrivage;
use App\Models\Arrivage;



class InvoiceController extends Controller
{
    public function show(Order $order)
    {
        // Sécurité : on vérifie que la commande appartient bien au client connecté
        if ($order->client_id !== session('authenticated_client_id')) {
            abort(403);
        }

        // On charge les relations nécessaires
        $order->load('client', 'orderItems');

        $pdf = Pdf::loadView('invoices.order-invoice', ['order' => $order]);

        // On propose le téléchargement du PDF
        return $pdf->download('Facture-SOMACIF-' . $order->numero_commande . '.pdf');
    }

    /**
     * Génère et télécharge le PDF d'un bordereau de règlement.
     */
    public function downloadReglementPdf(Reglement $reglement)
    {
        $pdf = Pdf::loadView('invoices.reglement-invoice', [
            'reglement' => $reglement
        ]);

        return $pdf->download('bordereau-reglement-' . $reglement->id . '.pdf');
    }

     /**
     * Génère et télécharge le PDF d'une facture de commande.
     */
    public function downloadOrderInvoice(Order $order)
    {
        // Chargement des relations imbriquées nécessaires pour la vue de la facture
        $order->load(['items.uniteDeVente.product', 'client']);
        
        // CORRECTION : Utilisez le nom correct du fichier de vue
        // Le nom de la vue est 'invoices.order-invoice', pas 'invoices.commande-facture'
        $pdf = PDF::loadView('invoices.order-invoice', compact('order'));
        return $pdf->download('facture-commande-' . $order->numero_commande . '.pdf');
    }

    /**
     * Génère la facture proforma pour une commande spécifique.
     */
    public function generateOrderInvoice(Order $order)
    {
        $order->increment('facture_proforma_print_count'); // Incrémente le compteur
        $order->load('client', 'pointDeVente', 'items.uniteDeVente.product');
        $pdf = Pdf::loadView('invoices.order-invoice', ['order' => $order]);
        return $pdf->download('facture-proforma-' . $order->numero_commande . '.pdf');
    }

    /**
     * Génère le bon de livraison pour une commande spécifique.
     */
    public function generateDeliveryNote(Order $order)
    {
        $order->increment('bon_livraison_print_count'); // Incrémente le compteur
        $order->load('client', 'pointDeVente', 'items.uniteDeVente.product', 'livreur');
        $pdf = Pdf::loadView('invoices.bon-livraison', ['order' => $order]);
        return $pdf->download('bon-de-livraison-' . $order->numero_commande . '.pdf');
    }

    /**
     * Génère le reçu de versement pour un règlement spécifique.
     */
    public function generateReglementReceipt(Reglement $reglement)
    {
        $reglement->increment('recu_versement_print_count');
        // On charge toutes les données nécessaires
        $reglement->load('client', 'order.pointDeVente', 'details.uniteDeVente.product');
        
        // On récupère l'état du stock ACTUEL du point de vente concerné
        $stockDuPointDeVente = [];
        if ($reglement->order && $reglement->order->pointDeVente) {
            $stockDuPointDeVente = $reglement->order->pointDeVente
                                      ->lieuDeStockage
                                      ->inventories()
                                      ->with('uniteDeVente.product')
                                      ->where('quantite_stock', '>', 0)
                                      ->get();
        }

        $data = [
            'reglement' => $reglement,
            'stockDuPointDeVente' => $stockDuPointDeVente,
        ];

        $pdf = Pdf::loadView('invoices.reglement-receipt', $data);
        return $pdf->download('recu-versement-' . $reglement->id . '-' . $reglement->client->nom . '.pdf');
    }

    /**
     * NOUVEAU : Génère le rapport de suivi pour un arrivage spécifique.
     */
    public function generateArrivageReport(\App\Models\Arrivage $arrivage)
    {
        // On instancie la page Filament pour réutiliser sa logique de calcul
        $suiviPage = new SuiviParArrivage();
        $suiviPage->selectedArrivageId = $arrivage->id;
        $data = $suiviPage->getSelectedArrivageData();

        if (!$data) {
            abort(404, "Données du rapport non trouvées.");
        }
        
        $pdf = Pdf::loadView('invoices.arrivage-report', ['data' => $data]);
        return $pdf->download('rapport-suivi-' . $arrivage->numero_bon_livraison . '.pdf');
    }
}