<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reçu de Versement - {{ $reglement->id }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        .container { width: 100%; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 5px 0; }
        .content { margin-top: 20px; }
        .info-section { margin-bottom: 20px; }
        .info-section div { width: 48%; display: inline-block; vertical-align: top; }
        .info-section .right { text-align: right; }
        h2 { font-size: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 7px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total-summary { font-size: 14px; font-weight: bold; text-align: center; padding: 10px; background-color: #f2f2f2; border: 1px solid #ccc; margin: 20px 0; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #777; }
        .no-border-table td { border: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="text-align: center;">SOMACIF - REÇU DE VERSEMENT</h1>
        </div>
        <hr>
        <div class="content">
            <div class="info-section">
                <div>
                    <p><strong>Client :</strong> {{ $reglement->client->nom }}</p>
                    <p><strong>Point de Vente :</strong> {{ $reglement->order->pointDeVente->nom }}</p>
                </div>
                <div class="right">
                    <p><strong>Date du Reçu :</strong> {{ now()->format('d/m/Y H:i') }}</p>
                    <p><strong>Versement N° :</strong> {{ $reglement->numero_versement }}</p>
                    <p><strong>Commande :</strong> {{ $reglement->order->numero_commande }}</p>
                </div>
            </div>

            <div class="total-summary">
                Montant Versé : {{ number_format($reglement->montant_verse, 0, ',', ' ') }} FCFA
            </div>

            <h2>Détails des Articles Vendus (Ce Versement)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Article</th>
                        <th class="text-right">Qté Vendue</th>
                        <th class="text-right">Prix Unitaire</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reglement->details as $detail)
                    <tr>
                        <td>{{ $detail->uniteDeVente->nom_complet }}</td>
                        <td class="text-right">{{ $detail->quantite_vendue }}</td>
                        <td class="text-right">{{ number_format($detail->prix_de_vente_unitaire, 0, ',', ' ') }} FCFA</td>
                        <td class="text-right">{{ number_format($detail->quantite_vendue * $detail->prix_de_vente_unitaire, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <h2>Situation de la Commande (Après ce versement)</h2>
            <table class="no-border-table">
                <tr>
                    <td>Total Proforma : {{ number_format($reglement->order->montant_total, 0, ',', ' ') }} FCFA</td>
                    <td>Total Encaissé : {{ number_format($reglement->order->total_verse, 0, ',', ' ') }} FCFA</td>
                    <td class="text-right highlight">Solde à Payer : {{ number_format($reglement->order->solde_restant_a_payer, 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>

            <h2>État du Stock du Point de Vente (Après Opération)</h2>
            <table>
                 <thead>
                    <tr>
                        <th>Article</th>
                        <th class="text-right">Quantité en Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockDuPointDeVente as $inventory)
                    <tr>
                        <td>{{ $inventory->uniteDeVente->nom_complet }}</td>
                        <td class="text-right">{{ $inventory->quantite_stock }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2">Le stock de ce point de vente est vide.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>Ce document atteste du versement de la somme indiquée ci-dessus.</p>
            <p>Imprimé {{ $reglement->recu_versement_print_count }} fois.</p>
        </div>
    </div>
</body>
</html>