<!DOCTYPE html>
<html>
<head>
    <title>Bordereau de Règlement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; }
        .content { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bordereau de Règlement N°: {{ $reglement->id }}</h1>
            <p>Date: {{ $reglement->date_reglement->format('d/m/Y') }}</p>
        </div>

        <div class="content">
            <p><strong>Client:</strong> {{ $reglement->client->nom }}</p>
            <p><strong>Enregistré par:</strong> {{ $reglement->user->name }}</p>

            <h3>Détail des ventes déclarées</h3>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix de Vente</th>
                        <th>Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reglement->details as $detail)
                    <tr>
                        <td>{{ $detail->uniteDeVente->nom_unite }}</td>
                        <td>{{ $detail->quantite_vendue }}</td>
                        <td>{{ number_format($detail->prix_de_vente_unitaire, 0, ',', ' ') }} CFA</td>
                        <td>{{ number_format($detail->quantite_vendue * $detail->prix_de_vente_unitaire, 0, ',', ' ') }} CFA</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <h3>Récapitulatif</h3>
            <p><strong>Total des ventes calculé:</strong> <span class="total">{{ number_format($reglement->montant_calcule, 0, ',', ' ') }} CFA</span></p>
            <p><strong>Montant Versé:</strong> <span class="total">{{ number_format($reglement->montant_verse, 0, ',', ' ') }} CFA</span></p>

            <p><strong>Notes:</strong> {{ $reglement->notes ?? 'Aucune' }}</p>
        </div>

        <div class="footer">
            <p>Signature Client: _________________________</p>
            <p>Signature Comptable: _________________________</p>
        </div>
    </div>
</body>
</html>