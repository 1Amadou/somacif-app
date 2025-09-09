<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bon de Livraison - {{ $order->numero_commande }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; }
        .content { margin-top: 20px; }
        .info-section { margin-bottom: 20px; padding: 10px; }
        .info-section div { width: 48%; display: inline-block; vertical-align: top; }
        .info-section .right { text-align: right; }
        h2 { font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .signatures { margin-top: 80px; width: 100%; }
        .signature-box { width: 30%; display: inline-block; text-align: center; margin: 0 1.5%; }
        .signature-box p { border-top: 1px solid #333; padding-top: 5px; margin-top: 40px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SOMACIF</h1>
            <p>Votre partenaire en produits de qualité</p>
        </div>
        
        <hr>

        <div class="content">
            <h1 style="text-align: center; font-size: 20px; margin-bottom: 20px;">BON DE LIVRAISON</h1>

            <div class="info-section">
                <div>
                    <h2>Informations Client</h2>
                    <p><strong>Client :</strong> {{ $order->client->nom }}</p>
                    <p><strong>Livrer à :</strong> {{ $order->pointDeVente->nom }}</p>
                    <p><strong>Adresse :</strong> {{ $order->pointDeVente->adresse ?? 'N/A' }}</p>
                </div>
                <div class="right">
                    <h2>Informations Livraison</h2>
                    <p><strong>N° Commande :</strong> {{ $order->numero_commande }}</p>
                    <p><strong>Date :</strong> {{ now()->format('d/m/Y') }}</p>
                    <p><strong>Livreur :</strong> {{ $order->livreur?->full_name ?? 'N/A' }}</p>
                </div>
            </div>

            <h2>Articles à Livrer</h2>
            <table>
                <thead>
                    <tr>
                        <th>Article</th>
                        <th class="text-right">Quantité</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->uniteDeVente->nom_complet }}</td>
                        <td class="text-right">{{ $item->quantite }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="signatures">
                <div class="signature-box">
                    <p>Signature Expéditeur</p>
                </div>
                <div class="signature-box">
                    <p>Signature Livreur</p>
                </div>
                <div class="signature-box">
                    <p>Signature & Cachet Client</p>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Merci de vérifier la conformité de la marchandise.</p>
            {{-- Compteur d'impression ajouté --}}
            @if(isset($order->delivery_note_print_count))
                <p>Imprimé {{ $order->delivery_note_print_count }} fois.</p>
            @endif
        </div>
    </div>
</body>
</html>