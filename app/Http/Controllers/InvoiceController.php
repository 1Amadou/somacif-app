<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf; // Importez la façade PDF

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
}