<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture - {{ $order->numero_commande }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; line-height: 1.6; }
        .container { width: 100%; margin: 0 auto; padding: 20px; }
        .header { display: table; width: 100%; margin-bottom: 20px; }
        .header .logo { display: table-cell; text-align: left; vertical-align: middle; }
        .header .company-info { display: table-cell; text-align: right; vertical-align: middle; }
        .header .company-info h1 { font-size: 24px; color: #004D99; margin: 0; }
        .header .company-info p { margin: 2px 0; font-size: 10px; }
        .info-section { display: table; width: 100%; border-spacing: 15px; }
        .info-section .column { display: table-cell; width: 50%; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .info-section .column p { margin: 2px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .items-table th { background-color: #f8f8f8; font-weight: bold; }
        .text-right { text-align: right; }
        .totals-section { float: right; width: 40%; margin-top: 20px; }
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { padding: 8px; }
        .totals-table .total-row { background-color: #f2f2f2; font-weight: bold; }
        .totals-table .total-amount { font-size: 18px; color: #D32F2F; font-weight: bold; }
        .signature-section { display: table; width: 100%; margin-top: 50px; }
        .signature-section .column { display: table-cell; width: 50%; text-align: center; }
        .signature-section p { margin-bottom: 50px; }
        .signature-line { border-top: 1px dashed #aaa; width: 60%; margin: 0 auto; }
        .status-badge { 
            padding: 4px 8px; 
            border-radius: 12px; 
            font-weight: bold; 
            font-size: 10px; 
            color: #fff;
            text-transform: uppercase;
        }
        .status-validee { background-color: #4CAF50; }
        .status-en_attente { background-color: #FFC107; }
        .status-annulee { background-color: #F44336; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="{{ public_path('images/somacif-logo.png') }}" alt="Logo SOMACIF" style="height: 60px;">
            </div>
            <div class="company-info">
                <h1>SOMACIF</h1>
                <p>Le Leader de la Distribution de Poisson Congelé au Mali</p>
                <p>ACI 2000, Bamako, Mali | Tél: +223 76 00 00 00 | Email: contact@somacif.ml</p>
            </div>
        </div>

        <div class="info-section">
            <div class="column">
                <strong>Facturé à :</strong>
                <p>{{ $order->client?->nom }}</p>
                <p>{{ $order->client?->telephone }}</p>
                <p>ID Client: {{ $order->client?->identifiant_unique_somacif }}</p>
            </div>
            <div class="column">
                <p><strong>Facture N° :</strong> {{ $order->numero_commande }}</p>
                <p><strong>Date de la commande :</strong> {{ $order->created_at->format('d/m/Y') }}</p>
                <p>
                    <strong>Statut :</strong> 
                    <span class="status-badge status-{{ $order->statut }}">
                        {{ $order->statut }}
                    </span>
                </p>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Calibre</th>
                    <th class="text-right">Quantité (cartons)</th>
                    <th class="text-right">Prix Unitaire</th>
                    <th class="text-right">Sous-total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->uniteDeVente?->product?->nom }}</td>
                    <td>{{ $item->uniteDeVente?->calibre }}</td>
                    <td class="text-right">{{ $item->quantite }}</td>
                    <td class="text-right">{{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                    <td class="text-right">{{ number_format($item->prix_unitaire * $item->quantite, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td><strong>Montant Total :</strong></td>
                    <td class="text-right">{{ number_format($order->montant_total, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <td><strong>Montant Payé :</strong></td>
                    <td class="text-right">- {{ number_format($order->montant_paye, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Solde Restant à Payer :</strong></td>
                    <td class="text-right total-amount">{{ number_format($order->montant_total - $order->montant_paye, 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <div class="signature-section">
            <div class="column">
                <p>Signature du Client</p>
                <div class="signature-line"></div>
            </div>
            <div class="column">
                <p>Signature SOMACIF</p>
                <div class="signature-line"></div>
            </div>
        </div>

    </div>
</body>
</html>