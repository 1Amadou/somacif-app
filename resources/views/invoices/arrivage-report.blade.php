<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rapport de Suivi - {{ $data['arrivage']->numero_bon_livraison }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        .container { width: 100%; }
        .header h1 { margin: 0; font-size: 20px; text-align: center; }
        .header p { margin: 4px 0; text-align: center; }
        h2 { font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-size: 9px; }
        .text-right { text-align: right; }
        .summary-grid { margin: 15px 0; width: 100%; }
        .summary-box { width: 32%; display: inline-block; border: 1px solid #eee; padding: 10px; text-align: center; }
        .summary-box p { margin: 0; font-size: 9px; color: #555; }
        .summary-box .value { font-size: 14px; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rapport de Suivi d'Arrivage</h1>
            <p><strong>Arrivage N° :</strong> {{ $data['arrivage']->numero_bon_livraison }} | <strong>Date :</strong> {{ $data['arrivage']->date_arrivage->format('d/m/Y') }}</p>
            <p><strong>Fournisseur :</strong> {{ $data['arrivage']->fournisseur->nom_entreprise }}</p>
        </div>

        <h2>Résumé Global</h2>
        <div class="summary-grid">
            <div class="summary-box">
                <p>Coût Total d'Achat</p>
                <span class="value negative">{{ number_format($data['totalCoutAchat'], 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="summary-box">
                <p>Revenu Total Généré</p>
                <span class="value positive">{{ number_format($data['totalRevenuGenere'], 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="summary-box">
                <p>Marge Brute Réalisée</p>
                <span class="value {{ $data['margeGlobale'] >= 0 ? 'positive' : 'negative' }}">{{ number_format($data['margeGlobale'], 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        <h2>Résumé par Produit</h2>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th class="text-right">Qté Reçue</th>
                    <th class="text-right">Stock Actuel</th>
                    <th class="text-right">Qté Vendue</th>
                    <th class="text-right">Revenu Généré</th>
                    <th class="text-right">Marge</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['reportData'] as $item)
                <tr>
                    <td>{{ $item['nom_complet'] }}</td>
                    <td class="text-right">{{ number_format($item['quantite_recue']) }}</td>
                    <td class="text-right">{{ number_format($item['stock_total_actuel']) }}</td>
                    <td class="text-right">{{ number_format($item['quantite_vendue']) }}</td>
                    <td class="text-right">{{ number_format($item['revenu_genere'], 0, ',', ' ') }}</td>
                    <td class="text-right {{ $item['marge_sur_ventes'] >= 0 ? 'positive' : 'negative' }}">{{ number_format($item['marge_sur_ventes'], 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Sections détaillées pour chaque produit --}}
        @foreach ($data['reportData'] as $item)
            <div style="margin-top: 20px; page-break-inside: avoid;">
                <h3 style="font-size: 12px; font-weight: bold; color: #0d6efd;">Analyse Détaillée : {{ $item['nom_complet'] }}</h3>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 48%; vertical-align: top; padding-right: 10px;">
                            <h4 style="font-size: 11px; margin-bottom: 5px;">Détail des Ventes</h4>
                            @if($item['ventes_detaillees']->isNotEmpty())
                                <table style="font-size: 9px;">
                                    @foreach($item['ventes_detaillees'] as $vente)
                                    <tr>
                                        <td>{{ $vente->quantite_totale }} unités vendues à</td>
                                        <td class="text-right">{{ number_format($vente->prix_de_vente_unitaire, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                    @endforeach
                                </table>
                            @else
                                <p style="font-size: 9px;">Aucune vente pour ce produit.</p>
                            @endif
                        </td>
                        <td style="width: 48%; vertical-align: top; padding-left: 10px;">
                             <h4 style="font-size: 11px; margin-bottom: 5px;">Répartition du Stock Restant</h4>
                             <table style="font-size: 9px;">
                                <tr>
                                    <td>Entrepôt Principal</td>
                                    <td class="text-right"><b>{{ number_format($item['stock_entrepot_actuel']) }}</b></td>
                                </tr>
                                @foreach($item['repartition_stock'] as $stock)
                                <tr>
                                    <td>PDV: {{ $stock['nom_pdv'] }}</td>
                                    <td class="text-right">{{ number_format($stock['quantite']) }}</td>
                                </tr>
                                @endforeach
                             </table>
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach
    </div>
    <div class="footer">
        <p>Rapport généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>