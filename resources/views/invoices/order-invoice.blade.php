<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $order->numero_commande }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; }
        .header h1 { font-size: 24px; color: #D32F2F; margin: 0; }
        .header p { margin: 2px 0; }
        .content { margin-top: 30px; }
        .customer-info, .order-info { width: 48%; display: inline-block; vertical-align: top; }
        .order-info { text-align: right; }
        .info-box { border: 1px solid #ccc; padding: 15px; border-radius: 5px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .totals { float: right; width: 40%; margin-top: 20px; }
        .totals table { width: 100%; }
        .text-right { text-align: right; }
        .brand-red { color: #D32F2F; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            {{-- Vous pouvez mettre le logo ici si vous le souhaitez --}}
            <h1>SOMACIF</h1>
            <p>Le Leader de la Distribution de Poisson Congelé au Mali</p>
            <p>ACI 2000, Bamako, Mali | Tél: +223 76 00 00 00 | Email: contact@somacif.ml</p>
        </div>

        <div class="info-box">
            <div class="customer-info">
                <strong>Facturé à :</strong><br>
                {{ $order->client->nom }}<br>
                {{ $order->client->telephone }}<br>
                ID Client: {{ $order->client->identifiant_unique_somacif }}
            </div>
            <div class="order-info">
                <strong>Facture N° :</strong> {{ $order->numero_commande }}<br>
                <strong>Date de la commande :</strong> {{ $order->created_at->format('d/m/Y') }}<br>
                <strong>Statut :</strong> {{ $order->statut }}<br>
                @if($order->due_date)
                    <strong>Échéance de paiement :</strong> <span class="brand-red">{{ $order->due_date->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>

        <div class="content">
            <table>
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
                    @foreach($order->orderItems as $item)
                    <tr>
                        <td>{{ $item->nom_produit }}</td>
                        <td>{{ $item->calibre }}</td>
                        <td class="text-right">{{ $item->quantite }}</td>
                        <td class="text-right">{{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                        <td class="text-right">{{ number_format($item->prix_unitaire * $item->quantite, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="totals">
            <table>
                <tr>
                    <td><strong>Montant Total :</strong></td>
                    <td class="text-right">{{ number_format($order->montant_total, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <td><strong>Montant Payé :</strong></td>
                    <td class="text-right">- {{ number_format($order->amount_paid, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td><strong>Solde Restant à Payer :</strong></td>
                    <td class="text-right brand-red">{{ number_format($order->remaining_balance, 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>
        </div>

    </div>
</body>
</html>