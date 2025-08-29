<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Models\Reglement;

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
}