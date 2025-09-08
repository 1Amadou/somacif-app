<?php

use App\Filament\Pages\Documentation;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

?>
<x-filament-panels::page>
    <div class="space-y-12 prose dark:prose-invert max-w-none">
        <section>
            <h1>Guide d'Utilisation de l'Application SOMACIF</h1>
            <p class="lead">
                Bienvenue dans la documentation officielle de votre application de gestion. Ce guide est conçu pour vous aider à maîtriser chaque aspect de l'outil, de la gestion du catalogue à l'analyse des ventes, en mettant l'accent sur la <strong>robustesse</strong> et la <strong>logique métier</strong>.
            </p>
        </section>

        <section>
            <h2>Le Workflow Général</h2>
            <p>
                L'application est construite autour d'un cycle de vie logique et traçable de la marchandise :
            </p>
            <ol>
                <li><strong>Le Catalogue :</strong> Créez les Produits et leurs Unités de Vente.</li>
                <li><strong>L'Arrivage :</strong> Enregistrez la marchandise pour augmenter le stock de l'entrepôt principal.</li>
                <li><strong>La Commande :</strong> Transférez le stock de l'entrepôt principal vers l'inventaire d'un distributeur.</li>
                <li><strong>La Vente :</strong> Enregistrez la sortie du stock, soit via une <strong>Vente Directe</strong>, soit via un <strong>Règlement client</strong>.</li>
                <li><strong>Le Suivi :</strong> Analysez la rentabilité et l'état des stocks.</li>
            </ol>
        </section>

        <hr>

        <section>
            <h2 id="validation">La Validation des Données : Le Cœur de la Fiabilité du Système</h2>
            <p>
                L'objectif principal de l'application SOMACIF n'est pas seulement d'enregistrer des données, mais de garantir que ces données sont <strong>cohérentes, justes et qu'elles reflètent la réalité du terrain</strong>. Pour atteindre cet objectif, nous avons mis en place des mécanismes de validation stricts à chaque étape critique du workflow. Ces mécanismes agissent comme des "gardiens" qui protègent l'intégrité de votre stock et de vos finances.
            </p>

            <h3>Principe Fondamental : Aucune Action sans Vérification</h3>
            <p>
                Chaque opération qui modifie le stock ou la comptabilité passe par un ou plusieurs points de contrôle. Si une seule condition n'est pas respectée, l'opération est bloquée et un message d'erreur clair est affiché à l'utilisateur.
            </p>
        </section>

        <section>
            <h3>1. Le Cycle de l'Arrivage : La Garantie d'un Stock Juste</h3>
            <p>
                L'arrivage est la source de tout votre stock. Sa logique doit être <strong>atomique et réversible</strong> pour éviter les "stocks fantômes".
            </p>
            <h4>La Solution Robuste (via <code>ArrivageObserver</code>)</h4>
            <p>
                Chaque action sur un arrivage est surveillée et a une contre-opération directe et instantanée sur le stock.
            </p>
            <ul>
                <li><strong>Création :</strong> Le stock de l'entrepôt principal est augmenté.</li>
                <li><strong>Suppression :</strong> Le stock est retiré, annulant parfaitement l'opération initiale.</li>
                <li><strong>Modification :</strong> Le système effectue deux actions pour garantir l'exactitude : il <strong>annule complètement</strong> l'ancien stock, puis il <strong>applique ensuite</strong> le nouvel arrivage.</li>
            </ul>

            <h4>Diagramme du Flux de Modification d'un Arrivage</h4>
            <div class="w-full">
                <img src="{{ asset('storage/docs/diagramme-arrivage.svg') }}" alt="Diagramme de flux de modification d'un arrivage" class="mx-auto" />
            </div>
            <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                (Note : Pour afficher le diagramme, assurez-vous que le fichier SVG est disponible à ce chemin.)
            </p>
        </section>

        <hr>

        <section>
            <h3>2. Le Cycle de la Commande : La Protection contre la Vente à Perte</h3>
            <p>
                Une commande transfère le stock de l'entrepôt vers un client. C'est l'opération la plus risquée car elle pourrait mener à des stocks négatifs.
            </p>
            <h4>La Solution Robuste (implémentée dans les formulaires de commande)</h4>
            <p>
                La validation du stock se déclenche uniquement lorsque le statut de la commande passe à <strong>"Validée"</strong>. Le système vérifie que le stock disponible dans l'entrepôt principal est suffisant pour tous les articles. Si un seul article manque, l'opération est bloquée et une notification d'erreur détaillée s'affiche.
            </p>
            <h4>Diagramme du Flux de Validation d'une Commande</h4>
            <div class="w-full">
                <img src="{{ asset('storage/docs/diagramme-commande.svg') }}" alt="Diagramme de flux de validation d'une commande" class="mx-auto" />
            </div>
        </section>

        <hr>

        <section>
            <h3>3. Le Cycle du Règlement : La Garantie de la Cohérence Ventes/Stock</h3>
            <p>
                Le règlement enregistre les ventes d'un distributeur. Sa logique doit garantir que le client ne déclare pas vendre plus que ce qu'il a reçu.
            </p>
            <h4>La Solution Robuste (implémentée dans <code>ReglementResource.php</code>)</h4>
            <p>
                Avant d'enregistrer, le système regroupe toutes les lignes de vente par article et additionne les quantités vendues. Il compare ensuite cette somme à la quantité initialement reçue dans la commande associée. Si la quantité vendue est supérieure à la quantité reçue, l'opération est bloquée.
            </p>
        </section>

        <hr>

        {{-- DÉTAIL DES MODULES --}}

        <section>
            <h2>Stock & Catalogue</h2>
            <h3>Produits</h3>
            <p>Ce module est la base de votre inventaire. Un <strong>Produit</strong> est l'entité générale (ex: "Tilapia"). Une <strong>Unité de Vente (U.V.)</strong> est la manière dont il est conditionné et vendu (ex: "Carton 10kg"). Pour éviter toute confusion, le système utilise un <strong>"Nom Complet"</strong> unique partout : <code>Nom Produit (Nom Unité, Calibre)</code>.</p>
        </section>

        <section>
            <h2>Gestion de Stock</h2>
            <h3>Arrivages</h3>
            <p>C'est le seul point d'entrée de la marchandise dans l'entrepôt principal. La logique de stock est atomique : toute création, modification ou suppression d'un arrivage met à jour le stock de manière fiable.</p>
            <h3>Stock Entrepôt Principal</h3>
            <p>Cette page offre une vue d'inventaire complète de l'entrepôt central. Elle liste toutes les Unités de Vente et propose un raccourci pour créer un "Nouvel Arrivage".</p>
            <h3>Suivi par Arrivage</h3>
            <p>Permet d'analyser le flux de stock pour une cargaison spécifique : ce qui a été reçu, ce qui est encore en stock, et ce qui est sorti.</p>
            <h3>Transferts de Stock</h3>
            <p>Permet de déplacer du stock entre les points de vente ou de retourner de la marchandise à l'entrepôt principal. Le formulaire affiche les stocks en temps réel pour éviter les erreurs.</p>
        </section>

        <section>
            <h2>Ventes & Commandes</h2>
            <h3>Orders (Commandes)</h3>
            <p>Une commande est un document de transfert de stock de l'entrepôt vers un point de vente client. Lors du passage au statut <strong>"Validée"</strong>, le système <strong>vérifie impérativement</strong> la disponibilité du stock dans l'entrepôt principal.</p>
            <h3>Règlements Clients</h3>
            <p>Permet d'enregistrer les ventes d'un distributeur. Le système <strong>valide</strong> que la quantité vendue ne dépasse pas la quantité commandée et que le montant versé correspond au montant des ventes déclarées. La validation déclenche le déstockage de l'inventaire du client.</p>
        </section>

        <section>
            <h2>Ventes en Gros</h2>
            <h3>Ventes Directes</h3>
            <p>Pour les ventes rapides au comptoir. Le système déduit le stock directement de l'entrepôt principal et <strong>interdit</strong> de vendre une quantité supérieure au stock disponible.</p>
        </section>

        <section>
            <h2>Clients & Partenaires</h2>
            <h3>Clients, Fournisseurs, Points de Vente</h3>
            <p>Ces modules permettent de gérer les fiches d'information de vos partenaires commerciaux. Les Points de Vente sont particulièrement importants car ils sont liés à un client et possèdent leur propre inventaire de stock.</p>
            <h3>Demandes De Partenariat</h3>
            <p>Ce module liste les soumissions du formulaire "Devenir Partenaire" du site web, vous permettant de les examiner et de créer de nouvelles fiches clients.</p>
        </section>

        <section>
            <h2>Gestion des Utilisateurs</h2>
            <h3>Livreurs & Utilisateurs Admin</h3>
            <p>Ces sections permettent de gérer les comptes d'accès pour vos collaborateurs, qu'il s'agisse des livreurs ou des administrateurs de la plateforme.</p>
        </section>

        <section>
            <h2>Administration</h2>
            <h3>Modèles De Notification & Paramètres</h3>
            <p>Ces sections avancées permettent de personnaliser les e-mails et SMS envoyés par l'application, ainsi que de gérer divers paramètres globaux du système.</p>
        </section>

        <section>
            <h2>Contenu & Site Web</h2>
            <h3>Articles, Catégories D'actualités, Contenu des Pages</h3>
            <p>Ces modules vous donnent un contrôle total sur le contenu éditorial de votre site web public. Vous pouvez y créer et gérer des articles de blog, organiser vos pages et structurer vos actualités.</p>
        </section>

    </div>
</x-filament-panels::page>