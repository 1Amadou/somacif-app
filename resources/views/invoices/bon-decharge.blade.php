<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bon de Décharge - {{ $arrivage->numero_bon_livraison }}</title>
    <style>
        body { font-family: sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { margin-top: 60px; }
        .signatures { margin-top: 80px; width: 100%; }
        .signatures td { border: 0; padding: 20px 0; }
        .signature-line { border-top: 1px solid #000; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bon de Décharge</h1>
        <p><strong>N° Arrivage :</strong> {{ $arrivage->numero_bon_livraison }}</p>
        <p><strong>Date :</strong> {{ $arrivage->date_arrivage->format('d/m/Y H:i') }}</p>
        <p><strong>Fournisseur :</strong> {{ $arrivage->fournisseur->nom_entreprise }}</p>
    </div>

    <h3>Produits Reçus</h3>
    <table>
        <thead>
            <tr>
                <th>Produit (Unité / Calibre)</th>
                <th>Quantité Reçue (Cartons)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($arrivage->details_produits as $detail)
            <tr>
                <td>{{ \App\Models\UniteDeVente::find($detail['unite_de_vente_id'])->nom_complet ?? 'Produit inconnu' }}</td>
                <td>{{ $detail['quantite_cartons'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td style="width: 50%; text-align: center;">
                <p><strong>Nom & Signature du Chauffeur :</strong></p>
                <div class="signature-line" style="width: 80%; margin: 60px auto 0 auto;"></div>
            </td>
            <td style="width: 50%; text-align: center;">
                <p><strong>Nom & Signature du Superviseur :</strong></p>
                <div class="signature-line" style="width: 80%; margin: 60px auto 0 auto;"></div>
            </td>
        </tr>
    </table>

    <div class="footer">
        <p>Confirmation de réception des marchandises listées ci-dessus en bon état et en quantité conforme.</p>
    </div>
</body>
</html>