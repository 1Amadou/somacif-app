<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture Proforma - {{ $order->numero_commande }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; }
        .content { margin-top: 30px; }
        .customer-info, .company-info { width: 48%; display: inline-block; vertical-align: top; }
        .company-info { text-align: right; }
        .info-section { margin-bottom: 20px; border: 1px solid #eee; padding: 15px; border-radius: 5px; }
        .info-section h2 { font-size: 16px; margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total-section { margin-top: 20px; text-align: right; }
        .total-section table { width: 40%; float: right; }
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SOMACIF</h1>
            <p>Votre partenaire en produits de qualité</p>
            <p>Adresse : [Votre Adresse], Bamako, Mali | Tél : [Votre Téléphone] | Email : [Votre Email]</p>
        </div>

        <hr>

        <div class="content">
            <h1 style="text-align: center; font-size: 20px; margin-bottom: 30px;">FACTURE PROFORMA</h1>

            <div class="info-section">
                <div class="customer-info">
                    <h2>Facturé à :</h2>
                    <p><strong>Client :</strong> {{ $order->client->nom }}</p>
                    <p><strong>Adresse :</strong> {{ $order->client->adresse ?? 'N/A' }}</p>
                    <p><strong>Téléphone :</strong> {{ $order->client->telephone ?? 'N/A' }}</p>
                </div>
                <div class="company-info">
                    <h2>Détails de la Commande :</h2>
                    <p><strong>N° Commande :</strong> {{ $order->numero_commande }}</p>
                    <p><strong>Date :</strong> {{ $order->created_at->format('d/m/Y') }}</p>
                    <p><strong>Statut Paiement :</strong> <span style="font-weight: bold;">{{ $order->statut_paiement->getLabel() }}</span></p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Article</th>
                        <th class="text-right">Quantité</th>
                        <th class="text-right">Prix Unitaire</th>
                        <th class="text-right">Total HT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->uniteDeVente->nom_complet }}</td>
                        <td class="text-right">{{ $item->quantite }}</td>
                        <td class="text-right">{{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                        <td class="text-right">{{ number_format($item->quantite * $item->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total-section">
                <table>
                    <tr>
                        <td><strong>Total HT</strong></td>
                        <td class="text-right">{{ number_format($order->montant_total, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    <tr>
                        <td><strong>Total TTC</strong></td>
                        <td class="text-right"><strong>{{ number_format($order->montant_total, 0, ',', ' ') }} FCFA</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="footer">
            <p>Merci de votre confiance.</p>
            <p>SOMACIF - [Votre NIF/RCCM]</p>
            {{-- Compteur d'impression ajouté --}}
            @if(isset($order->invoice_print_count))
                <p>Imprimé {{ $order->invoice_print_count }} fois.</p>
            @endif
        </div>
    </div>
</body>
</html>